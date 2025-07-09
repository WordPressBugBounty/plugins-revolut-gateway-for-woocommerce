<?php
/**
 * Singleton class that handles class loading.
 *
 * @package    WooCommerce
 * @category   Payment Gateways
 * @author     Revolut
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit();

/**
 * WC_Revolut_Manager class.
 */
class WC_Revolut_Manager {

	/**
	 * Class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Revolut Settings API Class instance.
	 *
	 * @var object
	 */
	public $api_settings;

	/**
	 * Revolut Promotional Settings API Class instance.
	 *
	 * @var object
	 */
	public $promotional_settings;

	/**
	 * Advanced Plugin Settings Class instance.
	 *
	 * @var WC_Revolut_Advanced_Settings
	 */
	public $advanced_settings;

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
	 * Constructor
	 */
	public function __construct() {
		$this->woocommerce_dependencies();
	}

	/**
	 * Include plugin dependencies.
	 */
	public function woocommerce_dependencies() {
		// init core.
		include_once REVOLUT_PATH . '/src/index.php';

		// traits.
		include_once REVOLUT_PATH . 'includes/traits/wc-revolut-settings-trait.php';
		include_once REVOLUT_PATH . 'includes/traits/wc-revolut-logger-trait.php';
		include_once REVOLUT_PATH . 'includes/traits/wc-revolut-helper-trait.php';
		include_once REVOLUT_PATH . 'includes/traits/wc-revolut-express-checkout-helper-trait.php';

		// settings.
		include_once REVOLUT_PATH . 'includes/settings/class-wc-revolut-settings-api.php';
		include_once REVOLUT_PATH . 'includes/settings/class-wc-revolut-promotional-settings.php';
		include_once REVOLUT_PATH . 'includes/settings/class-wc-revolut-advanced-settings.php';
		include_once REVOLUT_PATH . '/includes/class-wc-revolut-payment-tokens.php';

		// load gateways.
		include_once REVOLUT_PATH . 'includes/abstract/class-wc-payment-gateway-revolut.php';
		include_once REVOLUT_PATH . 'includes/gateways/class-wc-gateway-revolut-cc.php';
		include_once REVOLUT_PATH . 'includes/gateways/class-wc-gateway-revolut-pay.php';
		include_once REVOLUT_PATH . 'includes/gateways/class-wc-gateway-revolut-pay-by-bank.php';
		require_once REVOLUT_PATH . 'includes/gateways/class-wc-gateway-revolut-payment-request.php';

		// main classes.
		include_once REVOLUT_PATH . 'includes/class-wc-revolut-privacy.php';
		include_once REVOLUT_PATH . 'includes/class-wc-revolut-validate-checkout.php';
		include_once REVOLUT_PATH . 'includes/class-wc-revolut-order-descriptor.php';
		include_once REVOLUT_PATH . 'includes/class-wc-revolut-payment-ajax-controller.php';
		include_once REVOLUT_PATH . 'includes/class-wc-revolut-apple-pay-onboarding.php';
		include_once REVOLUT_PATH . '/api/class-revolut-webhook-controller.php';


		new WC_Revolut_Apple_Pay_OnBoarding();
		new WC_Revolut_Payment_Ajax_Controller();
		new WC_Gateway_Revolut_Payment_Request();
		new WC_Gateway_Revolut_Pay();
		new WC_Gateway_Revolut_Pay_By_Bank();
	}
}

/**
 * Returns the global instance of the WC_Revolut_Manager.
 *
 * @return WC_Revolut_Manager
 * @since 2.0.0
 */
function revolut_wc() {
	return WC_Revolut_Manager::instance();
}

add_action( 'woocommerce_init', 'revolut_wc' );
