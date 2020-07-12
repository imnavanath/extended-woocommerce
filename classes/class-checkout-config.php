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

		/**
		 * Cretaing dynamic shortcode to render checkout form as per assigned product ID.
		 *
		 * Ex. [woo_extended_checkout product_id='137']
		 *
		 * Update product ID here.
		 */
		add_shortcode( 'woo_extended_checkout', array( $this, 'woo_extended_checkout_shortcode_markup' ) );

		// Hide / Show "Ship to Different Address?" fields.
		add_filter( 'woocommerce_cart_needs_shipping_address', array( $this, 'change_shipping_detail_requirement' ) );

		// Chnage "'Ship to a different address'" text from Checkout page.
		add_filter( 'gettext', array( $this, 'shipping_address_strings_translation' ), 20, 3 );

        // Preconfigured cart data.
		add_action( 'wp', array( $this, 'preconfigured_extended_cart_data' ), 1 );

		// Load custom stylings for checkout page.
		add_action( 'wp_enqueue_scripts', array( $this, 'load_checkout_page_custom_stylings' ) );

		// Remove checkout page unwanted actions.
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
		remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );

		// Change "Place order" button text to "Validate & Pay".
		add_filter( 'woocommerce_order_button_text', array( $this, 'update_checkout_place_order_text' ) );
	}

	/**
	* Generating new custom checkout page with custom depending fields.
	*
	* @since 1.0.0
	* @return bool
	*/
	public function woo_extended_checkout_shortcode_markup( $atts ) {

		$atts = shortcode_atts(
			array(
				'product_id' => '0',
			),
			$atts
		);

		global $post;

		$product_id = (int) $atts['product_id'];

		$options_data = get_option( 'woo_extended_checkout_linking', array() );

		if( $product_id ) {
			$options_data[ $post->ID ] = $product_id;
		}

		update_option( 'woo_extended_checkout_linking', $options_data );

		$output = '';

		ob_start();

		$output .= '<input type="hidden" class="input-hidden woo_extended_product" name="woo_extended_product" value="' . $product_id . '">';

		$output .= ob_get_clean();

		return $output;
	}

	/**
	* Chnage 'Ship to a different address' text to new text.
	* Updated page ID where shipping fields need to load.
	*
	* @since 1.0.0
	* @return bool true | false.
	*/
	public function change_shipping_detail_requirement() {

		if( is_page( 18 ) ) {
			return true;
		}

		return false;
	}

	/**
	* Chnage 'Ship to a different address' text to new text.
	*
	* @since 1.0.0
	* @param string $translated_text Translated Text.
	* @param string $text Text.
	* @param string $domain Domain name.
	*
	* @return string
	*/
	public function shipping_address_strings_translation( $translated_text, $text, $domain ) {

		switch ( $translated_text ) {
			case 'Ship to a different address?' :
				$translated_text =  __( 'Office Address / आधिकारिक पता Same As Plant Address' );
				break;
		}

		return $translated_text;
	}

	/**
	* Load custom styles for checkout page.
	*
	* @since 1.0.0
	* @return bool
	*/
    public function load_checkout_page_custom_stylings() {

		// Global checkout form stylings.
		wp_enqueue_style( 'woo-frontend-checkout-styles', EXTENDED_WOOCOMMERCE_URI . 'assets/css/checkout-styles.css', array(), EXTENDED_WOOCOMMERCE_VER );

		// Update IEC form conditional form.
		if( is_page( 21 ) ) {
			wp_enqueue_script( 'woo-frontend-checkout-script', EXTENDED_WOOCOMMERCE_URI . 'assets/js/checkout-page.js', array( 'jquery' ), EXTENDED_WOOCOMMERCE_VER, false );
		}
	}

	/**
	* Updated order button CTA text.
	*
	* @since 1.0.0
	* @return bool
	*/
    public function update_checkout_place_order_text( $cta_text ) {
		$cta_text = __( 'Validate & Pay' );
		return $cta_text;
	}

    /**
	* Check if it is checkout shortcode.
	*
	* @since 1.0.0
	* @return bool
	*/
    public function is_woo_extended_checkout_shortcode_on_page() {
   
        global $post;
    
        if ( ! empty( $post ) && has_shortcode( $post->post_content, 'woo_extended_checkout' ) ) {
    
            return true;
        }
    
        return false;
    }

    /**
	 * Get unique id.
	 *
	 * @param int $length Length.
	 *
	 * @return string
	 */
    public function get_unique_id( $length = 8 ) {

		return substr( md5( microtime() ), 0, $length );
	}

	/**
	 * Get product ID linked to current checkout page.
	 *
	 * @param int $checkout_id current checkout page.
	 *
	 * @return string
	 */
    public function get_linked_woo_product_for_checkout( $checkout_id = 0 ) {

		$options_data = get_option( 'woo_extended_checkout_linking', array() );

		if( ! empty( $options_data ) && isset( $options_data[$checkout_id] ) ) {
			return $options_data[$checkout_id];
		}

		return $checkout_id;
	}

    /**
	 * Get selected checkout products and data. 
	 *
	 * @param int   $checkout_id    Checkout id.
	 *
	 * @return array
	 */
	public function get_selected_checkout_products( $checkout_id = '' ) {

		global $post;

		if ( empty( $checkout_id ) ) {
			$checkout_id = $post->ID;
		}

		if ( ! isset( $this->checkout_products[ $checkout_id ] ) ) {

            $products = array(
                'product'        => $this->get_linked_woo_product_for_checkout( $checkout_id ),
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

		if ( $this->is_woo_extended_checkout_shortcode_on_page() ) {

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
