<?php
/**
 * Revolut_Webhook_Controller
 *
 * Controller for handling Revolut webhook callbacks
 *
 * @package    WooCommerce
 * @category   Payment Gateways
 * @author     Revolut
 * @since      2.0.0
 */

use Revolut\Plugin\Infrastructure\Api\MerchantApi;
use Revolut\Plugin\Services\Log\RLog;

/**
 * Revolut_Webhook_Controller class
 */
class Revolut_Webhook_Controller extends \WC_REST_Data_Controller {

	use WC_Gateway_Revolut_Helper_Trait;

	use WC_Gateway_Revolut_Express_Checkout_Helper_Trait;

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'revolut';

	/**
	 * Register routes.
	 *
	 * @since 3.5.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => \WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'handle_revolut_webhook_deprecated_endpoint' ),
				'permission_callback' => function ( $request = null ) {
					return true;
				},
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/webhook/sandbox',
			array(
				'methods'             => \WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'handle_revolut_webhook' ),
				'permission_callback' => array( $this, 'revolut_webhook_permission_callback_sandbox' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/webhook/live',
			array(
				'methods'             => \WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'handle_revolut_webhook' ),
				'permission_callback' => array( $this, 'revolut_webhook_permission_callback_live' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/address/validation/webhook/sandbox',
			array(
				'methods'             => \WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'handle_revolut_address_validation_webhook' ),
				'permission_callback' => array( $this, 'revolut_address_validation_webhook_permission_callback_sandbox' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/address/validation/webhook/live',
			array(
				'methods'             => \WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'handle_revolut_address_validation_webhook' ),
				'permission_callback' => array( $this, 'revolut_address_validation_webhook_permission_callback_live' ),
			)
		);
	}


	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool
	 */
	public function revolut_address_validation_webhook_permission_callback_live( $request = null ) {
		$signing_secret = get_option( 'revolut_pay_synchronous_webhook_domain_live_signing_key' );
		return $this->revolut_address_validation_webhook_permission_callback( $request, $signing_secret );
	}

	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool
	 */
	public function revolut_address_validation_webhook_permission_callback_sandbox( $request = null ) {
		$signing_secret = get_option( 'revolut_pay_synchronous_webhook_domain_sandbox_signing_key' );
		return $this->revolut_address_validation_webhook_permission_callback( $request, $signing_secret );
	}

	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool
	 */
	public function revolut_webhook_permission_callback_sandbox( $request = null ) {
		$signing_secret = get_option( 'sandbox_revolut_webhook_domain_signing_secret' );
		return $this->revolut_webhook_permission_callback( $request, $signing_secret );
	}

	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool
	 */
	public function revolut_webhook_permission_callback_live( $request = null ) {
		$signing_secret = get_option( 'live_revolut_webhook_domain_signing_secret' );
		return $this->revolut_webhook_permission_callback( $request, $signing_secret );
	}

	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param string          $signing_secret secret key for signing the request.
	 *
	 * @return bool
	 */
	public function revolut_address_validation_webhook_permission_callback( $request, $signing_secret ) {
		if ( empty( $signing_secret ) ) {
			return false;
		}

		$received_signature = $request->get_header( 'Revolut-Pay-Payload-Signature' );

		if ( empty( $received_signature ) ) {
			return false;
		}

		$calculated_signature = hash_hmac( 'sha256', $request->get_body(), $signing_secret );

		return hash_equals( $calculated_signature, $received_signature );
	}

	/**
	 * Permissions check
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param string          $signing_secret secret key for signing the request.
	 *
	 * @return bool
	 */
	public function revolut_webhook_permission_callback( $request, $signing_secret ) {
		if ( empty( $signing_secret ) ) {
			return false;
		}

		$request_timestamp  = $request->get_header( 'Revolut-Request-Timestamp' );
		$received_signature = $request->get_header( 'Revolut-Signature' );

		if ( empty( $request_timestamp ) || empty( $received_signature ) ) {
			return false;
		}

		$payload_to_sign = 'v1.' . $request_timestamp . '.' . $request->get_body();

		$calculated_signature = 'v1=' . hash_hmac( 'sha256', $payload_to_sign, $signing_secret );

		return hash_equals( $calculated_signature, $received_signature );
	}

	/**
	 * Revolut webhook callback request
	 *
	 * @param WP_REST_Request $request WP REST Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_revolut_address_validation_webhook( $request ) {
		$parameters = $request->get_params();

		$this->convert_revolut_order_metadata_into_wc_session( $parameters['order_id'] );

		$requested_address              = $parameters['shipping_address'];
		$requested_address['address']   = $requested_address['street_line_1'];
		$requested_address['address_2'] = '';
		$requested_address['state']     = ! empty( $requested_address['region'] ) ? $requested_address['region'] : '';

		$country  = $requested_address['country'];
		$postcode = $requested_address['postcode'];

		$postcode          = wc_format_postcode( $postcode, $country );
		$is_valid_postcode = WC_Validation::is_postcode( $postcode, $country );

		if ( ! $is_valid_postcode ) {
			$this->log_info( 'Invalid postcode info: ' . $postcode );

			return new WP_REST_Response(
				array(
					'valid'            => false,
					'delivery_methods' => array(),
				),
				200
			);
		}

		$shipping_options = $this->get_shipping_options( $requested_address );

		return new WP_REST_Response(
			array(
				'valid'            => (bool) count( $shipping_options ),
				'delivery_methods' => $shipping_options,
			),
			200
		);
	}

	/**
	 * Revolut webhook callback request
	 *
	 * @param WP_REST_Request $request WP REST Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_revolut_webhook( $request ) {
		$parameters       = $request->get_params();
		$revolut_order_id = '';
		$event            = '';

		if ( isset( $parameters['order_id'] ) ) {
			$revolut_order_id = $parameters['order_id'];
		}

		if ( isset( $parameters['event'] ) ) {
			$event = $parameters['event'];
		}

		if ( empty( $revolut_order_id ) || empty( $event ) ) {
			$parameters = $request->get_body();
			$parameters = json_decode( $parameters, true );

			if ( isset( $parameters['order_id'] ) ) {
				$revolut_order_id = $parameters['order_id'];
			}

			if ( isset( $parameters['event'] ) ) {
				$event = $parameters['event'];
			}
		}

		if ( empty( $revolut_order_id ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'Failed',
					'message' => 'missing order id',
				),
				400
			);
		}

		if ( empty( $event ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'Failed',
					'message' => 'missing event',
				),
				400
			);
		}

		if ( ! in_array( $event, array( 'ORDER_COMPLETED', 'ORDER_AUTHORISED' ), true ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'Failed',
					'message' => 'Unrecognized order event',
				),
				400
			);
		}

		$wc_order_id = (int) $this->get_wc_order_id_by_revolut_order_id( $revolut_order_id );

		if ( empty( $wc_order_id ) ) {
			return new WP_REST_Response( array( 'status' => 'Failed' ), 404 );
		}

		$wc_order = wc_get_order( $wc_order_id );

		if ( empty( $wc_order ) ) {

			RLog::error( "WebhookEventHandler Error : wc order not found for order id $revolut_order_id" );

			return new WP_REST_Response(
				array(
					'status'  => 'Failed',
					'message' => 'WC order not found',
				),
				404
			);
		}

		if ( 'ORDER_AUTHORISED' === $event ) {
			$is_subscription_payment = (int) $wc_order->get_meta( 'is_subscription_order' );

			$this->capture_payment( $revolut_order_id );

			$result       = $this->process_authorised_order( $revolut_order_id, $wc_order->get_id(), $is_subscription_payment, true );
			$response_msg = $result ? 'Payment AUTHORISED event hanled by webhook' : 'Payment AUTHORISED event hanled by main process';

			if ( $result ) {
				RLog::info( 'Order ID(' . $wc_order->get_id() . ' - ' . $revolut_order_id . ') ' . $response_msg );
			}

			return new WP_REST_Response(
				array(
					'status'  => 'OK',
					'message' => $response_msg,
				),
				200
			);
		}

		$result       = $this->process_captured_order( $revolut_order_id, $wc_order->get_id() );
		$response_msg = $result ? 'Payment COMPLETED event hanled by webhook' : 'Payment COMPLETED event hanled by main process';

		if ( $result ) {
			RLog::info( 'Order ID(' . $wc_order->get_id() . ' - ' . $revolut_order_id . ') ' . $response_msg );
		}

		$data = array(
			'status'   => 'OK',
			'response' => $response_msg,
		);

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Revolut webhook callback request
	 *
	 * @param WP_REST_Request $request WP REST Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_revolut_webhook_deprecated_endpoint( $request ) {
		$parameters = $request->get_params();

		if ( in_array( 'shipping_address', array_keys( $parameters ), true ) ) {
			return $this->handle_revolut_address_validation_webhook( $request );
		}

		return $this->handle_revolut_webhook( $request );
	}
}
