<?php
/**
 * Revolut CC Woo blocks checkout handler
 *
 * @package    Revolut
 * @category   Payment Gateways
 * @author     Revolut
 * @since      4.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once REVOLUT_PATH . 'includes/traits/wc-revolut-logger-trait.php';

/**
 * WC_Gateway_Revolut_Blocks_Support class.
 */
class WC_Gateway_Revolut_Blocks_Support extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {

	/**
	 * Gateway.
	 *
	 * @var WC_Gateway_Revolut_CC
	 */
	private $card_gateway;

	/**
	 * Gateway.
	 *
	 * @var WC_Gateway_Revolut_Pay
	 */
	private $revolut_pay_gateway;

	/**
	 * Gateway.
	 *
	 *  @var WC_Gateway_Revolut_Payment_Request
	 */
	private $payment_request_gateway;

	/**
	 * Blocks support gateway name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Constructor
	 *
	 * @param WC_Gateway_Revolut_CC              $card_gateway CC Gateway object.
	 * @param WC_Gateway_Revolut_Pay             $revolut_pay_gateway Revolut Pay Gateway object.
	 * @param WC_Gateway_Revolut_Payment_Request $payment_request_gateway Payment request Gateway object.
	 */
	public function __construct( $card_gateway, $revolut_pay_gateway, $payment_request_gateway ) {
		$this->card_gateway            = $card_gateway;
		$this->revolut_pay_gateway     = $revolut_pay_gateway;
		$this->payment_request_gateway = $payment_request_gateway;
		$this->name                    = 'revolut';
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', array( $this, 'prepare_gateway_for_processing' ), 10, 2 );
	}

	/**
	 * Initializes the payment gateway
	 */
	public function initialize() {
		$this->settings = array_merge(
			get_option( 'woocommerce_revolut_cc_settings', array() ),
			get_option( 'woocommerce_revolut_pay_settings', array() ),
			get_option( 'woocommerce_revolut_payment_request_settings', array() )
		);
	}

	/**
	 * Fetches gateway status
	 */
	public function is_active() {
		return $this->card_gateway->is_available() || $this->revolut_pay_gateway->is_available() || $this->payment_request_gateway->is_available();
	}

	/**
	 * Registers gateway frontend assets
	 */
	public function get_payment_method_script_handles() {
		return $this->register_blocks_scripts();
	}

	/**
	 * Prepares gateway data to be available in FE
	 */
	public function get_payment_method_data() {
			$payment_methods_data = array(
				'revolut_cc_data'              => $this->revolut_cc_payment_method_data(),
				'revolut_pay_data'             => $this->revolut_pay_payment_method_data(),
				'revolut_payment_request_data' => $this->revolut_payment_request_method_data(),
			);
			return array_merge(
				$payment_methods_data,
				$this->get_common_payment_data(),
			);
	}
	/**
	 * Returns gateway fields
	 */
	private function revolut_cc_payment_method_data() {
		if ( ! is_checkout() ) {
			return array();
		}

		try {
			return array_merge(
				get_option( 'woocommerce_revolut_cc_settings', array() ),
				array(
					'payment_method_name'              => $this->name,
					'locale'                           => $this->card_gateway->get_lang_iso_code(),
					'can_make_payment'                 => $this->card_gateway->is_available(),
					'is_save_payment_method_mandatory' => $this->card_gateway->cart_contains_subscription(),
					'card_holder_name_field_enabled'   => 'yes' === $this->card_gateway->get_option( 'enable_cardholder_name', 'yes' ),
					'banner'                           => array(
						'upsell_banner_enabled' => $this->card_gateway->promotional_settings->upsell_banner_enabled(),
					),
				)
			);
		} catch ( Throwable $e ) {
			$this->card_gateway->log_error( 'revolut_cc_payment_method_data : ' . $e->getMessage() );
			return array(
				'can_make_payment' => false,
			);
		}
	}


	/**
	 * Returns payment request fields
	 */
	private function revolut_pay_payment_method_data() {
		try {
			return array_merge(
				get_option( 'woocommerce_revolut_pay_settings', array() ),
				array(
					'payment_method_name' => $this->revolut_pay_gateway->id,
					'title'               => $this->revolut_pay_gateway->title,
					'can_make_payment'    => $this->revolut_pay_gateway->page_supported(),
					'mobile_redirect_url' => $this->revolut_pay_gateway->get_redirect_url(),
					'is_cart'             => is_cart(),
					'banner'              => array(
						'points_banner_enabled' => $this->revolut_pay_gateway->points_banner_available(),
						'label_icon_variant'    => $this->revolut_pay_gateway->promotional_settings->revolut_pay_label_icon_variant(),
					),
					'styles'              => $this->revolut_pay_gateway->get_revolut_pay_button_styles(),
				),
			);
		} catch ( Throwable $e ) {
			$this->revolut_pay_gateway->log_error( 'revolut_pay_payment_method_data : ' . $e->getMessage() );
			return array(
				'can_make_payment' => false,
			);
		}

	}

	/**
	 * Returns revolut pay fields
	 */
	private function revolut_payment_request_method_data() {
		try {
			return array_merge(
				get_option( 'woocommerce_revolut_payment_request_settings', array() ),
				array(
					'payment_method_name' => $this->payment_request_gateway->id,
					'title'               => $this->payment_request_gateway->title,
					'can_make_payment'    => $this->payment_request_gateway->page_supported(),
					'is_cart'             => is_cart(),
					'styles'              => $this->payment_request_gateway->get_prb_button_styles(),
				),
			);

		} catch ( Throwable $e ) {
			$this->payment_request_gateway->log_error( 'revolut_payment_request_method_data : ' . $e->getMessage() );
			return array(
				'can_make_payment' => false,
			);
		}

	}


	/**
	 * Returns common fields shared across all payment methods
	 */
	private function get_common_payment_data() {

		$descriptor = new WC_Revolut_Order_Descriptor(
			WC()->cart->get_total( '' ),
			get_woocommerce_currency(),
			null
		);

		return array(
			'locale'                    => $this->card_gateway->get_lang_iso_code(),
			'merchant_public_key'       => $this->card_gateway->get_merchant_public_api_key(),
			'wc_plugin_url'             => WC_REVOLUT_PLUGIN_URL,
			'available_card_brands'     => $this->card_gateway->get_available_card_brands(
				$descriptor->amount,
				$descriptor->currency
			),
			'informational_banner_data' => $this->revolut_pay_gateway->get_informational_banner_data(),
			'order'                     => array(
				'currency' => $descriptor->currency,
				'amount'   => $descriptor->amount,
			),
			'route'                     => array(
				'create_revolut_order' => get_site_url() . '/?wc-ajax=wc_revolut_create_order',
				'process_order'        => get_site_url() . '/?wc-ajax=wc_revolut_process_payment_result',
			),
			'nonce'                     => array(
				'create_revolut_order' => wp_create_nonce( 'wc-revolut-create-order' ),
				'process_order'        => wp_create_nonce( 'wc-revolut-process-payment-result' ),
			),
			'fast_checkout_params'      => $this->payment_request_gateway->get_wc_revolut_payment_request_params(),
		);
	}


	/**
	 * Registers blocks client scripts
	 *
	 * @return array
	 */
	private function register_blocks_scripts() {
		$external_dependencies = require REVOLUT_PATH . 'client/dist/index.asset.php';

		wp_register_script( WC_REVOLUT_UPSELL_WIDGET_SCRIPT_HANDLE, $this->card_gateway->api_client->base_url . '/upsell/embed.js', array(), WC_GATEWAY_REVOLUT_VERSION, true );
		wp_register_script( WC_REVOLUT_CHECKOUT_WIDGET_SCRIPT_HANDLE, $this->card_gateway->api_client->base_url . '/embed.js', array(), WC_GATEWAY_REVOLUT_VERSION, true );
		wp_register_script(
			WC_REVOLUT_BLOCKS_CHECKOUT_SCRIPT_HANDLE,
			WC_REVOLUT_PLUGIN_URL . '/client/dist/index.js',
			$external_dependencies['dependencies'],
			WC_GATEWAY_REVOLUT_VERSION,
			true
		);

		return array( WC_REVOLUT_CHECKOUT_WIDGET_SCRIPT_HANDLE, WC_REVOLUT_UPSELL_WIDGET_SCRIPT_HANDLE, WC_REVOLUT_BLOCKS_CHECKOUT_SCRIPT_HANDLE );
	}

	/**
	 * Prepare order for processing via the correct gateway
	 *
	 * @param PaymentContext $context Payment context.
	 * @param PaymentResult  $result  Payment result.
	 */
	public function prepare_gateway_for_processing( $context, &$result ) {

		switch ( $context->payment_method ) {
			case $this->card_gateway->id:
				$this->card_gateway->blocks_checkout_processor( $context, $result );
				break;
			case $this->revolut_pay_gateway->id:
				$this->revolut_pay_gateway->blocks_checkout_processor( $context, $result );
				break;
			case $this->payment_request_gateway->id:
				$this->payment_request_gateway->blocks_checkout_processor( $context, $result );
				break;
			default:
				return;
		}
	}
}
