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
use Revolut\Wordpress\ServiceProvider;
use Revolut\Plugin\Services\Log\RLog;

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
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function create_subscription_order( WC_Revolut_Order_Descriptor $order_descriptor ) {
		$payment_action     = $this->api_settings->get_option( 'payment_action' );
		$auto_mode_required = 'authorize_and_capture' === $payment_action;

		return $this->create_revolut_order( $order_descriptor, false, $auto_mode_required );
	}

	/**
	 * Create Revolut Order
	 *
	 * @param WC_Revolut_Order_Descriptor $order_descriptor Revolut Order Descriptor.
	 *
	 * @param bool                        $is_express_checkout indicator.
	 * @param bool                        $auto_mode_required  indicator.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function create_revolut_order( WC_Revolut_Order_Descriptor $order_descriptor, $is_express_checkout = false, $auto_mode_required = false ) {
		$payment_action = $this->api_settings->get_option( 'payment_action' );
		$capture_mode   = $auto_mode_required ? 'automatic' : 'manual';

		$body = array(
			'amount'       => $order_descriptor->amount,
			'currency'     => $order_descriptor->currency,
			'capture_mode' => $capture_mode,
		);

		if ( 'authorize_and_capture' === $payment_action && ! $auto_mode_required ) {
			$body['cancel_authorised_after'] = WC_REVOLUT_AUTO_CANCEL_TIMEOUT;
		}

		if ( ! empty( $order_descriptor->revolut_customer_id ) ) {
			$body['customer'] = array( 'id' => $order_descriptor->revolut_customer_id );
		}

		// needed in address validation for RPay fast checkout orders.
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
		 * @param Bool                        $is_pay_by_bank is pay_by_bank method.
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
	 * Check if existing checkout order already processed
	 *
	 * @param string $revolut_public_id Revolut revolut_public_id.
	 */
	public function check_existing_checkout_order_already_processed( $revolut_public_id = null ) {
		try {
			if ( empty( $revolut_public_id ) ) {
				return;
			}

			$oder_ids = $this->get_order_ids_by_public_id( $revolut_public_id );

			if ( empty( $oder_ids['wc_order_id'] ) || empty( $oder_ids['order_id'] ) ) {
				return;
			}

			$redirect_url = $this->check_existing_order_already_processed( $oder_ids['wc_order_id'], $oder_ids['order_id'] );

			return $redirect_url;
		} catch ( Exception $e ) {
			$this->log_error( 'check_existing_checkout_order_already_processed failed : ' . $e->getMessage() );
		}
	}

	/**
	 * Check if existing checkout order already processed
	 */
	public function check_existing_order_pay_page_order_already_processed() {
		try {
			if ( ! ( (bool) get_query_var( 'pay_for_order' ) ) || empty( get_query_var( 'key' ) ) ) {
				return;
			}

			global $wp;

			$wc_order_id = wc_clean( $wp->query_vars['order-pay'] );

			if ( ! $wc_order_id ) {
				return;
			}

			$revolut_order_id = $this->get_revolut_order_by_wc_order_id( $wc_order_id );

			if ( empty( $revolut_order_id ) ) {
				return;
			}

			$redirect_url = $this->check_existing_order_already_processed( $wc_order_id, $revolut_order_id );

			return $redirect_url;
		} catch ( Exception $e ) {
			$this->log_error( 'check_existing_order_pay_page_order_already_processed failed : ' . $e->getMessage() );
		}
	}

	/**
	 * Check if existing order already processed
	 *
	 * @param int    $wc_order_id WooCommerce order id.
	 * @param string $revolut_order_id Revolut order id.
	 */
	public function check_existing_order_already_processed( $wc_order_id, $revolut_order_id ) {
		try {
			if ( empty( $wc_order_id ) || empty( $revolut_order_id ) ) {
				return;
			}

			$revolut_order = MerchantApi::private()->get( "/orders/$revolut_order_id" );

			if ( ! isset( $revolut_order['state'] ) || 'pending' === $revolut_order['state'] ) {
				return;
			}

			$wc_order = wc_get_order( (int) $wc_order_id );

			if ( ! $wc_order || ! $wc_order->get_id() ) {
				return;
			}


			switch ( $revolut_order['state'] ) {
				case 'authorised':
					$this->capture_payment( $revolut_order_id );
					$this->process_authorised_order( $revolut_order_id, $wc_order_id, false );

					$this->unset_revolut_public_id();
					$this->unset_revolut_pbb_checkout_public_id();

					return $wc_order->get_checkout_order_received_url();
				case 'completed':
					$this->process_captured_order( $revolut_order_id, $wc_order_id );

					$this->unset_revolut_public_id();
					$this->unset_revolut_pbb_checkout_public_id();

					return $wc_order->get_checkout_order_received_url();
				case 'processing':
				case 'cancelled':
				case 'failed':
					$wc_order->update_status( 'cancelled' );
					$this->unset_revolut_public_id();
					$this->unset_revolut_pbb_checkout_public_id();
					break;
			}
		} catch ( Exception $e ) {
			$this->log_error( 'check_existing_order_already_processed failed : ' . $e->getMessage() );
		}
	}

	/**
	 * Check if order already has another payment and process.
	 *
	 * @param string   $new_revolut_order_id Revolut order id.
	 * @param WC_Order $wc_order WooCommerce order.
	 *
	 * @throws Exception Exception.
	 */
	protected function check_order_already_has_payment( $new_revolut_order_id, $wc_order ) {
		try {
			if ( ! $wc_order ) {
				throw new Exception( 'wc order not exist' );
			}

			if ( ! $new_revolut_order_id ) {
				throw new Exception( 'revolut order not exist' );
			}

			$wc_order_id = $wc_order->get_id();

			global $wpdb;

			$exist_wc_order_id = $wpdb->get_row( $wpdb->prepare( 'SELECT wc_order_id, HEX(order_id) as order_id, HEX(public_id) as public_id FROM ' . $wpdb->prefix . 'wc_revolut_orders WHERE wc_order_id=%d', array( $wc_order_id ) ), ARRAY_A ); // db call ok; no-cache ok.

			if ( ! empty( $exist_wc_order_id ) && ! empty( $exist_wc_order_id['wc_order_id'] ) ) {
				$exist_revolut_order_id  = $this->uuid_dashes( $exist_wc_order_id['order_id'] );
				$exist_revolut_public_id = $this->uuid_dashes( $exist_wc_order_id['public_id'] );

				if ( strtolower( $exist_revolut_order_id ) === strtolower( $new_revolut_order_id ) ) {
					return '';
				}

				$revolut_order = MerchantApi::private()->get( "/orders/$exist_revolut_order_id" );


				if ( empty( $revolut_order['state'] ) ) {
					throw new Exception( "Can not load exist payment : $exist_revolut_order_id " );
				}

				$is_automatic = 'automatic' === $revolut_order['capture_mode'];

				$this->update_payment_method_title( $exist_revolut_order_id, $wc_order );

				switch ( $revolut_order['state'] ) {
					case 'authorised':
						$this->capture_payment( $exist_revolut_order_id );
						$this->process_authorised_order( $exist_revolut_order_id, $wc_order_id, false );

						$this->unset_revolut_public_id();
						$this->unset_revolut_pbb_checkout_public_id();

						$wc_order->update_meta_data( 'revolut_payment_public_id', $exist_revolut_public_id );
						$wc_order->update_meta_data( 'revolut_payment_order_id', $exist_revolut_order_id );


						return $wc_order->get_checkout_order_received_url();
					case 'completed':
						$this->process_captured_order( $exist_revolut_order_id, $wc_order_id );

						$this->unset_revolut_public_id();
						$this->unset_revolut_pbb_checkout_public_id();

						$wc_order->update_meta_data( 'revolut_payment_public_id', $exist_revolut_public_id );
						$wc_order->update_meta_data( 'revolut_payment_order_id', $exist_revolut_order_id );

						return $wc_order->get_checkout_order_received_url();
					case 'processing':
						if ( $is_automatic ) {
							$wc_order->update_status( 'on-hold' );
							$this->handle_revolut_order_result( $wc_order, $exist_revolut_order_id );

							$wc_order->update_meta_data( 'revolut_payment_public_id', $exist_revolut_public_id );
							$wc_order->update_meta_data( 'revolut_payment_order_id', $exist_revolut_order_id );

							return $wc_order->get_checkout_order_received_url();
						}

						$this->set_wc_order_record_to_null( $wc_order_id );
						break;
					case 'cancelled':
					case 'failed':
					case 'pending':
						$this->set_wc_order_record_to_null( $wc_order_id );
						break;
				}
			}
		} catch ( Exception $e ) {
			$this->log_error( 'check_order_already_has_payment: ' . $e->getMessage() );
		}
	}

	/**
	 * Update internal table to avoid piggybacking on already paid order.
	 *
	 * @param string $public_id Revolut public id.
	 * @param string $revolut_order_id Revolut order id.
	 * @param int    $wc_order_id WooCommerce order id.
	 * @param string $wc_order_number WooCommerce order id.
	 *
	 * @throws Exception Exception.
	 */
	protected function save_wc_order_id( $public_id, $revolut_order_id, $wc_order_id, $wc_order_number ) {
		try {
			global $wpdb;

			$exist_wc_order_id = $wpdb->get_row( $wpdb->prepare( 'SELECT wc_order_id FROM ' . $wpdb->prefix . 'wc_revolut_orders WHERE wc_order_id=%d', array( $wc_order_id ) ), ARRAY_A ); // db call ok; no-cache ok.

			if ( ! empty( $exist_wc_order_id ) && ! empty( $exist_wc_order_id['wc_order_id'] ) ) {
				$updated_rows = $wpdb->query(
					$wpdb->prepare(
						'UPDATE ' . $wpdb->prefix . "wc_revolut_orders
						SET order_id=UNHEX(REPLACE(%s, '-', '')), public_id=UNHEX(REPLACE(%s, '-', ''))
						WHERE wc_order_id=%d",
						array( $revolut_order_id, $public_id, $wc_order_id )
					)
				); // db call ok; no-cache ok.
			} else {
				$updated_rows = $wpdb->query(
					$wpdb->prepare(
						'UPDATE ' . $wpdb->prefix . 'wc_revolut_orders
						SET wc_order_id=%d
						WHERE public_id=UNHEX(REPLACE(%s, "-", ""))',
						array( $wc_order_id, $public_id )
					)
				); // db call ok; no-cache ok.
			}

			if ( 1 !== $updated_rows && ! empty( $wpdb->last_error ) ) {
				$this->log_error( 'Can not update wc_order_id for Revolut order record on DB: ' . $wpdb->last_error );
				return false;
			}

			$merchant_order_ext_ref = $this->advanced_settings->external_order_reference_is_order_id() ? $wc_order_id : $wc_order_number;

			$body = array(
				'merchant_order_ext_ref' => $merchant_order_ext_ref,
			);

			MerchantApi::privateLegacy()->patch( "/orders/$revolut_order_id", $body );
		} catch ( Exception $e ) {
			$this->log_error( $e->getMessage() );
		}
	}

	/**
	 * Delete payment record.
	 *
	 * @param int $order_id Woo Order Id.
	 *
	 * @return void
	 */
	public function delete_revolut_order_record( $order_id ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . $wpdb->prefix . 'wc_revolut_orders WHERE wc_order_id=%s',
				array( $order_id )
			)
		); // db call ok; no-cache ok.
	}

	/**
	 * Delete order mapping record.
	 *
	 * @param int $order_id Woo Order Id.
	 *
	 * @return void
	 */
	public function set_wc_order_record_to_null( $order_id ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . $wpdb->prefix . 'wc_revolut_orders set wc_order_id = null WHERE wc_order_id=%s',
				array( $order_id )
			)
		); // db call ok; no-cache ok.
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param string   $revolut_order_id Revolut order id.
	 * @param WC_Order $wc_order WooCommerce order.
	 */
	protected function update_payment_method_title( $revolut_order_id, $wc_order ) {
		try {
			$revolut_order = MerchantApi::privateLegacy()->get( '/orders/' . $revolut_order_id );

			if ( ! isset( $revolut_order['payments'][0]['payment_method']['type'] ) || empty( $revolut_order['payments'][0]['payment_method']['type'] ) ) {
				return;
			}

			$payment_method = $revolut_order['payments'][0]['payment_method']['type'];

			if ( 'APPLE_PAY' === $payment_method ) {
				$payment_method_title = 'Apple Pay (via Revolut)';
				$wc_order->set_payment_method_title( $payment_method_title );
				$wc_order->set_payment_method( WC_Gateway_Revolut_Payment_Request::GATEWAY_ID );
			} elseif ( 'GOOGLE_PAY' === $payment_method ) {
				$payment_method_title = 'Google Pay (via Revolut)';
				$wc_order->set_payment_method_title( $payment_method_title );
				$wc_order->set_payment_method( WC_Gateway_Revolut_Payment_Request::GATEWAY_ID );
			} elseif ( 'OPEN_BANKING' === $payment_method ) {
				$wc_order->set_payment_method( WC_Gateway_Revolut_Pay_By_Bank::GATEWAY_ID );
				$wc_order->set_payment_method_title( WC_Gateway_Revolut_Pay_By_Bank::GATEWAY_TITLE );
			} else {
				return;
			}

			$wc_order->save();
		} catch ( Exception $e ) {
			$this->log_error( $e->getMessage() );
		}
	}

	/**
	 * Verify is paid amount and order total are equal
	 *
	 * @param string   $revolut_order_id Revolut order id.
	 * @param WC_Order $wc_order WooCommerce order.
	 *
	 * @throws Exception Exception.
	 */
	protected function verify_order_total( $revolut_order_id, $wc_order ) {
		$revolut_order          = MerchantApi::privateLegacy()->get( '/orders/' . $revolut_order_id );
		$revolut_order_total    = $this->get_revolut_order_amount( $revolut_order );
		$revolut_order_currency = $this->get_revolut_order_currency( $revolut_order );

		if ( empty( $revolut_order_total ) || empty( $revolut_order_currency ) ) {
			/* translators: %s: Revolut order id. */
			$wc_order->add_order_note( sprintf( __( 'Can\'t retrieve payment amount for this order. Please check your Revolut Business account (Order ID: %s)', 'revolut-gateway-for-woocommerce' ), $revolut_order_id ) );
			return;
		}

		$wc_order_currency = $wc_order->get_currency();
		$wc_order_total    = $this->get_revolut_order_total( $wc_order->get_total(), $wc_order_currency );

		if ( $wc_order_total !== $revolut_order_total || strtolower( $revolut_order_currency ) !== strtolower( $wc_order_currency ) ) {
			if ( abs( $wc_order_total - $revolut_order_total ) < 100 ) {
				return;
			}

			$wc_order_total      = $this->get_wc_order_total( $wc_order_total, $wc_order_currency );
			$revolut_order_total = $this->get_wc_order_total( $revolut_order_total, $revolut_order_currency );

			$order_message  = '<b>Difference detected between order and payment total.</b> Please verify order with the customer. (Order ID: ' . $revolut_order_id . ').';
			$order_message .= ' Order Total: ' . $wc_order->get_total() . strtoupper( $wc_order_currency );
			$order_message .= ' Paid amount: ' . $revolut_order_total . strtoupper( $revolut_order_currency );

			$wc_order->add_order_note( wp_kses_post( $order_message ) );
			$wc_order->update_status( 'on-hold' );
		}
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

			$shipping_details = $this->collect_order_shipping_info( $order );
			$order_details    = array(
				'line_items' => $this->collect_order_line_items( $order ),
				'shipping'   => ! empty( $shipping_details ) ? $shipping_details : null,
			);

			MerchantApi::private()->patch( "/orders/$revolut_order_id", $order_details );
		} catch ( Exception $e ) {
			$this->log_error( 'save_order_line_items failed : ' . $e->getMessage() );
		}
	}

	/**
	 * Validate order shipping information
	 *
	 * @param WC_Order $order WC order.
	 * @return bool Valid.
	 */
	private function validate_order_shipping_info( $order ) {
		$shipping_address = $order->get_address( 'shipping' );
		$missing_fields   = array();

		$required = array( 'country', 'address_1', 'postcode', 'city' );
		foreach ( $required as $field ) {
			if ( empty( $shipping_address[ $field ] ) ) {
				$missing_fields[] = "shipping_{$field}";
			}
		}

		if ( 2 !== strlen( $shipping_address['country'] ) ) {
			$missing_fields[] = 'shipping_country (invalid format)';
		}

		if ( ! empty( $missing_fields ) ) {
			$this->log_error( sprintf( 'valdiate_order_shipping_info:  order_id %s - missing_fields : %s', $order->get_id(), wp_json_encode( $missing_fields ) ) );
			return false;
		}

		return true;
	}

	/**
	 * Collect order shipping information
	 *
	 * @param WC_Order $order WC order.
	 * @return array Shipping information.
	 */
	public function collect_order_shipping_info( $order ) {
		if ( ! $this->validate_order_shipping_info( $order ) ) {
			return array();
		}

		$shipping_address = $order->get_address( 'shipping' );
		$billing_phone    = preg_replace( '/[^0-9]/', '', $order->get_billing_phone() );

		$contact_name = null;
		if ( ! empty( $shipping_address['first_name'] ) && ! empty( $shipping_address['last_name'] ) ) {
			$contact_name = trim( $shipping_address['first_name'] . ' ' . $shipping_address['last_name'] );
		}

		$shipping = array(
			'address' => array(
				'street_line_1' => $shipping_address['address_1'],
				'street_line_2' => ! empty( $shipping_address['address_2'] ) ? $shipping_address['address_2'] : null,
				'region'        => ! empty( $shipping_address['state'] ) ? $shipping_address['state'] : null,
				'city'          => $shipping_address['city'],
				'country_code'  => $shipping_address['country'],
				'postcode'      => $shipping_address['postcode'],
			),
			'contact' => array(
				'name'  => $contact_name,
				'email' => $order->get_billing_email(),
				'phone' => ! empty( $billing_phone ) ? $billing_phone : null,
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
	 * Process Capatred WooCommerce Order
	 *
	 * @param string $revolut_order_id Revolut Payment id.
	 * @param id     $wc_order_id WooCommerce order id.
	 */
	protected function process_captured_order( $revolut_order_id, $wc_order_id ) {
		$revolut_order_id            = strtolower( $revolut_order_id );
		$process_captured_order_lock = ServiceProvider::processCapturedOrderLock( $revolut_order_id );

		if ( ! $process_captured_order_lock->acquire() ) {
			return false;
		}

		try {
			$wc_order = wc_get_order( $wc_order_id );

			$options = ServiceProvider::optionRepository();

			$is_revolut_order_processed = (int) $options->get( 'is_revolut_order_processed_' . $revolut_order_id );

			if ( $is_revolut_order_processed ) {
				return false;
			}

			if ( ! $this->is_completed_payment( $revolut_order_id ) ) {
				return false;
			}

			$wc_order->payment_complete( $revolut_order_id );
			$wc_order->add_order_note( 'Payment has been successfully captured (Order ID: ' . $revolut_order_id . ').' );
			$options->add( 'is_revolut_order_processed_' . $revolut_order_id, 1 );
			$wc_order->save();

			$this->verify_order_total( $revolut_order_id, $wc_order );
		} finally {
			$process_captured_order_lock->release();
		}

		return true;
	}

	/**
	 * Complete WC order if not
	 *
	 * @param WC_Order $wc_order Wc order.
	 * @return void
	 */
	public function maybe_complete_wc_order( $wc_order ) {

		$irrelevent_statuses = array( 'completed', 'refunded', 'processing' );
		$wc_order_status     = $wc_order->get_status();

		if ( in_array( $wc_order_status, $irrelevent_statuses, true ) ) {
			return;
		}

		$wc_order->payment_complete();
	}


	/**
	 * Process Authorised WooCommerce Order
	 *
	 * @param string $revolut_order_id        Revolut Payment id.
	 * @param id     $wc_order_id             WooCommerce order id.
	 * @param bool   $is_subscription_payment subscription payment indicator.
	 * @param bool   $is_webhook              webhook indicator.
	 */
	protected function process_authorised_order( $revolut_order_id, $wc_order_id, $is_subscription_payment, $is_webhook = false ) {
		$revolut_order_id            = strtolower( $revolut_order_id );
		$process_authorised_order    = ServiceProvider::processAuthorisedOrderLock( $revolut_order_id );
		$process_captured_order_lock = ServiceProvider::processCapturedOrderLock( $revolut_order_id );

		if ( $process_captured_order_lock->isLocked() || ! $process_authorised_order->acquire() ) {
			return false;
		}

		try {
			$wc_order = wc_get_order( $wc_order_id );
			$options  = ServiceProvider::optionRepository();

			$is_revolut_order_processed  = (int) $options->get( 'is_revolut_order_processed_' . $revolut_order_id );
			$is_revolut_order_authorised = (int) $options->get( 'is_revolut_order_authorised_' . $revolut_order_id );

			$mode = ServiceProvider::apiConfigProvider()->getConfigValue( 'payment_action' );

			if ( $is_revolut_order_authorised || $is_revolut_order_processed ) {
				return;
			}

			if ( ! $this->is_authorised_payment( $revolut_order_id ) ) {
				return false;
			}

			$is_pay_by_bank_method = $wc_order->get_payment_method() === WC_Gateway_Revolut_Pay_By_Bank::GATEWAY_ID;

			$wc_order->add_order_note( 'Payment has been successfully authorized (Order ID: ' . $revolut_order_id . ').' );
			$options->add( 'is_revolut_order_authorised_' . $revolut_order_id, 1 );

			if ( $is_pay_by_bank_method ) {
				$should_process_on_authorise = WC_Gateway_Revolut_Pay_By_Bank::should_process_on_authorise();
				$wc_order->add_order_note(
					'Pay by Bank payments can take up to 1 business day to complete. 
						If the order is not moved to the "Payment accepted" state after 1 business day, 
						merchants should check their Revolut account to verify that this payment was taken, and may need to reach out the customer if it was not.'
				);

				if ( $should_process_on_authorise ) {
					$this->maybe_complete_wc_order( $wc_order );
					$wc_order->add_order_note( 'This Payment is not captured yet, the order has been moved to processing state based on your Pay by bank settings' );
					return true;
				}
			}

			$wc_order->update_status( 'on-hold' );

			if ( 'authorize_and_capture' === $mode && ! $is_subscription_payment && ! $is_webhook ) {
				$wc_order->add_order_note(
					'Payment is taking a bit longer than expected to be completed. 
						If the order is not moved to the “Processing” state after 24h, please check your Revolut account to verify that this payment was taken. 
						You might need to contact your customer if it wasn’t.'
				);
			}
		} finally {
			$process_authorised_order->release();
		}

		return true;
	}

	/**
	 * Mark WooCommerce Order as failed
	 *
	 * @param string $revolut_order_id        Revolut Payment id.
	 * @param id     $wc_order_id             WooCommerce order id.
	 */
	protected function mark_wc_order_failed( $revolut_order_id, $wc_order_id ) {
		if ( $this->is_authorised_or_completed_payment( $revolut_order_id ) ) {
			return false;
		}

		$options = ServiceProvider::optionRepository();

		$is_revolut_order_processed  = (int) $options->get( 'is_revolut_order_processed_' . $revolut_order_id );
		$is_revolut_order_authorised = (int) $options->get( 'is_revolut_order_authorised_' . $revolut_order_id );

		$process_authorised_order    = ServiceProvider::processAuthorisedOrderLock( $revolut_order_id );
		$process_captured_order_lock = ServiceProvider::processCapturedOrderLock( $revolut_order_id );

		if ( $process_captured_order_lock->isLocked() || $process_authorised_order->isLocked() ) {
			return false;
		}

		if ( $is_revolut_order_authorised || $is_revolut_order_processed ) {
			return false;
		}

		$wc_order            = wc_get_order( $wc_order_id );
		$is_wc_order_on_hold = $wc_order && ( $wc_order->has_status( 'on-hold' ) || $wc_order->has_status( 'pending' ) );

		if ( ! $is_wc_order_on_hold ) {
			return false;
		}

		$wc_order->update_status( 'failed' );

		return true;
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
	 * Check if payment is completed.
	 *
	 * @param string $revolut_order_id Revolut order id.
	 */
	protected function is_completed_payment( $revolut_order_id ) {
		$revolut_order = MerchantApi::privateLegacy()->get( '/orders/' . $revolut_order_id );
		return ( isset( $revolut_order['state'] ) && 'COMPLETED' === $revolut_order['state'] );
	}

	/**
	 * Check if payment is authorised.
	 *
	 * @param string $revolut_order_id Revolut order id.
	 */
	protected function is_authorised_payment( $revolut_order_id ) {
		$revolut_order = MerchantApi::privateLegacy()->get( '/orders/' . $revolut_order_id );
		return ( isset( $revolut_order['state'] ) && ( 'AUTHORISED' === $revolut_order['state'] ) );
	}

	/**
	 * Check if payment is authorised or completed.
	 *
	 * @param string $revolut_order_id Revolut order id.
	 */
	protected function is_authorised_or_completed_payment( $revolut_order_id ) {
		$revolut_order = MerchantApi::privateLegacy()->get( '/orders/' . $revolut_order_id );
		return ( isset( $revolut_order['state'] ) && ( 'AUTHORISED' === $revolut_order['state'] || 'COMPLETED' === $revolut_order['state'] ) );
	}

	/**
	 * Capture action.
	 *
	 * @param string $revolut_order_id Revolut order id.
	 */
	protected function capture_payment( $revolut_order_id ) {
		try {
			$lock = ServiceProvider::capturePaymentLock( $revolut_order_id );

			$payment_action = ServiceProvider::apiConfigProvider()->getConfigValue( 'payment_action' );

			// authorize only mode do nothing.
			if ( 'authorize' === $payment_action ) {
				return true;
			}

			$revolut_order = MerchantApi::private()->get( "/orders/$revolut_order_id" );

			if ( 'manual' !== $revolut_order['capture_mode'] ) {
				return true;
			}

			if ( 'capture_started' === $revolut_order['state'] ) {
				return true;
			}

			if ( 'completed' === $revolut_order['state'] ) {
				return true;
			}

			if ( 'authorised' !== $revolut_order['state'] ) {
				return false;
			}

			if ( ! $lock->acquire() ) {
				return false;
			}

			$result = MerchantApi::private()->post( "/orders/$revolut_order_id/capture", array() );

			return ! empty( $result ) && isset( $result['id'] );
		} catch ( Exception $e ) {
			$this->log_error( 'payment capture action error : ' . $e->getMessage() );
			return false;
		} finally {
			$lock->release();
		}
	}

	/**
	 * Grab selected payment token from Request
	 *
	 * @param int $wc_token_id WooCommerce payment token id.
	 * @return string
	 * @throws Exception Exception.
	 */
	public function get_selected_payment_token( $wc_token_id ) {
		$wc_token          = WC_Payment_Tokens::get( $wc_token_id );
		$payment_method_id = $wc_token->get_token();

		if ( empty( $payment_method_id ) || $wc_token->get_user_id() !== get_current_user_id() ) {
			throw new Exception( 'Can not process payment token' );
		}

		return $wc_token;
	}

	/**
	 * Charge customer with previously saved payment method.
	 *
	 * @param string              $revolut_order_id Revolut order id.
	 * @param WC_Payment_Token_CC $wc_token WooCommerce payment token.
	 */
	protected function pay_by_saved_method( $revolut_order_id, $wc_token ) {
		$payment_method_id = $wc_token->get_token();

		$body = array(
			'payment_method_id' => $payment_method_id,
		);

		$this->action_revolut_order( $revolut_order_id, 'confirm', $body );
		return $wc_token;
	}

	/**
	 * Send order action request from Woocommerce to API.
	 *
	 * @param String            $revolut_order_id Revolut order id.
	 * @param String            $action Api action.
	 * @param array|object|null $body Request body.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function action_revolut_order( $revolut_order_id, $action, $body = array() ) {
		if ( empty( $revolut_order_id ) ) {
			return array();
		}

		$json = MerchantApi::privateLegacy()->post( "/orders/$revolut_order_id/$action", $body );

		if ( ! empty( $json ) && ! isset( $json['id'] ) && isset( $json['code'] ) ) {
			if ( ! empty( $json['code'] ) && FAILED_CARD === $json['code'] ) {
				/* translators: %s: Order Action. */
				throw new Exception( sprintf( __( 'Customer will not be able to get a %s using this card!', 'revolut-gateway-for-woocommerce' ), $action ) );
			}

			/* translators:%1s: Order Action. %$2s: Order Action.*/
			throw new Exception( sprintf( __( 'Cannot %1$s Order - Error Id: %2$s.', 'revolut-gateway-for-woocommerce' ), $action, $json['code'] ) );
		}

		return $json;
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
	 * Fetch order ids by public id
	 *
	 * @param String $public_id Revolut public id.
	 *
	 * @return string|null
	 */
	public function get_order_ids_by_public_id( $public_id ) {
		global $wpdb;
		// resolve into order_id.
		$result =
			$wpdb->get_row( // phpcs:ignore
				$wpdb->prepare(
					'SELECT HEX(order_id) as order_id, wc_order_id FROM ' . $wpdb->prefix . 'wc_revolut_orders
                WHERE public_id=UNHEX(REPLACE(%s, "-", ""))',
					array( $public_id )
				),
				ARRAY_A
			);


		if ( ! empty( $result['order_id'] ) ) {
			$result['order_id'] = $this->uuid_dashes( $result['order_id'] );
		}

		return $result;
	}

	/**
	 * Fetch Revolut order by public id
	 *
	 * @param int $wc_order_id WooCommerce order id.
	 *
	 * @return string|null
	 */
	public function get_revolut_order_by_wc_order_id( $wc_order_id ) {
		global $wpdb;

		return $this->uuid_dashes(
			$wpdb->get_col( // phpcs:ignore
				$wpdb->prepare(
					'SELECT HEX(order_id) FROM ' . $wpdb->prefix . 'wc_revolut_orders WHERE wc_order_id=%d',
					array( $wc_order_id )
				)
			)
		);
	}

	/**
	 * Get Woocommerce Order ID
	 *
	 * @param String $order_id Revolut order id.
	 *
	 * @return array|object|void|null
	 */
	public function get_wc_order_id_by_revolut_order_id( $order_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( 'SELECT wc_order_id FROM ' . $wpdb->prefix . "wc_revolut_orders WHERE order_id=UNHEX(REPLACE(%s, '-', ''))", array( $order_id ) ) ); // db call ok; no-cache ok.
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
			return ServiceProvider::merchantDetailsService()->getAvailablePaymentMethods( $amount, $currency );
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
