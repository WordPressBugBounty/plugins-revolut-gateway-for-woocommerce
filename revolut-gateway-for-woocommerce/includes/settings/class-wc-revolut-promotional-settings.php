<?php
/**
 * Revolut Api Settings
 *
 * Provides configuration for API settings
 *
 * @package WooCommerce
 * @category Payment Gateways
 * @author Revolut
 * @since 4.17.6
 */

/**
 * WC_Revolut_Promotional_Settings class.
 */
class WC_Revolut_Promotional_Settings extends WC_Revolut_Settings_API {

	/**
	 * Class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id        = 'revolut_promotional_tab';
		$this->tab_title = __( 'Rewards & Promotions', 'revolut-gateway-for-woocommerce' );
		$this->init_form_fields();
		$this->init_settings();
		$this->hooks();
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
	 * Initialize Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'title'                             => array(
				'type'  => 'title',
				'title' => __( 'Revolut Gateway - Rewards & Promotions Settings', 'revolut-gateway-for-woocommerce' ),
			),
			'gateway_upsell_banner_enabled'     => array(
				'title'       => 'Reward banner',
				'label'       => __( 'Let your customers earn rewards for signing up to Revolut with your link', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => 'This banner can increase conversions by 5% and gives your customers a Revolut-funded reward. Without this banner, you can’t join our Merchant Affiliate Programme and earn cash rewards for referrals. Find out more below.',
				'default'     => 'yes',
			),
			'revolut_points_banner_enabled'     => array(
				'title'       => 'Benefits banner',
				'label'       => __( 'Displays informational banner with a brief description of Revolut Pay benefits', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => 'This allows your customers to open a pop-up with more details on the payment process and available benefits.',
				'default'     => 'yes',
			),
			'revolut_pay_label_icon'            => array(
				'title'       => 'Revolut Pay information',
				'description' => __( 'Displays an icon or a "Learn more" link which opens a pop-up with details on the Revolut Pay payment process and benefits', 'revolut-gateway-for-woocommerce' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'icon'     => __( 'Small icon', 'revolut-gateway-for-woocommerce' ),
					'link'     => __( 'Learn more', 'revolut-gateway-for-woocommerce' ),
					'cashback' => __( 'Get cashback', 'revolut-gateway-for-woocommerce' ),
					'disabled' => __( 'Disabled', 'revolut-gateway-for-woocommerce' ),

				),
				'default'     => 'cashback',
			),
			'revolut_pay_button_cashback_label' => array(
				'title'   => 'Cashback label',
				'label'   => __( 'Displays a label under Revolut Pay button offering cashback to new customers' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			'revolut_affiliate_program'         => array(
				'type' => 'affiliate_program_section',
			),
		);
	}

	public function generate_affiliate_program_section_html() {

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label>Merchant Affiliate Programme</label>
			</th>
			<td class="forminp">
				<p class="description">Earn cash rewards for every new customer you refer to Revolut — an easy way to earn back the cost of processing fees. Some of our merchants offset up to 50% of their fees through our Revolut Merchant Affiliate Programme. Plus, you get more customers who can pay in 1-click with Revolut Pay.</p>
				<p class="description">
					<a 
						target="_blank" 
						href='https://app.impact.com/campaign-campaign-info-v2/Revolut-Brand.brand?io=EYZ1y6ipUxQkpBEDNsR%2FmYlxR0LrkCUqwW9SOpjbIEkbgRJH3lCxQaN1IytrjPFZ'>
						Apply to Affiliate Programme
					</a> 
					or 
					<a 
						target="_blank" 
						href='https://developer.revolut.com/docs/resources/marketing-assets-guidelines/marketing-guidelines#affiliate-program---get-paid-for-introducing-new-customers-to-revolut'>
						Find out more
					</a>
			</td>
		</tr>
		<?php
		return ob_get_clean();

	}
	/**
	 * Returns upsell banner availability
	 */
	public function upsell_banner_enabled() {
		return 'yes' === $this->get_option( 'gateway_upsell_banner_enabled' );
	}

	/**
	 * Returns revpoints banner availability
	 */
	public function revpoints_banner_enabled() {
		return 'yes' === $this->get_option( 'revolut_points_banner_enabled' );
	}

	/**
	 * Returns RPay label icon
	 */
	public function revolut_pay_label_icon_variant() {
		$label_variant = $this->get_option( 'revolut_pay_label_icon' );
		if ( 'disabled' === $label_variant ) {
			return false;
		}

		return $label_variant;
	}

	/**
	 * Returns RPay button cashback label availability
	 */
	public function revolut_pay_cashback_enabled() {
		return 'yes' === $this->get_option( 'revolut_pay_button_cashback_label' );
	}
}
