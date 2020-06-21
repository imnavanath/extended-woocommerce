<?php
/**
 * Extend Admin Woo Checkout Settings.
 *
 * @package Extended WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// If plugin - 'WooCommerce' not exist then return.
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Updated checkout page & their configs.
 *
 * @since 1.0.0
 */
class Extended_Checkout_Config {

    /**
	 * Member Variable
	 *
	 * @var checkout_products
	 */
	public $checkout_products = array();

    /**
     *  Constructor
     */
    public function __construct() {

        // Preconfigured cart data.
		add_action( 'wp', array( $this, 'preconfigured_extended_cart_data' ), 1 );
    }

    /**
	* Check if it is checkout shortcode.
	*
	* @since 1.0.0
	* @return bool
	*/
    public function is_woo_checkout_shortcode_on_page() {
   
        global $post;
    
        if ( ! empty( $post ) && has_shortcode( $post->post_content, 'woocommerce_checkout' ) ) {
    
            return true;
        }
    
        return false;
    }

    /**
	 * Get unique id.
	 *
	 * @param int $length    Length.
	 *
	 * @return string
	 */
    public function get_unique_id( $length = 8 ) {

		return substr( md5( microtime() ), 0, $length );
	}

    /**
	 * Get selected checkout products and data
	 *
	 * @param int   $checkout_id    Checkout id.
	 *
	 * @return array
	 */
	public function get_selected_checkout_products( $checkout_id = '' ) {

		if ( empty( $checkout_id ) ) {

			global $post;

			$checkout_id = $post->ID;
		}

		if ( ! isset( $this->checkout_products[ $checkout_id ] ) ) {

            $products = array(
                'product'        => '146',
                'quantity'       => '1',
                'discount_type'  => '',
                'discount_value' => '',
                'unique_id'      => $this->get_unique_id(),
                'add_to_cart'    => true,
            );

			$this->checkout_products[ $checkout_id ] = $products;
		}

		return $this->checkout_products[ $checkout_id ];
    }
    
    /**
	 * Set selected checkout products and data
	 *
	 * @param int   $checkout_id    Checkout id..
	 * @param array $products_data  Saved product.
	 *
	 * @return array
	 */
	public function set_selcted_checkout_products( $checkout_id = '', $products_data = array() ) {

		if ( empty( $checkout_id ) ) {

			global $post;

			$checkout_id = $post->ID;
		}

		if ( isset( $this->checkout_products[ $checkout_id ] ) ) {
			$products = $this->checkout_products[ $checkout_id ];
		} else {
			$products = $this->get_selected_checkout_products( $checkout_id );
		}

		if ( is_array( $products ) && ! empty( $products_data ) ) {

			foreach ( $products as $in => $data ) {

				if ( isset( $products_data[ $in ] ) ) {
					$products[ $in ] = wp_parse_args( $products_data[ $in ], $products[ $in ] );
				}
			}
		}

		$this->checkout_products[ $checkout_id ] = $products;

		return $this->checkout_products[ $checkout_id ];
	}

    /**
	 * Configure Cart Data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function preconfigured_extended_cart_data() {

		if ( is_admin() ) {
			return;
		}

		global $post;

		if ( $this->is_woo_checkout_shortcode_on_page() ) {

			if ( wp_doing_ajax() ) {
				return;
			} else {

                $checkout_id = $post->ID;
                
				do_action( 'extended_woo_checkout_before_configure_cart', $checkout_id );

				$products = $this->get_selected_checkout_products( $checkout_id );

				if ( ! is_array( $products ) ) {
					return;
				}

				/* Empty the current cart */
                WC()->cart->empty_cart();

				/* Set customer session if not set */
				if ( ! is_user_logged_in() && WC()->cart->is_empty() ) {
					WC()->session->set_customer_session_cookie( true );
				}

				$cart_product_count = 0;
				$cart_key           = '';
				$products_new       = array();

                $data = apply_filters( 'extended_woo_selected_checkout_products', $products, $checkout_id );
				
				if( ! empty( $data ) ) {

                    $quantity = $data['quantity'];
                    $product_id = $data['product'];
					$_product   = wc_get_product( $product_id );
                    
					if ( ! empty( $_product ) ) {
						if ( $_product->is_type( 'simple' ) ) {
                            $cart_key = WC()->cart->add_to_cart( $product_id, $quantity, 0, array() );
                            $cart_product_count++;
						} else {
							$wrong_product_notice = __( 'This product can\'t be purchased' );
							wc_add_notice( $wrong_product_notice );
						}
                    }

					$products_new[] = array(
						'cart_item_key' => $cart_key,
					);
                }

				/* Set checkout products data */
				$this->set_selcted_checkout_products( $checkout_id, $products_new );

				do_action( 'extended_woo_checkout_after_configure_cart', $checkout_id );
			}
		}
	}
}

new Extended_Checkout_Config();
