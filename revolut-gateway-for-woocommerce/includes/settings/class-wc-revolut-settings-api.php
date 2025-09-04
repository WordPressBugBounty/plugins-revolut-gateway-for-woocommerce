<?php
/**
 * Revolut Api Settings
 *
 * Provides configuration for API settings
 *
 * @package WooCommerce
 * @category Payment Gateways
 * @author Revolut
 * @since 2.0
 */

use Revolut\Wordpress\Infrastructure\OptionRepository;
use Revolut\Wordpress\ServiceProvider;

use Revolut\Plugin\Infrastructure\Repositories\OptionTokenRepository;
use Revolut\Plugin\Infrastructure\Api\MerchantApi;
/**
 * WC_Revolut_Settings_API class.
 */
class WC_Revolut_Settings_API extends WC_Settings_API {


	use WC_Revolut_Settings_Trait;


	/**
	 * Option key name.
	 *
	 * @var array
	 */
	public static $option_key = 'woocommerce_revolut_settings';

	/**
	 * Error message list.
	 *
	 * @var array
	 */
	public $error_message = array();

	/**
	 * Success message list.
	 *
	 * @var array
	 */
	public $success_message = array();

	/**
	 * Dev Connect server url
	 *
	 * @var string
	 */
	public $connect_server_url_dev = 'https://checkout.revolut.codes';

	/**
	 * Prod Connect server url
	 *
	 * @var string
	 */
	public $connect_server_url_live = 'https://checkout.revolut.com';

	/**
	 * Webhook endpoint path
	 *
	 * @var string
	 */
	public static $webhook_endpoint = '/wp-json/wc/v3/revolut';

	/**
	 * New webhook endpoint path
	 *
	 * @var string
	 */
	public static $webhook_endpoint_new = 'wc/v3/revolut/webhook';

	/**
	 * New address validation webhook endpoint path
	 *
	 * @var string
	 */
	public static $address_validation_webhook_endpoint_new = 'wc/v3/revolut/address/validation/webhook';

	/**
	 * Class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Config provider class.
	 *
	 * @var object
	 */
	private $config_provider;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id              = 'revolut';
		$this->tab_title       = __( 'API Settings', 'revolut-gateway-for-woocommerce' );
		$this->config_provider = ServiceProvider::apiConfigProvider();

		$this->init_form_fields();
		$this->init_settings();
		$this->hooks();
		wp_enqueue_script( 'revolut-connect' );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Get singleton class instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add required filters
	 */
	public function hooks() {
		add_filter( 'wc_revolut_settings_nav_tabs', array( $this, 'admin_nav_tab' ), 1 );
		add_action( 'woocommerce_settings_checkout', array( $this, 'output_settings_nav' ) );
		add_action( 'woocommerce_settings_checkout', array( $this, 'admin_options' ) );
		add_action( 'added_option', array( $this, 'updated_option' ) );
		add_action( 'updated_option', array( $this, 'updated_option' ) );
		add_action( 'admin_notices', array( $this, 'show_messages' ) );
		add_action( 'admin_notices', array( $this, 'add_revolut_description' ) );
		add_action( 'woocommerce_update_options_checkout_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Wp Hook will be fired after update update action
	 *
	 * @param string $option_key updated option key.
	 */
	public function updated_option( $option_key ) {
		if ( $option_key !== self::$option_key ) {
			return;
		}

		if ( ! $this->options_updated() ) {
			return;
		}

		if ( ! $this->store_has_valid_connection() ) {
			return $this->add_error_message( __( 'Revolut requires connection to your account.', 'revolut-gateway-for-woocommerce' ) );
		}

		ServiceProvider::resetApiConfigProvider();
		$this->config_provider = ServiceProvider::apiConfigProvider();
		ServiceProvider::initMerchantApi();

		$this->maybe_register_webhook();
		$this->maybe_register_synchronous_webhooks();
	}

	/**
	 * Initialize Settings Form Fields
	 */
	public function init_form_fields() {
		$mode            = $this->get_option( 'mode' );
		$mode            = empty( $mode ) ? 'sandbox' : $mode;
		$api_key_sandbox = $this->get_option( 'api_key_sandbox' );
		$api_key_dev     = $this->get_option( 'api_key_dev' );
		$api_key_live    = $this->get_option( 'api_key' );

		$this->form_fields = array(
			'title'                        => array(
				'type'  => 'title',
				'title' => __( 'Revolut Gateway - API Settings', 'revolut-gateway-for-woocommerce' ),
			),
			'mode'                         => array(
				'title'       => __( 'Select Mode', 'revolut-gateway-for-woocommerce' ),
				'description' => __( 'Select mode between live mode and sandbox.', 'revolut-gateway-for-woocommerce' ),
				'desc_tip'    => true,
				'type'        => 'select',
				'default'     => $mode,
				'options'     => array(
					'sandbox' => __( 'Sandbox', 'revolut-gateway-for-woocommerce' ),
					'live'    => __( 'Live', 'revolut-gateway-for-woocommerce' ),
					// phpcs:ignore
		 			'dev' => __('Dev', 'revolut-gateway-for-woocommerce'),
				),
			),
			'api_key_sandbox'              => array(
				'title'       => __( 'Sandbox API secret key' ),
				'description' => __( 'Sandbox API secret key from your Merchant settings on Revolut.', 'revolut-gateway-for-woocommerce' ),
				'desc_tip'    => true,
				'default'     => $api_key_sandbox,
				'type'        => 'password',
				'class'       => 'enabled-sandbox',
			),
			// phpcs:ignore
			'api_key_dev' => array(
				'title'       => __( 'API Key Dev' ),
				'description' => __( 'API Key from your Merchant settings on Revolut.', 'revolut-gateway-for-woocommerce' ),
				'desc_tip'    => true,
				'default'     => $api_key_dev,
				'type'        => 'api_connect',
				'class'       => 'enabled-sandbox',
			),
			'api_key'                      => array(
				'title'       => __( 'Production API secret key', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'api_connect',
				'description' => __( 'Production API secret key from your Merchant settings on Revolut.', 'revolut-gateway-for-woocommerce' ),
				'desc_tip'    => true,
				'default'     => $api_key_live,
				'class'       => 'enabled-live',
			),
			'payment_action'               => array(
				'title'       => __( 'Payment Action', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'select',
				'default'     => 'authorize_and_capture',
				'options'     => array(
					'authorize'             => __( 'Authorize Only', 'revolut-gateway-for-woocommerce' ),
					'authorize_and_capture' => __( 'Authorize and Capture', 'revolut-gateway-for-woocommerce' ),
				),
				'description' => __(
					'Select "Authorize Only" mode. This allows the payment to be captured up to 7 days after the user has placed the order (e.g. when the goods are shipped or received). 
                If not selected, Revolut will try to authorize and capture all payments.',
					'revolut-gateway-for-woocommerce'
				),
				'desc_tip'    => true,
			),
			'accept_capture'               => array(
				'title'       => '',
				'label'       => __( 'Automatically capture order in Revolut', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Automatically try to capture orders when their status is changed.', 'revolut-gateway-for-woocommerce' ),
				'default'     => 'yes',
			),
			'customise_capture_status'     => array(
				'title'       => '',
				'label'       => __( 'Customize status to trigger capture.', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Default when checkbox not selected: Processing, Completed', 'revolut-gateway-for-woocommerce' ),
				'default'     => 'yes',
			),
			'selected_capture_status_list' => array(
				'title'             => '',
				'type'              => 'multiselect',
				'description'       => __( 'Order Status for triggering the payment capture on Revolut. Default: processing, completed', 'revolut-gateway-for-woocommerce' ),
				'desc_tip'          => true,
				'class'             => 'wc-enhanced-select',
				'options'           => wc_get_order_statuses(),
				'default'           => array(
					'wc-processing' => 'Processing',
					'wc-completed'  => 'Completed',
				),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select status', 'revolut-gateway-for-woocommerce' ),
				),
			),
		);
	}

	/**
	 * Generate api_connect view html.
	 *
	 * @param string $field field name.
	 */
	public function generate_api_connect_html( $field ) {
		ob_start();

		$api_key            = $this->get_option( $field );
		$is_api_key_present = ! empty( $api_key );
		$api_key_value      = esc_attr( $api_key );

		?>
		<tr valign="top" id="woocommerce_revolut_<?php echo esc_html( $field ); ?>_container">
			<th scope="row" class="titledesc">
				<label id="revolut-connection-type-label_<?php echo esc_attr( $field ); ?>">
					<span class="revolut-connection-label-text">
						Connect to revolut
					</span>
					<span>
						<?php echo wc_help_tip( 'Directly Connect your Revolut account. or use your production api secret keys instead', 'revolut-gateway-for-woocommerce' ); ?>
					</span>
				</label>
			</th>

			<td class="forminp">
				<!-- API Key Input -->
				<div id="revolut-api-key-wrapper-<?php echo esc_attr( $field ); ?>" style="<?php echo $is_api_key_present ? '' : 'display:none;'; ?>">
					<fieldset>
						<input class="input-text regular-input" 
							type="password" 
							name="woocommerce_revolut_<?php echo esc_attr( $field ); ?>" 
							id="woocommerce_revolut_<?php echo esc_attr( $field ); ?>" 
							value="<?php echo esc_html( $api_key_value ); ?>" />
					</fieldset>

					<!-- Toggle to OAuth -->
					<div style="margin-top:5px;<?php echo $is_api_key_present ? 'display:none;' : ''; ?>">
						<a href="#" class="revolut-switch-to-oauth">Or connect your Revolut account</a>
					</div>
				</div>

				<!-- OAuth Connect UI -->
				<div id="revolut-oauth-wrapper-<?php echo esc_attr( $field ); ?>" style="<?php echo $is_api_key_present ? 'display:none;' : ''; ?>">
					<div id="oauth_connection_container_<?php echo esc_attr( $field ); ?>"></div>
				</div>
			</td>
		</tr>
		<?php

		return ob_get_clean();

	}

	/**
	 * Enqueue the admin JS only on our settings page.
	 *
	 * @param string $hook_suffix hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'woocommerce_page_wc-settings' === $hook_suffix && $this->check_is_get_data_submitted( 'section' ) && $this->get_request_data( 'section' ) === 'revolut' ) {
			$external_dependencies = require REVOLUT_PATH . 'client/dist/client/oauth.index.asset.php';
			wp_enqueue_script(
				WC_REVOLUT_OAUTH_CONNECT_SCRIPT_HANDLE,
				WC_REVOLUT_PLUGIN_URL . '/client/dist/client/oauth.index.js',
				array_merge( $external_dependencies['dependencies'], array( 'jquery' ) ),
				WC_GATEWAY_REVOLUT_VERSION,
				true
			);

			wp_enqueue_style( 'revolut-ui-kit-style', WC_REVOLUT_PLUGIN_URL . '/client/dist/uikit/style.css', array(), WC_GATEWAY_REVOLUT_VERSION );

			wp_localize_script(
				WC_REVOLUT_OAUTH_CONNECT_SCRIPT_HANDLE,
				'ConnectVars',
				array(
					'ajax_url'                => admin_url( 'admin-ajax.php' ),
					'nonce'                   => wp_create_nonce( 'revolut_connect_nonce' ),
					'disconnect_nonce'        => wp_create_nonce( 'revolut_disconnect_nonce' ),
					'connect_server_url_dev'  => $this->connect_server_url_dev,
					'connect_server_url_live' => $this->connect_server_url_live,
					'store_has_valid_tokens'  => $this->hasValidTokens(),
				)
			);
		}
	}

	/**
	 * Check if store has valid tokens
	 */
	public function hasValidTokens() {
		$tokens = ( new OptionTokenRepository( new OptionRepository() ) )->getTokens();

		if ( empty( $tokens ) ) {
			return false;
		}

		try {
			MerchantApi::privateLegacy()->get( '/webhooks' );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}


	/**
	 * Displays configuration page with tabs
	 */
	public function admin_options() {
		if ( $this->check_is_get_data_submitted( 'page' ) && $this->check_is_get_data_submitted( 'section' ) ) {
			$is_revolut_api_section = 'wc-settings' === $this->get_request_data( 'page' ) && 'revolut' === $this->get_request_data( 'section' );

			if ( $is_revolut_api_section ) {
				echo wp_kses_post( '<table class="form-table">' );
				$this->generate_settings_html( $this->get_form_fields(), true );
				echo wp_kses_post( '</table>' );
			}
		}
	}

	/**
	 * Output Revolut description.
	 *
	 * @since 2.0.0
	 */
	public function add_revolut_description() {
		// Ensure 'page' and 'section' GET data is present.

		if ( ! $this->options_updated() ) {
			return;
		}

		if ( $this->store_has_valid_connection() ) {
			return;
		}

		?>
		<div class="notice notice-info sf-notice-nux is-dismissible" id="revolut_notice">
			<div class="notice-content">
				<p>
					Welcome to the <strong>Revolut Gateway for WooCommerce plugin!</strong>
				</p>
				<p>
					To start accepting payments from your customers at great rates, follow these three simple steps:
				</p>
				<ul style="list-style-type: disc; margin-left: 50px;">
					<li>
						<a href="<?php echo esc_url( 'https://business.revolut.com/signup' ); ?>" target="_blank" rel="noopener noreferrer">
							Sign up for Revolut
						</a>
						if you don't have an account already.
					</li>
					<li>
						Once your Revolut account has been approved, 
						<a href="<?php echo esc_url( 'https://business.revolut.com/merchant' ); ?>" target="_blank" rel="noopener noreferrer">
							apply for a Merchant Account
						</a>
					</li>
					<li>
						Connect your Revolut account by clicking the button below
					</li>
				</ul>
				<p>
					<a href="<?php echo esc_url( 'https://www.revolut.com/business/online-payments' ); ?>" target="_blank" rel="noopener noreferrer">
						Find out more
					</a>
					about why accepting payments through Revolut is the right decision for your business.
				</p>
				<p>
					If you'd like to know more about how to configure this plugin for your needs, 
					<a href="<?php echo esc_url( 'https://developer.revolut.com/docs/accept-payments/plugins/woocommerce/configuration' ); ?>" target="_blank" rel="noopener noreferrer">
						check out our documentation.
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Hool options_updated.
	 *
	 * @return boolean
	 */
	public function options_updated() {
		$data_submited = $this->check_is_get_data_submitted( 'page' ) && $this->check_is_get_data_submitted( 'section' );

		if ( ! $data_submited ) {
			return false;
		}

		$is_revolut_section = 'wc-settings' === $this->get_request_data( 'page' ) && in_array( $this->get_request_data( 'section' ), WC_REVOLUT_GATEWAYS, true );

		return $is_revolut_section;
	}

	/**
	 * Check if store has valid connection.
	 *
	 * @return boolean
	 */
	public function store_has_valid_connection() {
		if ( $this->hasValidTokens() ) {
			return true;
		}

		return ! empty( $this->get_option( 'api_key_live' ) )
			|| ! empty( $this->get_option( 'api_key_dev' ) )
			|| ! empty( $this->get_option( 'api_key_sandbox' ) );
	}

	/**
	 * Setup Revolut webhook if not configured
	 */
	public function maybe_register_webhook() {
		if ( empty( $this->config_provider->getConfig()->getSecretKey() ) ) {
			return false;
		}

		if ( ! $this->check_is_shop_needs_webhook_setup() ) {
			return false;
		}

		if ( ! $this->setup_revolut_webhook() ) {
			return false;
		}

		$this->add_success_message( __( 'Webhook url successfully configured', 'revolut-gateway-for-woocommerce' ) );
	}

	/**
	 * Setup Revolut synchronous webhooks if not configured
	 */
	public function maybe_register_synchronous_webhooks() {
		if ( empty( $this->config_provider->getConfig()->getSecretKey() ) ) {
			return false;
		}

		if ( ! $this->setup_revolut_synchronous_webhook() ) {
			/* translators:%1s: %$2s: */
			$this->add_error_message( sprintf( __( 'Synchronous Webhook setup unsuccessful. Please make sure you are using the correct %1$sAPI key%2$s. If the problem persists, please reach out to support via our in-app chat.', 'revolut-gateway-for-woocommerce' ), '<a href="https://developer.revolut.com/docs/accept-payments/get-started/generate-the-api-key" target="_blank">', '</a>' ) );
			return false;
		}
	}

	/**
	 * Revolut location setup
	 *
	 * @throws Exception Exception.
	 */
	public function setup_revolut_location() {
		$domain        = get_site_url();
		$location_name = str_replace( array( 'https://', 'http://' ), '', $domain );
		$locations     = MerchantApi::private()->get( '/locations' );

		if ( ! empty( $locations ) ) {
			foreach ( $locations as $location ) {
				if ( isset( $location['name'] ) && $location['name'] === $domain && ! empty( $location['id'] ) ) {
					return $location['id'];
				}
			}
		}

		$body = array(
			'name'    => $location_name,
			'type'    => 'online',
			'details' => array(
				'domain' => $domain,
			),
		);

		$location = MerchantApi::private()->post( '/locations', $body );

		if ( ! isset( $location['id'] ) || empty( $location['id'] ) ) {
			throw new Exception( 'Can not create location object.' );
		}

		return $location['id'];
	}

	/**
	 * Check is shop needs webhook setup
	 */
	public function check_is_shop_needs_webhook_setup() {
		try {
			$mode = $this->get_option( 'mode' );
			$mode = empty( $mode ) ? 'sandbox' : $mode;

			$web_hook_url = get_site_url( null, self::$webhook_endpoint_new . '/' . $mode, 'https' );

			if ( strpos( $web_hook_url, 'http://localhost' ) !== false ) {
				return false;
			}

			if ( $this->get_option( $mode . '_revolut_webhook_domain' ) === $web_hook_url ) {
				return false;
			}

			$web_hook_url_list = MerchantApi::privateLegacy()->get( '/webhooks' );

			if ( ! empty( $web_hook_url_list ) ) {
				$web_hook_url_list = array_column( $web_hook_url_list, 'url' );

				if ( in_array( $web_hook_url, $web_hook_url_list, true ) ) {
					return false;
				}
			}
		} catch ( Exception $e ) {
			$this->add_error_message( $e->getMessage() );
		}

		return true;
	}

	/**
	 * Remove old webhook setup
	 */
	public function remove_old_revolut_webhook_if_exist() {
		$old_web_hook_url  = get_site_url( null, self::$webhook_endpoint, 'https' );
		$web_hook_url_list = MerchantApi::privateLegacy()->get( '/webhooks' );

		if ( ! empty( $web_hook_url_list ) ) {
			foreach ( $web_hook_url_list as $key => $value ) {
				if ( $value['url'] === $old_web_hook_url ) {
					MerchantApi::privateLegacy()->delete( '/webhooks/' . $value['id'] );
				}
			}
		}
	}

	/**
	 * Revolut webhook setup
	 */
	public function setup_revolut_webhook() {
		try {
			$mode = $this->get_option( 'mode' );
			$mode = empty( $mode ) ? 'sandbox' : $mode;

			$web_hook_url = rest_url() . self::$webhook_endpoint_new . "/$mode";

			$body = array(
				'url'    => $web_hook_url,
				'events' => array(
					'ORDER_COMPLETED',
					'ORDER_AUTHORISED',
				),
			);

			$response = MerchantApi::privateLegacy()->post( '/webhooks', $body );

			if ( isset( $response['id'] ) && ! empty( $response['id'] ) && ! empty( $response['signing_secret'] ) ) {
				$this->remove_old_revolut_webhook_if_exist();
				update_option( $mode . '_revolut_webhook_domain', $web_hook_url );
				update_option( $mode . '_revolut_webhook_domain_signing_secret', $response['signing_secret'] );

				return true;
			}
		} catch ( Exception $e ) { // phpcs:ignore
			// Prevent double logs. Exception logged previously.
		}

		return false;
	}

	/**
	 * Revolut webhook setup
	 */
	public function setup_revolut_synchronous_webhook() {
		try {
			$mode = $this->get_option( 'mode' );
			$mode = empty( $mode ) ? 'sandbox' : $mode;

			$web_hook_url = rest_url() . self::$address_validation_webhook_endpoint_new . "/$mode";

			if ( strpos( $web_hook_url, 'http://localhost' ) !== false ) {
				return false;
			}

			$location_id = $this->setup_revolut_location();

			if ( get_option( 'revolut_pay_synchronous_webhook_domain_' . $mode . '_' . $location_id ) === $web_hook_url ) {
				update_option( 'revolut_' . $mode . '_location_id', $location_id );
				return true;
			}

			$body = array(
				'url'         => $web_hook_url,
				'event_type'  => 'fast_checkout.validate_address',
				'location_id' => $location_id,
			);

			$response = MerchantApi::private()->post( '/synchronous-webhooks', $body );

			if ( isset( $response['signing_key'] ) && ! empty( $response['signing_key'] ) ) {
				update_option( 'revolut_' . $mode . '_location_id', $location_id );
				update_option( 'revolut_pay_synchronous_webhook_domain_' . $mode . '_' . $location_id, $web_hook_url );
				update_option( 'revolut_pay_synchronous_webhook_domain_' . $mode . '_signing_key', $response['signing_key'] );
				$this->add_success_message( __( 'Synchronous Webhook url successfully configured', 'revolut-gateway-for-woocommerce' ) );
				return true;
			}

			$this->add_error_message( wp_json_encode( $response ) );
		} catch ( Exception $e ) {
			$this->add_error_message( $e->getMessage() );
		}

		return false;
	}

	/**
	 * Get Revolut Location
	 */
	public function get_revolut_location() {
		$mode = empty( $this->get_option( 'mode' ) ) ? 'sandbox' : $this->get_option( 'mode' );
		return get_option( 'revolut_' . $mode . '_location_id' );
	}

	/**
	 * Display error message
	 *
	 * @param string $message display message.
	 */
	public function add_error_message( $message ) {
		$this->error_message[] = $message;
	}

	/**
	 * Display success message
	 *
	 * @param string $message display message.
	 */
	public function add_success_message( $message ) {
		$this->success_message[] = $message;
	}

	/**
	 * Display setting update messages
	 */
	public function show_messages() {
		if ( ! empty( $this->success_message ) ) {
			foreach ( $this->success_message as $message ) {
				echo wp_kses_post( '<div style="border-left-color: green" class="error revolut-passphrase-message"><p>' . $message . '</p></div>' );
			}
		}

		if ( ! empty( $this->error_message ) ) {
			foreach ( $this->error_message as $message ) {
				echo wp_kses_post( '<div class="error revolut-passphrase-message"><p>' . $message . '</p></div>' );
			}
		}
	}

	/**
	 * Check is data submitted for GET request.
	 *
	 * @param string $submit request key.
	 */
	public function check_is_get_data_submitted( $submit ) {
		return isset( $_GET[ $submit ] ); // phpcs:ignore 
	}

	/**
	 * Safe get request data
	 *
	 * @param string $get_key request key.
	 */
	public function get_request_data( $get_key ) {
		return isset( $_GET[ $get_key ] ) ? wc_clean( wp_unslash( $_GET[ $get_key ] ) ) : ''; // phpcs:ignore 
	}
}
