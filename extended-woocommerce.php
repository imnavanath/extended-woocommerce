<?php
/**
 * Plugin Name: Extended WooCommerce Structure
 * Description: Let's extend Woo structure and core functionalities.
 * Version: 1.0.0
 * Text Domain: extended-woocommerce
 *
 * @package Extended WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'EXTENDED_WOOCOMMERCE_VER', '1.0.0' );
define( 'EXTENDED_WOOCOMMERCE_FILE', __FILE__ );
define( 'EXTENDED_WOOCOMMERCE_BASE', plugin_basename( EXTENDED_WOOCOMMERCE_FILE ) );
define( 'EXTENDED_WOOCOMMERCE_DIR', plugin_dir_path( EXTENDED_WOOCOMMERCE_FILE ) );
define( 'EXTENDED_WOOCOMMERCE_URI', plugins_url( '/', EXTENDED_WOOCOMMERCE_FILE ) );

if ( ! function_exists( 'extended_woocommerce_setup' ) ) :

	/**
	 * Extended WooCommerce Setup
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function extended_woocommerce_setup() {

		require_once EXTENDED_WOOCOMMERCE_DIR . 'classes/class-extend-admin-order-view.php';
	}

	add_action( 'plugins_loaded', 'extended_woocommerce_setup' );

endif;