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

/**
 * WC_Revolut_Settings_API class.
 */
class WC_Revolut_Advanced_Settings extends WC_Revolut_Settings_API {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id        = 'revolut_advanced_settings';
		$this->tab_title = __( 'Advanced Settings', 'revolut-gateway-for-woocommerce' );
		$this->init_form_fields();
		$this->init_settings();
		$this->hooks();
	}

	/**
	 * Add required filters
	 */
	public function hooks() {
		add_action( 'woocommerce_settings_checkout', array( $this, 'admin_options' ) );
		add_filter( 'wc_revolut_settings_nav_tabs', array( $this, 'admin_nav_tab' ), 10 );
		add_action( 'woocommerce_update_options_checkout_' . $this->id, array( $this, 'process_admin_options' ) );
	}

		/**
		 * Displays configuration page with tabs
		 */
	public function admin_options() {
		if ( $this->check_is_get_data_submitted( 'page' ) && $this->check_is_get_data_submitted( 'section' ) ) {
			$is_revolut_api_section = 'wc-settings' === $this->get_request_data( 'page' ) && $this->id === $this->get_request_data( 'section' );

			if ( $is_revolut_api_section ) {
				echo wp_kses_post( '<table class="form-table">' );
				$this->generate_settings_html( $this->get_form_fields(), true );
				echo wp_kses_post( '</table>' );
			}
		}
	}

	/**
	 * Check External Order Reference
	 */
	public function external_order_reference_is_order_id() {
		return $this->get_option( 'external_order_reference' ) === 'order_id';
	}

	/**
	 * Initialize Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'title'                    => array(
				'type'  => 'title',
				'title' => __( 'Revolut Gateway - Advanced Settings', 'revolut-gateway-for-woocommerce' ),
			),
			'external_order_reference' => array(
				'title'       => __( 'Select External WooCommerce Order Reference', 'revolut-gateway-for-woocommerce' ),
				'description' => __( 'This configuration allows selecting between using the Order ID (e.g. 1756) or modified Order Numbers (e.g RVLT001756) for the purpose of external references.', 'revolut-gateway-for-woocommerce' ),
				'desc_tip'    => true,
				'type'        => 'select',
				'default'     => 'order_id',
				'options'     => array(
					'order_id'     => __( 'WooCommerce Order ID', 'revolut-gateway-for-woocommerce' ),
					'order_number' => __( 'WooCommerce Order Number', 'revolut-gateway-for-woocommerce' ),
				),
			),
		);
	}
}
