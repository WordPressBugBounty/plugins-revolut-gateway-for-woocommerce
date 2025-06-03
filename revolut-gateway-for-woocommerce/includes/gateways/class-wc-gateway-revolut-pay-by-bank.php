<?php
/**
 * Revolut Credit Card Gateway
 *
 * Provides a Revolut Payment Gateway to accept credit card payments.
 *
 * @package WooCommerce
 * @category Payment Gateways
 * @author Revolut
 */

/**
 * WC_Gateway_Revolut_CC class.
 */
class WC_Gateway_Revolut_Pay_By_Bank extends WC_Payment_Gateway_Revolut {
	const GATEWAY_ID  = 'revolut_pay_by_bank';
	const METHOD_NAME = 'open_banking';
	const FLAG_NAME   = 'ENABLE_OPEN_BANKING_FOR_EUR';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id           = self::GATEWAY_ID;
		$this->method_title = __( 'Revolut Gateway - Pay by Bank', 'revolut-gateway-for-woocommerce' );
		$this->tab_title    = __( 'Pay by Bank', 'revolut-gateway-for-woocommerce' );

		$this->default_title = __( 'Pay by Bank', 'revolut-gateway-for-woocommerce' );
		/* translators:%1s: %$2s: */
		$this->method_description = sprintf( __( 'Accept card payments easily and securely via %1$sRevolut%2$s.', 'revolut-gateway-for-woocommerce' ), '<a href="https://www.revolut.com/business/online-payments">', '</a>' );

		$this->title = __( 'Pay by Bank', 'revolut-gateway-for-woocommerce' );

		parent::__construct();

		add_filter( 'wc_revolut_settings_nav_tabs', array( $this, 'pay_by_bank_admin_nav_tab' ), 5 );
	}

	/**
	 * Supported functionality
	 */
	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'refunds';
	}

	/**
	 * Add setting tab to admin configuration.
	 *
	 * @param array $tabs setting tabs.
	 */
	public function pay_by_bank_admin_nav_tab( $tabs ) {
		if ( ! $this->is_supported() ) {
			return $tabs;
		}

		$tabs[ $this->id ] = $this->tab_title;
		return $tabs;
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'revolut-gateway-for-woocommerce' ),
				'label'       => __( 'Enable ', 'revolut-gateway-for-woocommerce' ) . $this->method_title,
				'type'        => 'checkbox',
				'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'revolut-gateway-for-woocommerce' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Returns wether payment method is supported in the current page or not
	 */
	public function page_supported() {
		return $this->is_available();
	}

	/**
	 * Add public_id field and logo on card form
	 *
	 * @param String $public_id            Revolut public id.
	 * @param String $merchant_public_key  Revolut public key.
	 * @param String $display_tokenization Available saved card tokens.
	 *
	 * @return string
	 */
	public function generate_inline_revolut_form( $public_id, $merchant_public_key, $display_tokenization ) {
		if ( ! in_array( get_woocommerce_currency(), $this->card_payments_currency_list, true ) ) {
			return get_woocommerce_currency() . ' currency is not available for card payments';
		}

		$total    = WC()->cart->get_total( '' );
		$currency = get_woocommerce_currency();
		$total    = $this->get_revolut_order_total( $total, $currency );
		$mode     = $this->api_settings->get_option( 'mode' );

		return '<fieldset id="wc-' . $this->id . '-form" class="wc-credit-card-form wc-payment-form">
        <div id="woocommerce-revolut-pay-by-bank-element" data-mode="' . $mode . '" data-currency="' . $currency . '" data-total="' . $total . '" data-locale="' . $this->get_lang_iso_code() . '" data-public-id="' . $public_id . '" data-merchant-public-key="' . $merchant_public_key . '"></fieldset>';
	}

	/**
	 * Check is payment method available.
	 */
	public function is_supported() {
		return $this->is_payment_method_available( self::METHOD_NAME ) && $this->check_feature_support( self::FLAG_NAME );
	}

	/**
	 * Check is payment method available.
	 */
	public function is_available() {
		if ( ! $this->check_currency_support() || ! $this->is_supported() ) {
			return false;
		}

		if ( 'authorize_and_capture' !== $this->api_settings->get_option( 'payment_action' ) ) {
			return false;
		}

		return 'yes' === $this->enabled;
	}
}
