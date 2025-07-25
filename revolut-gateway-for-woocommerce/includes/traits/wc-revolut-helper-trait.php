<?php
/**
 * Revolut Helper
 *
 * Helper class for required tools.
 *
 * @package WooCommerce
 * @category Payment Gateways
 * @author Revolut
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Revolut\Plugin\Infrastructure\Api\MerchantApi;

/**
 * WC_Gateway_Revolut_Helper_Trait trait.
 */
trait WC_Gateway_Revolut_Helper_Trait {


	use WC_Revolut_Settings_Trait;
	use WC_Revolut_Logger_Trait;

	/**
	 * Create Revolut Order
	 *
	 * @param WC_Revolut_Order_Descriptor $order_descriptor Revolut Order Descriptor.
	 *
	 * @param bool                        $is_express_checkout indicator.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function create_revolut_order( WC_Revolut_Order_Descriptor $order_descriptor, $is_express_checkout = false, $is_pay_by_bank = false ) {
		$payment_action = $this->api_settings->get_option( 'payment_action' );
		$capture_mode   = $is_pay_by_bank ? 'automatic' : 'manual';

		$body = array(
			'amount'       => $order_descriptor->amount,
			'currency'     => $order_descriptor->currency,
			'capture_mode' => $capture_mode,
		);

		if ( $payment_action === 'authorize_and_capture' && ! $is_pay_by_bank ) {
			$body['cancel_authorised_after'] = WC_REVOLUT_AUTO_CANCEL_TIMEOUT;
		}

		if ( ! empty( $order_descriptor->revolut_customer_id ) ) {
			$body['customer'] = array( 'id' => $order_descriptor->revolut_customer_id );
		}

		// needed in address validation for RPay fast checkout orders
		$location_id = $this->api_settings->get_revolut_location();

		if ( $location_id ) {
			$body['location_id'] = $location_id;
		}

		$json = MerchantApi::private()->post( '/orders', $body );

		if ( isset( $json['token'] ) ) {
			$json['public_id'] = $json['token'];
		}

		if ( empty( $json['id'] ) || empty( $json['public_id'] ) ) {
			throw new Exception( 'Something went wrong: ' . wp_json_encode( $json, JSON_PRETTY_PRINT ) );
		}

		global $wpdb;
		$result = $wpdb->query(
			$wpdb->prepare(
				'INSERT INTO ' . $wpdb->prefix . "wc_revolut_orders (order_id, public_id)
            VALUES (UNHEX(REPLACE(%s, '-', '')), UNHEX(REPLACE(%s, '-', '')))",
				array(
					$json['id'],
					$json['public_id'],
				)
			)
		); // db call ok; no-cache ok.

		if ( 1 !== $result ) {
			throw new Exception( 'Can not save Revolut order record on DB:' . $wpdb->last_error );
		}

		if ( $is_express_checkout ) {
			$this->add_or_update_temp_session( $json['id'] );
		}

		return $json['public_id'];
	}

		/**
		 * Update Revolut Order.
		 *
		 * @param WC_Revolut_Order_Descriptor $order_descriptor Revolut Order Descriptor.
		 * @param String                      $public_id Revolut public id.
		 * @param Bool                        $is_revpay_express_checkout is revpay express checkout.
		 *
		 * @return mixed
		 * @throws Exception Exception.
		 */
	public function update_revolut_order( WC_Revolut_Order_Descriptor $order_descriptor, $public_id, $is_revpay_express_checkout = false, $is_pay_by_bank = false ) {
		$order_id = null;

		if ( $public_id ) {
			$order_id = $this->get_revolut_order_by_public_id( $public_id );
		}

		$body = array(
			'amount'      => $order_descriptor->amount,
			'currency'    => $order_descriptor->currency,
			'customer_id' => $order_descriptor->revolut_customer_id,
		);

		if ( empty( $order_id ) ) {
			return $this->create_revolut_order( $order_descriptor, $is_revpay_express_checkout, $is_pay_by_bank );
		}

		$revolut_order = MerchantApi::privateLegacy()->get( "/orders/$order_id" );

		if ( ! isset( $revolut_order['public_id'] ) || ! isset( $revolut_order['id'] ) || 'PENDING' !== $revolut_order['state'] ) {
			return $this->create_revolut_order( $order_descriptor, $is_revpay_express_checkout, $is_pay_by_bank );
		}

		$revolut_order = MerchantApi::privateLegacy()->patch( "/orders/$order_id", $body );

		if ( ! isset( $revolut_order['public_id'] ) || ! isset( $revolut_order['id'] ) ) {
			return $this->create_revolut_order( $order_descriptor, $is_revpay_express_checkout, $is_pay_by_bank );
		}

		if ( $is_revpay_express_checkout ) {
			$this->add_or_update_temp_session( $revolut_order['id'] );
		}

		return $revolut_order['public_id'];
	}

	/**
	 * Save line items info into Revolut order.
	 *
	 * @param int    $wc_order_id WooCommerce order id.
	 * @param string $revolut_order_id Revolut order id.
	 */
	public function save_order_line_items( $wc_order_id, $revolut_order_id ) {
		try {
			$order = wc_get_order( $wc_order_id );

			if ( ! $order ) {
				return array();
			}

			$order_details = array(
				'line_items' => $this->collect_order_line_items( $order ),
				'shipping'   => $this->collect_order_shipping_info( $order ),
			);

			MerchantApi::private()->patch( "/orders/$revolut_order_id", $order_details );
		} catch ( Exception $e ) {
			$this->log_error( 'save_order_line_items failed : ' . $e->getMessage() );
		}
	}

	/**
	 * Get line items from WC order.
	 *
	 * @param object $order WooCommerce order object.
	 */
	public function collect_order_shipping_info( $order ) {
		$shipping_address     = $order->get_address( 'shipping' );
		$billing_phone_number = preg_replace( '/[^0-9]/', '', $order->get_billing_phone() );

			$shipping = array(
				'address' => array(
					'street_line_1' => $shipping_address['address_1'],
					'street_line_2' => $shipping_address['address_2'],
					'region'        => $shipping_address['state'],
					'city'          => $shipping_address['city'],
					'country_code'  => $shipping_address['country'],
					'postcode'      => $shipping_address['postcode'],
				),
				'contact' => array(
					'name'  => $shipping_address['first_name'] . ' ' . $shipping_address['last_name'],
					'email' => $order->get_billing_email(),
					'phone' => empty( $billing_phone_number ) ? null : $billing_phone_number,
				),
			);

			return $shipping;
	}

	/**
	 * Get shipping details from WC order.
	 *
	 * @param object $order WooCommerce order object.
	 */
	public function collect_order_line_items( $order ) {
		if ( ! $order ) {
			return array();
		}

		$line_items = array();
		$currency   = $order->get_currency();

		foreach ( $order->get_items() as $item ) {
			$product           = $item->get_product();
			$product_id        = $product->get_id();
			$product_name      = $product->get_name();
			$product_type      = $product->is_virtual() ? 'service' : 'physical';
			$quantity          = $item->get_quantity();
			$unit_price_amount = $this->get_revolut_order_total( $item->get_subtotal(), $currency );
			$total_amount      = $this->get_revolut_order_total( $item->get_total(), $currency );
			$description       = ! empty( $product->get_description() ) ? $product->get_description() : null;
			$product_url       = rawurlencode( get_permalink( $product_id ) );
			$image_urls        = $this->get_product_images( $product );

			if ( ! empty( $description ) && strlen( $description ) > 1024 ) {
				$description = substr( $description, 0, 1024 );
			}

			$line_items[] = array(
				'name'              => $product_name,
				'type'              => $product_type,
				'quantity'          => array(
					'value' => $quantity,
				),
				'unit_price_amount' => $unit_price_amount,
				'total_amount'      => $total_amount,
				'external_id'       => (string) $product_id,
				'description'       => $description,
			);
		}

		return $line_items;
	}

	/**
	 * Extract product images list from product object.
	 *
	 * @param object $product WooCommerce Product object.
	 */
	public function get_product_images( $product ) {
		$all_image_urls     = array();
		$gallery_image_urls = array();

		$main_image_url = rawurlencode( wp_get_attachment_url( $product->get_image_id() ) );

		if ( $main_image_url ) {
			$all_image_urls[] = $main_image_url;
		}

		$gallery_image_ids = $product->get_gallery_image_ids();

		foreach ( $gallery_image_ids as $image_id ) {
			$gallery_image_urls[] = rawurlencode( wp_get_attachment_url( $image_id ) );
		}

		return array_merge( $all_image_urls, $gallery_image_urls );
	}

	/**
	 * Retrieve the revolut customer's id.
	 *
	 * @param  string $billing_phone holds customer phone address.
	 * @param  string $billing_email holds customer billing email.
	 * @throws Exception Exception.
	 */
	public function get_or_create_revolut_customer( $billing_phone = '', $billing_email = '' ) {
		if ( empty( $billing_email ) || empty( $billing_phone ) ) {
			$wc_customer   = WC()->customer;
			$billing_email = $wc_customer->get_billing_email();
			$billing_phone = $wc_customer->get_billing_phone();
		}

		if ( empty( $billing_email ) ) {
			return;
		}

		$revolut_customer_id = $this->get_revolut_customer_id();

		if ( empty( $revolut_customer_id ) ) {
			$revolut_customer_id = $this->create_revolut_customer( $billing_phone, $billing_email );
			return $revolut_customer_id;
		}

		return $revolut_customer_id;
	}

	/**
	 * Update the revolut customer's phone.
	 *
	 * @param string $revolut_customer_id customer_id.
	 * @param string $billing_phone billing phone number.
	 * @throws Exception Exception.
	 * @return void|null
	 */
	public function update_revolut_customer( $revolut_customer_id, $billing_phone ) {
		if ( empty( $revolut_customer_id ) || empty( $billing_phone ) ) {
			return null;
		}

		MerchantApi::privateLegacy()->patch( "/customers/$revolut_customer_id", array( 'phone' => $billing_phone ) );
	}

	/**
	 * Create revolut customer.
	 *
	 * @return $revolut_customer_id revolut customer id.
	 * @param  string $billing_phone holds customer billing phone.
	 * @param  string $billing_email holds customer email address.
	 * @throws Exception Exception.
	 */
	public function create_revolut_customer( $billing_phone, $billing_email ) {
		try {
			$body = array(
				'email' => $billing_email,
			);

			if ( ! empty( $billing_phone ) ) {
				$body['phone'] = $billing_phone;
			}

			$revolut_customer    = MerchantApi::privateLegacy()->get( '/customers?term=' . $billing_email );
			$revolut_customer_id = ! empty( $revolut_customer[0]['id'] ) ? $revolut_customer[0]['id'] : '';

			if ( ! $revolut_customer_id ) {
				$revolut_customer    = MerchantApi::privateLegacy()->post( '/customers', $body );
				$revolut_customer_id = ! empty( $revolut_customer['id'] ) ? $revolut_customer['id'] : '';
			}

			if ( ! $revolut_customer_id ) {
				return;
			}

			$this->insert_revolut_customer_id( $revolut_customer_id );

			$this->update_revolut_customer( $revolut_customer_id, $billing_phone );
			return $revolut_customer_id;
		} catch ( Exception $e ) {
			$this->log_error( 'create_revolut_customer: ' . $e->getMessage() );
		}
	}

	/**
	 * Save Revolut customer id.
	 *
	 * @param string $revolut_customer_id Revolut customer id.
	 *
	 * @throws Exception Exception.
	 */
	protected function insert_revolut_customer_id( $revolut_customer_id ) {
		if ( empty( get_current_user_id() ) ) {
			return;
		}

		global $wpdb;
		$revolut_customer_id = "{$this->config_provider->getConfig()->getMode()}_$revolut_customer_id";

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}wc_revolut_customer (wc_customer_id, revolut_customer_id) 
				 VALUES (%d, %s) ON DUPLICATE KEY UPDATE wc_customer_id = VALUES(wc_customer_id)",
				array( get_current_user_id(), $revolut_customer_id )
			)
		); // db call ok; no-cache ok.
	}

	/**
	 * Convert saved customer session into current session.
	 *
	 * @param string $id_revolut_order Revolut order id.
	 *
	 * @return void.
	 */
	public function convert_revolut_order_metadata_into_wc_session( $id_revolut_order ) {
		WC()->initialize_session();
		WC()->initialize_cart();

		global $wpdb;
		$temp_session = $wpdb->get_row( $wpdb->prepare( 'SELECT temp_session FROM ' . $wpdb->prefix . 'wc_revolut_temp_session WHERE order_id=%s', array( $id_revolut_order ) ), ARRAY_A ); // db call ok; no-cache ok.

		$wc_order_metadata = json_decode( $temp_session['temp_session'], true );
		$id_wc_customer    = (int) $wc_order_metadata['id_customer'];

		if ( $id_wc_customer ) {
			wp_set_current_user( $id_wc_customer );
		}

		WC()->session->set( 'cart', $wc_order_metadata['cart'] );
		WC()->session->set( 'cart_totals', $wc_order_metadata['cart_totals'] );
		WC()->session->set( 'applied_coupons', $wc_order_metadata['applied_coupons'] );
		WC()->session->set( 'coupon_discount_totals', $wc_order_metadata['coupon_discount_totals'] );
		WC()->session->set( 'coupon_discount_tax_totals', $wc_order_metadata['coupon_discount_tax_totals'] );
		WC()->session->set( 'get_removed_cart_contents', $wc_order_metadata['get_removed_cart_contents'] );

		$session = new WC_Cart_Session( WC()->cart );
		$session->get_cart_from_session();
	}

	/**
	 * Get order details
	 *
	 * @param array  $address Customer address.
	 * @param bool   $shipping_required is shipping option required for the current order.
	 * @param string $gateway selected payment gateway.
	 * @throws Exception Exception.
	 */
	public function format_wc_order_details( $address, $shipping_required, $gateway ) {
		if ( empty( $address['billingAddress'] ) ) {
			throw new Exception( 'Billing address is missing' );
		}

		if ( $shipping_required && empty( $address['shippingAddress'] ) ) {
			throw new Exception( 'Shipping address is missing' );
		}

		if ( empty( $address['email'] ) ) {
			throw new Exception( 'User Email information is missing' );
		}

		$revolut_billing_address         = $address['billingAddress'];
		$revolut_customer_email          = $address['email'];
		$revolut_customer_full_name      = ! empty( $revolut_billing_address['recipient'] ) ? $revolut_billing_address['recipient'] : '';
		$revolut_customer_billing_phone  = ! empty( $revolut_billing_address['phone'] ) ? $revolut_billing_address['phone'] : '';
		$revolut_customer_shipping_phone = '';
		$wc_shipping_address             = array();

		list($billing_firstname, $billing_lastname) = $this->parse_customer_name( $revolut_customer_full_name );

		if ( isset( $address['shippingAddress'] ) && ! empty( $address['shippingAddress'] ) ) {
			$revolut_shipping_address            = $address['shippingAddress'];
			$revolut_customer_shipping_phone     = ! empty( $revolut_shipping_address['phone'] ) ? $revolut_shipping_address['phone'] : '';
			$revolut_customer_shipping_full_name = ! empty( $revolut_shipping_address['recipient'] ) ? $revolut_shipping_address['recipient'] : '';

			$shipping_firstname = $billing_firstname;
			$shipping_lastname  = $billing_lastname;

			if ( ! empty( $revolut_customer_shipping_full_name ) ) {
				list($shipping_firstname, $shipping_lastname) = $this->parse_customer_name( $revolut_customer_shipping_full_name );
			}

			if ( empty( $revolut_customer_shipping_phone ) && ! empty( $revolut_customer_billing_phone ) ) {
				$revolut_customer_shipping_phone = $revolut_customer_billing_phone;
			}

			$wc_shipping_address = $this->get_wc_shipping_address( $revolut_shipping_address, $revolut_customer_email, $revolut_customer_shipping_phone, $shipping_firstname, $shipping_lastname );
		}

		if ( empty( $revolut_customer_billing_phone ) && ! empty( $revolut_customer_shipping_phone ) ) {
			$revolut_customer_billing_phone = $revolut_customer_shipping_phone;
		}

		$wc_billing_address = $this->get_wc_billing_address( $revolut_billing_address, $revolut_customer_email, $revolut_customer_billing_phone, $billing_firstname, $billing_lastname );

		if ( $shipping_required ) {
			$wc_order_data = array_merge( $wc_billing_address, $wc_shipping_address );
		} else {
			$wc_order_data = $wc_billing_address;
		}

		$wc_order_data['ship_to_different_address']    = $shipping_required;
		$wc_order_data['revolut_pay_express_checkout'] = 'revolut_pay' === $gateway;
		$wc_order_data['terms']                        = 1;
		$wc_order_data['order_comments']               = '';

		return $wc_order_data;
	}

	/**
	 * Get first and lastname from customer full name string.
	 *
	 * @param string $full_name Customer full name.
	 */
	public function parse_customer_name( $full_name ) {
		$full_name_list = explode( ' ', $full_name );
		if ( count( $full_name_list ) > 1 ) {
			$lastname  = array_pop( $full_name_list );
			$firstname = implode( ' ', $full_name_list );
			return array( $firstname, $lastname );
		}

		$firstname = $full_name;
		$lastname  = 'undefined';

		return array( $firstname, $lastname );
	}

	/**
	 * Create billing address for order.
	 *
	 * @param array  $shipping_address Shipping address.
	 * @param string $revolut_customer_email Email.
	 * @param string $revolut_customer_phone Phone.
	 * @param string $firstname Firstname.
	 * @param string $lastname Lastname.
	 */
	public function get_wc_shipping_address( $shipping_address, $revolut_customer_email, $revolut_customer_phone, $firstname, $lastname ) {
		if ( isset( $shipping_address['country'] ) ) {
			$shipping_address['country'] = strtoupper( $shipping_address['country'] );
		}
		$address['shipping_first_name'] = $firstname;
		$address['shipping_last_name']  = $lastname;
		$address['shipping_email']      = $revolut_customer_email;
		$address['shipping_phone']      = $revolut_customer_phone;
		$address['shipping_country']    = ! empty( $shipping_address['country'] ) ? $shipping_address['country'] : '';
		$address['shipping_address_1']  = ! empty( $shipping_address['addressLine'][0] ) ? $shipping_address['addressLine'][0] : '';
		$address['shipping_address_2']  = ! empty( $shipping_address['addressLine'][1] ) ? $shipping_address['addressLine'][1] : '';
		$address['shipping_city']       = ! empty( $shipping_address['city'] ) ? $shipping_address['city'] : '';
		$address['shipping_state']      = ! empty( $shipping_address['region'] ) ? $this->convert_state_name_to_id( $shipping_address['country'], $shipping_address['region'] ) : '';
		$address['shipping_postcode']   = ! empty( $shipping_address['postalCode'] ) ? $shipping_address['postalCode'] : '';
		$address['shipping_company']    = '';

		return $address;
	}

	/**
	 * Create billing address for order.
	 *
	 * @param array  $billing_address Billing address.
	 * @param string $revolut_customer_email Email.
	 * @param string $revolut_customer_phone Phone.
	 * @param string $firstname Firstname.
	 * @param string $lastname Lastname.
	 */
	public function get_wc_billing_address( $billing_address, $revolut_customer_email, $revolut_customer_phone, $firstname, $lastname ) {
		if ( isset( $billing_address['country'] ) ) {
			$billing_address['country'] = strtoupper( $billing_address['country'] );
		}
		$address                       = array();
		$address['billing_first_name'] = $firstname;
		$address['billing_last_name']  = $lastname;

		$address['billing_email']     = $revolut_customer_email;
		$address['billing_phone']     = $revolut_customer_phone;
		$address['billing_country']   = ! empty( $billing_address['country'] ) ? $billing_address['country'] : '';
		$address['billing_address_1'] = ! empty( $billing_address['addressLine'][0] ) ? $billing_address['addressLine'][0] : '';
		$address['billing_address_2'] = ! empty( $billing_address['addressLine'][1] ) ? $billing_address['addressLine'][1] : '';
		$address['billing_city']      = ! empty( $billing_address['city'] ) ? $billing_address['city'] : '';
		$address['billing_state']     = ! empty( $billing_address['region'] ) ? $this->convert_state_name_to_id( $billing_address['country'], $billing_address['region'] ) : '';
		$address['billing_postcode']  = ! empty( $billing_address['postalCode'] ) ? $billing_address['postalCode'] : '';
		$address['billing_company']   = '';

		return $address;
	}

	/**
	 * Check if payment is pending.
	 *
	 * @param string $revolut_order_id Revolut order id.
	 */
	protected function is_pending_payment( $revolut_order_id ) {
		$revolut_order = MerchantApi::privateLegacy()->get( '/orders/' . $revolut_order_id );
		return ! isset( $revolut_order['state'] ) || ( isset( $revolut_order['state'] ) && 'PENDING' === $revolut_order['state'] );
	}

	/**
	 * Save or Update customer session temporarily.
	 *
	 * @param string $revolut_order_id Revolut order id.
	 *
	 * @throws Exception Exception.
	 */
	public function add_or_update_temp_session( $revolut_order_id ) {
		$order_metadata['id_customer']                = get_current_user_id();
		$order_metadata['cart']                       = WC()->cart->get_cart_for_session();
		$order_metadata['cart_totals']                = WC()->cart->get_totals();
		$order_metadata['applied_coupons']            = WC()->cart->get_applied_coupons();
		$order_metadata['coupon_discount_totals']     = WC()->cart->get_coupon_discount_totals();
		$order_metadata['coupon_discount_tax_totals'] = WC()->cart->get_coupon_discount_tax_totals();
		$order_metadata['get_removed_cart_contents']  = WC()->cart->get_removed_cart_contents();

		$temp_session = wp_json_encode( $order_metadata );

		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				'INSERT INTO ' . $wpdb->prefix . 'wc_revolut_temp_session (order_id, temp_session)
            VALUES (%s, %s) ON DUPLICATE KEY UPDATE temp_session =  VALUES(temp_session)',
				array(
					$revolut_order_id,
					$temp_session,
				)
			)
		); // db call ok; no-cache ok.
	}

	/**
	 * Get Revolut customer id.
	 *
	 * @param int $wc_customer_id WooCommerce customer id.
	 */
	public function get_revolut_customer_id( $wc_customer_id = false ) {
		if ( ! $wc_customer_id ) {
			$wc_customer_id = get_current_user_id();
		}

		if ( empty( $wc_customer_id ) ) {
			return null;
		}

		global $wpdb;
		$revolut_customer_id = $wpdb->get_col( $wpdb->prepare( 'SELECT revolut_customer_id FROM ' . $wpdb->prefix . 'wc_revolut_customer WHERE wc_customer_id=%s', array( $wc_customer_id ) ) ); // db call ok; no-cache ok.
		$revolut_customer_id = reset( $revolut_customer_id );

		if ( empty( $revolut_customer_id ) ) {
			$revolut_customer_id = '';
		}

		$revolut_customer_id_with_mode = explode( '_', $revolut_customer_id );

		if ( count( $revolut_customer_id_with_mode ) > 1 ) {
			list( $api_mode, $revolut_customer_id ) = $revolut_customer_id_with_mode;

			if ( $api_mode !== $this->config_provider->getConfig()->getMode() ) {
				$this->delete_customer_record( $wc_customer_id );
				return null;
			}
		}

		if ( empty( $revolut_customer_id ) ) {
			return null;
		}

		// verify customer id through api.
		$revolut_customer = MerchantApi::privateLegacy()->get( '/customers/' . $revolut_customer_id );

		if ( empty( $revolut_customer['id'] ) ) {
			$this->delete_customer_record( $wc_customer_id );
			return null;
		}

		return $revolut_customer_id;
	}

	/**
	 * Remove customer db record
	 *
	 * @param string $wc_customer_id customer id.
	 */
	public function delete_customer_record( $wc_customer_id ) {
		global $wpdb;
		$wpdb->delete( // phpcs:ignore
			$wpdb->prefix . 'wc_revolut_customer',
			array(
				'wc_customer_id' => $wc_customer_id,
			)
		);
	}

	/**
	 * Update Revolut Order Total
	 *
	 * @param float  $order_total Order total.
	 * @param string $currency Order currency.
	 * @param string $public_id Order public id.
	 *
	 * @return bool
	 * @throws Exception Exception.
	 */
	public function update_revolut_order_total( $order_total, $currency, $public_id ) {
		$order_id = $this->get_revolut_order_by_public_id( $public_id );

		$order_total = round( $order_total, 2 );

		$revolut_order_total = $this->get_revolut_order_total( $order_total, $currency );

		$body = array(
			'amount'   => $revolut_order_total,
			'currency' => $currency,
		);

		if ( empty( $order_id ) ) {
			return false;
		}

		$revolut_order = MerchantApi::privateLegacy()->get( "/orders/$order_id" );

		$revolut_order_amount = $this->get_revolut_order_amount( $revolut_order );

		if ( $revolut_order_amount === $revolut_order_total ) {
			return true;
		}

		if ( ! isset( $revolut_order['public_id'] ) || ! isset( $revolut_order['id'] ) || 'PENDING' !== $revolut_order['state'] ) {
			return false;
		}

		$revolut_order = MerchantApi::privateLegacy()->patch( "/orders/$order_id", $body );

		if ( ! isset( $revolut_order['public_id'] ) || ! isset( $revolut_order['id'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Fetch Revolut order by public id
	 *
	 * @param String $public_id Revolut public id.
	 *
	 * @return string|null
	 */
	public function get_revolut_order_by_public_id( $public_id ) {
		global $wpdb;
		// resolve into order_id.
		return $this->uuid_dashes(
			$wpdb->get_col( // phpcs:ignore
				$wpdb->prepare(
					'SELECT HEX(order_id) FROM ' . $wpdb->prefix . 'wc_revolut_orders
                WHERE public_id=UNHEX(REPLACE(%s, "-", ""))',
					array( $public_id )
				)
			)
		);
	}

	/**
	 * Load Merchant Public Key from API.
	 *
	 * @return string
	 */
	public function get_merchant_public_api_key() {
		try {
			$merchant_public_key = $this->get_revolut_merchant_public_key();

			if ( ! empty( $merchant_public_key ) ) {
				return $merchant_public_key;
			}

			$merchant_public_key = $this->update_revolut_merchant_public_key();
			return $merchant_public_key;
		} catch ( Exception $e ) {
			$this->log_error( 'get_merchant_public_api_key: ' . $e->getMessage() );
			return '';
		}
	}

	/**
	 * Check Merchant Account features.
	 *
	 * @param String $feature_flag Feature flag name.
	 *
	 * @return bool
	 */
	public function check_feature_support( $feature_flag ) {
		try {
			$merchant_features = MerchantApi::public()->get( '/merchant' );

			return isset( $merchant_features['features'] ) && is_array( $merchant_features['features'] ) && in_array(
				$feature_flag,
				$merchant_features['features'],
				true
			);
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Checks if page is pay for order and change subs payment page.
	 *
	 * @return bool
	 */
	public function is_subs_change_payment() {
		return get_query_var( 'pay_for_order' ) && get_query_var( 'change_payment_method' );
	}

	/**
	 * Unset Revolut public_id
	 */
	protected function unset_revolut_public_id() {
		WC()->session->__unset( "{$this->config_provider->getConfig()->getMode()}_revolut_public_id" );
	}

	/**
	 * Unset Revolut public_id
	 */
	protected function unset_revolut_pbb_checkout_public_id() {
		$mode = $this->config_provider->getConfig()->getMode();
		WC()->session->__unset( $mode . '_revolut_pbb_order_public_id' );
	}

	/**
	 * Set Revolut public_id
	 *
	 * @param string $value Revolut public id.
	 */
	public function set_revolut_public_id( $value ) {
		WC()->session->set( "{$this->config_provider->getConfig()->getMode()}_revolut_public_id", $value );
	}

	/**
	 * Get Revolut public_id
	 *
	 * @return array|string|null
	 */
	public function get_revolut_public_id() {
		$public_id = WC()->session->get( "{$this->config_provider->getConfig()->getMode()}_revolut_public_id" );

		if ( empty( $public_id ) ) {
			return null;
		}

		$order_id = $this->get_revolut_order_by_public_id( $public_id );

		if ( empty( $order_id ) ) {
			return null;
		}

		return $public_id;
	}

	/**
	 * Set Revolut public_id
	 *
	 * @param string $value Revolut public id.
	 */
	public function set_revolut_pbb_order_public_id( $value ) {
		$mode = $this->config_provider->getConfig()->getMode();
		return WC()->session->set( $mode . '_revolut_pbb_order_public_id', $value );
	}

	/**
	 * Get Revolut pbb order public_id
	 *
	 * @return array|string|null
	 */
	public function get_revolut_pbb_order_public_id() {
		$mode = $this->config_provider->getConfig()->getMode();
		return WC()->session->get( $mode . '_revolut_pbb_order_public_id' );
	}


	/**
	 * Get Revolut Merchant Public Key
	 *
	 * @return array|string|null
	 */
	protected function get_revolut_merchant_public_key() {
		return get_option( "{$this->config_provider->getConfig()->getMode()}_revolut_merchant_public_key" );
	}

	/**
	 * Set  Revolut Merchant Public Key
	 */
	protected function update_revolut_merchant_public_key() {
		$merchant_public_key = MerchantApi::private()->get( WC_GATEWAY_PUBLIC_KEY_ENDPOINT );
		$merchant_public_key = isset( $merchant_public_key['public_key'] ) ? $merchant_public_key['public_key'] : '';
		update_option( "{$this->config_provider->getConfig()->getMode()}_revolut_merchant_public_key", $merchant_public_key );
		return $merchant_public_key;
	}

	/**
	 * Replace dashes
	 *
	 * @param mixed $uuid uuid.
	 *
	 * @return string|string[]|null
	 */
	protected function uuid_dashes( $uuid ) {
		if ( is_array( $uuid ) ) {
			if ( isset( $uuid[0] ) ) {
				$uuid = $uuid[0];
			}
		}

		$result = preg_replace( '/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i', '$1-$2-$3-$4-$5', $uuid );

		return $result;
	}

	/**
	 * Check if is not minor currency
	 *
	 * @param string $currency currency.
	 *
	 * @return bool
	 */
	public function is_zero_decimal( $currency ) {
		return 'jpy' === strtolower( $currency );
	}

	/**
	 * Get order total for Api.
	 *
	 * @param float  $order_total order total amount.
	 * @param string $currency currency.
	 */
	public function get_revolut_order_total( $order_total, $currency ) {
		$order_total = round( (float) $order_total, 2 );

		if ( ! $this->is_zero_decimal( $currency ) ) {
			$order_total = round( $order_total * 100 );
		}

		return (int) $order_total;
	}

	/**
	 * Get order total for WC order.
	 *
	 * @param float  $revolut_order_total order total amount.
	 * @param string $currency currency.
	 */
	public function get_wc_order_total( $revolut_order_total, $currency ) {
		$order_total = $revolut_order_total;

		if ( ! $this->is_zero_decimal( $currency ) ) {
			$order_total = round( $order_total / 100, 2 );
		}

		return $order_total;
	}

	/**
	 * Get total amount value from Revolut order.
	 *
	 * @param array $revolut_order Revolut order.
	 */
	public function get_revolut_order_amount( $revolut_order ) {
		return isset( $revolut_order['order_amount'] ) && isset( $revolut_order['order_amount']['value'] ) ? (int) $revolut_order['order_amount']['value'] : 0;
	}

	/**
	 * Get shipping amount value from Revolut order.
	 *
	 * @param array $revolut_order Revolut order.
	 */
	public function get_revolut_order_total_shipping( $revolut_order ) {
		$shipping_total = isset( $revolut_order['delivery_method'] ) && isset( $revolut_order['delivery_method']['amount'] ) ? (int) $revolut_order['delivery_method']['amount'] : 0;
		$currency       = $this->get_revolut_order_currency( $revolut_order );

		if ( $shipping_total ) {
			return $this->get_wc_order_total( $shipping_total, $currency );
		}

		return 0;
	}

	/**
	 * Get currency from Revolut order.
	 *
	 * @param array $revolut_order Revolut order.
	 */
	public function get_revolut_order_currency( $revolut_order ) {
		return isset( $revolut_order['order_amount'] ) && isset( $revolut_order['order_amount']['currency'] ) ? $revolut_order['order_amount']['currency'] : '';
	}

	/**
	 * Get total shipping price.
	 */
	public function get_cart_total_shipping() {
		$cart_totals    = WC()->session->get( 'cart_totals' );
		$shipping_total = 0;
		if ( ! empty( $cart_totals ) && is_array( $cart_totals ) && in_array( 'shipping_total', array_keys( $cart_totals ), true ) ) {
			$shipping_total = $cart_totals['shipping_total'];
		}

		return $this->get_revolut_order_total( $shipping_total, get_woocommerce_currency() );
	}

	/**
	 * Get two-digit language iso code.
	 */
	public function get_lang_iso_code() {
		return substr( get_locale(), 0, 2 );
	}

	/**
	 * Check order status
	 *
	 * @param String $order_status data for checking.
	 */
	public function check_is_order_has_capture_status( $order_status ) {
		if ( 'authorize' !== $this->api_settings->get_option( 'payment_action' ) ) {
			return false;
		}

		if ( 'yes' !== $this->api_settings->get_option( 'accept_capture' ) ) {
			return false;
		}

		$order_status                 = ( 0 !== strpos( $order_status, 'wc-' ) ) ? 'wc-' . $order_status : $order_status;
		$selected_capture_status_list = $this->api_settings->get_option( 'selected_capture_status_list' );
		$customize_capture_status     = $this->api_settings->get_option( 'customise_capture_status' );

		if ( empty( $selected_capture_status_list ) || 'no' === $customize_capture_status ) {
			$selected_capture_status_list = array( 'wc-processing', 'wc-completed' );
		}

		return in_array( $order_status, $selected_capture_status_list, true );
	}

	/**
	 * Get available card brands
	 *
	 * @param string $amount order amount.
	 * @param string $currency order currency.
	 */
	public function get_available_card_brands( $amount, $currency ) {
		try {
			$available_card_brands = get_option( "revolut_{$this->config_provider->getConfig()->getMode()}_{$currency}_available_card_brands" );

			if ( ! $available_card_brands ) {
				$available_card_brands = $this->fetch_available_payment_methods_and_brand_logos( $amount, $currency )['card_brands'];
			}
			return $available_card_brands;
		} catch ( Exception $e ) {
			$this->log_error( 'get_available_card_brands: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Get available payment methods
	 *
	 * @param string $amount order amount.
	 * @param string $currency order currency.
	 */
	public function get_available_payment_methods( $amount, $currency ) {
		try {
			$available_payment_methods = get_option( "revolut_{$this->config_provider->getConfig()->getMode()}_{$currency}_available_payment_methods" );

			if ( ! $available_payment_methods ) {
				$available_payment_methods = $this->fetch_available_payment_methods_and_brand_logos( $amount, $currency )['payment_methods'];
			}

			return $available_payment_methods;

		} catch ( Exception $e ) {
			$this->log_error( 'get_available_payment_methods: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Check the current page
	 */
	public function is_order_payment_page() {
		try {
			global $wp;
			return is_checkout() && ! empty( $wp->query_vars['order-pay'] );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Check the current page
	 *
	 * @param string|array $payment_method Payment method name.
	 */
	public function is_payment_method_available( $payment_method ) {
		try {
			$total = 1;

			if ( WC()->cart ) {
				$total = WC()->cart->get_total( '' );
			}

			$currency = get_woocommerce_currency();
			$total    = $this->get_revolut_order_total( $total, $currency );

			$available_payment_methods = $this->get_available_payment_methods( $total, $currency );

			if ( is_array( $payment_method ) ) {
				return count( array_intersect( $payment_method, $available_payment_methods ) );
			}

			return in_array( $payment_method, $available_payment_methods, true );
		} catch ( Exception $e ) {
			$this->log_error( 'is_payment_method_available: ' . $e->getMessage() );
			return false;
		}

		return false;
	}

	/**
	 * Update available payment methods in DB
	 *
	 * @param string $amount amount.
	 * @param string $currency stores default currency.
	 */
	public function fetch_available_payment_methods_and_brand_logos( $amount = 0, $currency = '' ) {

		$currency = empty( $currency ) ? get_woocommerce_currency() : $currency;
		$amount   = empty( $amount ) ? 1 : $amount;

		$available_payment_methods = array();
		$available_card_brands     = array();

		$order_details = MerchantApi::public()->get( "/available-payment-methods?amount=$amount&currency=$currency" );

		if ( isset( $order_details['available_payment_methods'] ) && ! empty( $order_details['available_payment_methods'] ) ) {
			$available_payment_methods = array_map( 'strtolower', $order_details['available_payment_methods'] );
			update_option( "revolut_{$this->config_provider->getConfig()->getMode()}_{$currency}_available_payment_methods", $available_payment_methods );
		}

		if ( isset( $order_details['available_card_brands'] ) && ! empty( $order_details['available_card_brands'] ) ) {
			$available_card_brands = array_map( 'strtolower', $order_details['available_card_brands'] );
			update_option( "revolut_{$this->config_provider->getConfig()->getMode()}_{$currency}_available_card_brands", $available_card_brands );
		}
		return array(
			'payment_methods' => $available_payment_methods,
			'card_brands'     => $available_card_brands,
		);
	}

	/**
	 * Loads order meta data based on a partial key
	 *
	 * @param object $order WC Order object.
	 * @param array  $metakey_list key of meta data.
	 */
	public function get_order_meta_by_partial_key( $order, $metakey_list ) {
		foreach ( $order->get_meta_data() as $meta ) {
			foreach ( $metakey_list as $meta_key ) {
				if ( strpos( $meta->key, $meta_key ) !== false ) {
					return $meta->value;
				}
			}
		}

		return '';
	}

	/**
	 * Traverse meta data on a given order, looking for shipment data on known shipping plugins.
	 *
	 * @param object $wc_order WC Order object.
	 */
	public function get_shipments_data_by_known_plugins( $wc_order ) {
		$shipments = array();

		foreach ( $wc_order->get_meta_data() as $meta ) {

			switch ( $meta->key ) {
				// WooCommerce Shipment Tracking Plugin meta key.
				case '_wc_shipment_tracking_items':
					if ( ! is_array( $meta->value ) ) {
						break;
					}

					foreach ( $meta->value as $tracking_item ) {
						$shipping_company = ! empty( $tracking_item['custom_tracking_provider'] ) ? $tracking_item['custom_tracking_provider'] : $tracking_item['tracking_provider'];
						$tracking_number  = $tracking_item['tracking_number'];

						if ( empty( $shipping_company ) || empty( $tracking_number ) ) {
							continue;
						}

						array_push(
							$shipments,
							array(
								'tracking_number'       => $tracking_number,
								'shipping_company_name' => $shipping_company,
							)
						);
					}
			}
		}

		return $shipments;

	}

	/**
	 * Traverse meta data on a given order, looking for shipment data by approximating meta keys.
	 *
	 * @param object $wc_order WC Order object.
	 */
	public function get_shipments_data_by_approximate_meta_keys( $wc_order ) {
		$shipments = array();

		$shipping_company = $this->get_order_meta_by_partial_key( $wc_order, array( 'shipping_company', 'shipping_provider', 'tracking_provider' ) );
		$tracking_number  = $this->get_order_meta_by_partial_key( $wc_order, array( 'tracking_number', 'tracking_code' ) );

		if ( ! empty( $shipping_company ) && ! empty( $tracking_number ) ) {
			array_push(
				$shipments,
				array(
					'tracking_number'       => $tracking_number,
					'shipping_company_name' => $shipping_company,
				)
			);
		}

		return $shipments;
	}
}
