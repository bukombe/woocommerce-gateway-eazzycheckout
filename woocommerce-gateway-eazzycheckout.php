<?php
/**
 * Plugin Name:     WooCommerce Gateway - EazzyCheckout
 * Plugin URI:      https://kanzucode.com
 * Description:     Handle payments to EazzyCheckout
 * Version:         1.0.0
 * Author:          Kanzu Code
 * Author URI:      https://kanzucode.com
 * Text Domain:     kanzu-eazzycheckout
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:     /languages
 */

add_action( 'plugins_loaded', 'init_eazzycheckout_payment_gateway_class' );

function init_eazzycheckout_payment_gateway_class() {

	if ( ! class_exists( 'WooCommerce_Gateway_EazzyCheckout' ) ) {

		/**
		 * EazzyCheckout Payment Gateway.
		 *
		 * Provides a EazzyCheckout Payment Gateway.
		 *
		 * @class       WooCommerce_Gateway_EazzyCheckout
		 * @extends     WC_Payment_Gateway
		 * @version     2.1.0
		 * @package     WooCommerce-Gateway-EazzyCHeckout
		 * @author      Kanzu Code
		 */
		class WooCommerce_Gateway_EazzyCheckout extends WC_Payment_Gateway {

			/**
			 * Constructor for the gateway.
			 */
			public function __construct() {
				$this->id                 = 'eazzycheckout';
				$this->icon               = apply_filters( 'woocommerce_cheque_icon', '' );
				$this->has_fields         = false;
				$this->method_title       = _x( 'EazzyCheckout payments', 'EazzyCheckout payment method', 'kanzu-eazzycheckout' );
				$this->method_description = __( 'Allows EazzyCheckout payments.', 'kanzu-eazzycheckout' );

				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();

				// Define user set variables
				$this->title           = $this->get_option( 'title' );
				$this->description     = $this->get_option( 'description' );
				$this->consumer_key    = $this->get_option( 'consumer_key' );
				$this->consumer_secret = $this->get_option( 'consumer_secret' );
				$this->merchant_code   = $this->get_option( 'merchant_code' );
				$this->merchant_key    = $this->get_option( 'merchant_key' );
				$this->outlet_code     = $this->get_option( 'outlet_code' );

				if ( 'yes' == $this->get_option( 'test_enabled' ) ) {
					$this->token_api  = 'https://api-test.equitybankgroup.com/v1/token';
					$this->script_url = 'https://api-test.equitybankgroup.com/js/eazzycheckout.js';
				} else {
					$this->token_api  = '';
					$this->script_url = '';
				}

				// Actions
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_thankyou_eazzycheckout', array( $this, 'thankyou_page' ) );

				// Callback endpoint
				add_action( 'woocommerce_api_wc_' . $this->id . '_gateway', array( $this, 'handle_callback' ) );

				// Add to this gateway to WooCommerce
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

				// Render Eazzy Checkout payment form
				add_action( 'wp', array( $this, 'render_payment_form' ) );
			}

			public function add_gateway( $methods ) {
				$methods[] = $this;
				return $methods;		
			}

			/**
			 * Handle EazzyCheckout callback
			 */
			public function handle_callback() {
				if ( ! isset( $_REQUEST['orderRef'] ) || ! isset( $_REQUEST['status'] ) ) {
					return;
				}

				if ( ( $order_id = $_REQUEST['orderRef'] ) && 'paid' == $_REQUEST['status'] ) {
					$order = wc_get_order( $order_id );
					if ( $order ) {
						$order->add_order_note( __( 'EazzyCheckout payment completed.', 'kanzu-eazzycheckout' ) );
						$order->payment_complete();

						wp_redirect( $this->get_return_url( $order ) );
						exit;
					}
				}

				wp_redirect( home_url( '/shop' ) );
				exit;
			}

			/**
			 * Initialise Gateway Settings Form Fields.
			 */
			public function init_form_fields() {

				$this->form_fields = array(
					'enabled'         => array(
						'title'   => __( 'Enable/Disable', 'kanzu-eazzycheckout' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable EazzyCheckout payments', 'kanzu-eazzycheckout' ),
						'default' => 'yes',
					),
					'title'           => array(
						'title'       => __( 'Title', 'kanzu-eazzycheckout' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'kanzu-eazzycheckout' ),
						'default'     => _x( 'EazzyCheckout payments', 'EazzyCheckout payment method', 'kanzu-eazzycheckout' ),
						'desc_tip'    => true,
					),
					'description'     => array(
						'title'       => __( 'Description', 'kanzu-eazzycheckout' ),
						'type'        => 'textarea',
						'description' => __( 'Payment method description that the customer will see on your checkout.', 'kanzu-eazzycheckout' ),
						'default'     => __( '', 'kanzu-eazzycheckout' ),
						'desc_tip'    => true,
					),
					'test_enabled'    => array(
						'title'   => __( 'Enable/Disable Test Mode', 'kanzu-eazzycheckout' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable Test Mode', 'kanzu-eazzycheckout' ),
						'default' => 'no',
					),
					'merchant_code'   => array(
						'title'       => __( 'Merchant Code', 'kanzu-eazzycheckout' ),
						'type'        => 'text',
						'description' => __( 'Merchant Code required for authentication.' ),
						'default'     => '',
						'desc_tip'    => true,
					),
					'merchant_key'    => array(
						'title'       => __( 'Merchant Key', 'kanzu-eazzycheckout' ),
						'type'        => 'text',
						'description' => __( 'Merchant Key required for authentication.' ),
						'default'     => '',
						'desc_tip'    => true,
					),
					'outlet_code'     => array(
						'title'       => __( 'Outlet Code', 'kanzu-eazzycheckout' ),
						'type'        => 'text',
						'description' => __( 'It identifies the merchant outlet.' ),
						'default'     => '',
						'desc_tip'    => true,
					),
					'consumer_key'    => array(
						'title'       => __( 'Consumer Key', 'kanzu-eazzycheckout' ),
						'type'        => 'text',
						'description' => __( 'Consumer Key required for authentication.' ),
						'default'     => '',
						'desc_tip'    => true,
					),
					'consumer_secret' => array(
						'title'       => __( 'Consumer Secret', 'kanzu-mpesa' ),
						'type'        => 'text',
						'description' => __( 'Consumer Secret required for authentication.' ),
						'default'     => '',
						'desc_tip'    => true,
					),
				);
			}

			/**
			 * Output for the order received page.
			 */
			public function thankyou_page() {
			}

			/**
			 * Render EazzyCheckout payment form
			 */
			public function render_payment_form() {
				if ( ! is_page( 'eazzy-checkout' ) || ! isset( $_GET['order-id'] ) || empty( $_GET['order-id'] ) ) return;

				$order_id = $_GET['order-id'];
				$order    = wc_get_order( $order_id );

				if ( ! $order || ! $order->has_status( 'on-hold' ) ) return;

				// Enqueue our scripts and styles
				wp_enqueue_style( 'kanzu-eazzycheckout-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/css/woocommerce-gateway-eazzycheckout.css' );

				wp_register_script( 'eazzycheckout-js', $this->script_url );
				wp_enqueue_script( 'kanzu-eazzycheckout-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/js/woocommerce-gateway-eazzycheckout.js', array( 'jquery', 'eazzycheckout-js' ) );
				wp_localize_script(
					'kanzu-eazzycheckout-js', 'KanzuEazzyCheckout', array(
						'token'        => $this->get_access_token(),
						'amount'       => (int) $order->get_total(),
						'merchantCode' => $this->merchant_code,
						'outletCode'   => $this->outlet_code,
						'callbackUrl'  => str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_EazzyCheckout_Gateway', home_url( '/' ) ) ),
						'orderRef'     => $order_id,
						'custName'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
						'description'  => __( 'WooCommerce Order', 'kanzu-eazzycheckout' ),
						'website'      => home_url( '/' ),
						'siteLogo'     => get_theme_mod( 'site_logo' ),

					)
				);
			}

			/**
			 * Process the payment and return the result.
			 *
			 * @param int $order_id
			 * @return array
			 */
			public function process_payment( $order_id ) {

				$order = wc_get_order( $order_id );

				// Mark as on-hold
				$order->update_status( 'on-hold', _x( 'Awaiting payment', 'EazzyCheckout payment method', 'kanzu-eazzycheckout' ) );

				// Reduce stock levels
				wc_reduce_stock_levels( $order_id );

				// Remove cart
				WC()->cart->empty_cart();

				//Redirect to Eazzy Checkout page
				$redirect = get_permalink( $this->get_checkout_page_id() );
				return array(
					'result'   => 'success',
					'redirect' => add_query_arg( 'order-id', $order_id, $redirect ),
				);
			}

			/**
			 * Retrieve OAuth access token
			 */
			public function get_access_token() {
				$data = array(
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( $this->consumer_key . ':' . $this->consumer_secret ),
						'Content-Type'  => 'application/x-www-form-urlencoded',
					),
					'body'    => array(
						'merchantCode' => $this->merchant_code,
						'password'     => $this->merchant_key,
					),
					'timeout' => 60,
				);

				$response = wp_remote_post( $this->token_api, $data );

				if ( ! is_wp_error( $response ) ) {
					$response = json_decode( $response['body'], true );
					if ( isset( $response['status'] ) && 'success' == $response['status'] ) {
						return $response['payment-token'];
					}
				}
				return false;
			}

			public function get_checkout_page_id() {
				if ( $page = get_page_by_path( 'eazzy-checkout' ) ) {
					return $page->ID;
				} else {
					$page_id = wp_insert_post(
						array(
							'post_type'    => 'page',
							'post_status'  => 'publish',
							'post_content' => __( 'Loading Eazzy Checkout...' ),
							'post_slug'    => 'eazzy-checkout',
							'post_title'   => 'Eazzy Checkout',
						)
					);

					return $page_id;
				}
			}

		}
	}
	new WooCommerce_Gateway_EazzyCheckout();
}
