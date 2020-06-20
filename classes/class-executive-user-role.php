<?php
/**
 * Extend Admin Woo Order Settings.
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
 * Add new Executive user role.
 *
 * @since 1.0.0
 */
class Executive_User_Role {

    /**
     *  Constructor
     */
    public function __construct() {

		// Get Excutive ID to whom it should assign next.
		add_action( 'wp_head', array( $this, 'get_next_assignee_executive' ) );

		// Update custom executive user ID to new shop_order.
		add_filter( 'woocommerce_new_order_data', array( $this, 'assign_shop_order_to_executive' ));

		// Update WP's default Author metabox for 'order_executive' custom user role.
		add_action( 'wp_dropdown_users_args', array( $this, 'update_author_for_post_order' ) );

		// Create 'order_executive' custom user role.
		add_action( 'init', array( $this, 'create_order_executive_user_role' ), 7 );

		// Remove 'custom-fields' metabox & add 'author' metabox suppot to 'shop_order' CPT.
		add_action( 'init', array( $this, 'manage_order_cpt_supports' ) );

		// Update 'shop_order' CPT's arguments for order_executive user role.
		add_filter( 'woocommerce_register_post_type_shop_order', array( $this, 'update_shop_order_cpt_args' ) );

		// Update 'authordiv' meta-box to Assignee for shop_order.
		add_action( 'add_meta_boxes', array( $this, 'change_author_metabox' ) );

		// Remove 'woocommerce-order-downloads' meta-box from shop_order.
		add_action( 'add_meta_boxes', array( $this, 'remove_order_downloads_metabox' ) );

		add_action('admin_head', array( $this, 'disable_new_posts_link' ) );
	}

	/**
	 * Assign new orders to Executive user role directly.
	 */
	public function assign_shop_order_to_executive( $order_meta_data ) {

		$last_assignee_user = $this->get_next_assignee_executive();

		$order_meta_data[ 'post_author' ] = $last_assignee_user;
		update_option( 'woo_last_assignee_executive', $last_assignee_user );

		return $order_meta_data;
	}

	/**
	 * Assign next Assignee user role that order should be assign.
	 */
	public function get_next_assignee_executive() {

		$all_executive_ids = array();
		$next_assignee_executive = 1;
		$last_assignee_position = 0;
		$all_executive_users = get_users( [ 'role__in' => [ 'order_executive' ] ] );

		$last_assignee_user = (int) get_option( 'woo_last_assignee_executive', 1 );

		if( $all_executive_users ) {
			foreach ( $all_executive_users as $executive ) {
				$all_executive_ids[] = $executive->ID;
				if( 1 === $last_assignee_user ) {
					$last_assignee_user = update_option( 'woo_last_assignee_executive', $executive->ID );
				}
			}
			$last_assignee_position = array_key_last( $all_executive_ids );
		}

		if( $all_executive_ids && in_array( $last_assignee_user, $all_executive_ids ) ) {

			$current_assignee_position = array_search( $last_assignee_user, $all_executive_ids );

			if( $current_assignee_position === $last_assignee_position ) {
				$assignee_at_position = 0;
			} else {
				$assignee_at_position = $current_assignee_position + 1;
			}

			$next_assignee_executive = $all_executive_ids[ $assignee_at_position ];
		}

		return $next_assignee_executive;
	}

	/**
	 * Remove 'Add New' Submenu for 'shop_order'.
	 */
	public function disable_new_posts_link() {

		$current_user_id = get_current_user_id();
		$user = get_userdata( $current_user_id );
		$user_roles = $user->roles;

		if ( in_array( 'order_executive', $user_roles, true ) ) {
			echo '<style type="text/css"> #adminmenu #menu-dashboard, #wp-admin-bar-new-content, #wpbody-content .wrap .subsubsub .all, .wp-menu-open.menu-icon-shop_order .wp-submenu, .menu-top.toplevel_page_woocommerce, .wp-menu-separator.woocommerce, .wp-menu-open.menu-icon-shop_order .wp-submenu, .post-type-shop_order .page-title-action {display:none;} .menu-icon-shop_order .wp-submenu {top: -1000em !important;} </style>';
		}
	}

	/**
	 * Remove 'woocommerce-order-downloads' unwanted metabox for 'shop_order'.
	 */
	public function remove_order_downloads_metabox() {
		remove_meta_box( 'woocommerce-order-downloads', 'shop_order', 'normal' );
	}

    /**
	 * Rename Author metabox label to Assignee.
	 */
    public function change_author_metabox() {

        // Put author metabox to the side.
		if ( post_type_supports( 'shop_order', 'author' ) ) {
			remove_meta_box( 'authordiv', 'shop_order', 'normal' );
			add_meta_box( 'authordiv', 'Assignee', 'post_author_meta_box', 'shop_order', 'side', 'high' );
		}
    }

    /**
	 * Add custom user roles to shop_order cpt.
	 */
    public function update_author_for_post_order( $args ) {
        if ( isset( $args['who'] ) ) {
            $args['role__in'] = ['order_executive'];
            $args['include_selected'] = true;
            unset( $args['who'] );
        }
        return $args;
    }

    /**
	 * Update shop_order cpt supports fields.
	 */
    public function manage_order_cpt_supports() {
        remove_post_type_support( 'shop_order', 'custom-fields' );
        add_post_type_support( 'shop_order', 'author' );
    }

    /**
	 * Update shop_order cpt args fields.
	 */
    public function update_shop_order_cpt_args( $args ) {

		if( is_user_logged_in() ) {

			$user = wp_get_current_user();
			$roles = ( array ) $user->roles;
			$user_role = $roles[0];

			if( isset( $user_role ) && 'order_executive' === $user_role ) {
				$args['show_in_menu']  = true;
				$args['menu_icon'] = 'dashicons-welcome-write-blog';
				return $args;
			}
		}

		return $args;
    }

    /**
	 * Create new Executive user role for shop.
	 */
	public function create_order_executive_user_role() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wc_installing' ) ) {
			return;
        }

        global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Shop Executive role.
		add_role(
			'order_executive',
			'Shop Executive',
			array(
				'read'                          => true,
                'assign_shop_order_terms'       => true,
				'edit_private_shop_orders'      => true,
				'edit_published_shop_orders'    => true,
				'edit_shop_order'               => true,
				'edit_shop_order_terms'         => true,
				'edit_shop_orders'              => true,
                'manage_shop_order_terms'       => true,
                'manage_woocommerce'            => true,
				'publish_shop_orders'           => true,
				'read_private_shop_orders'      => true,
				'read_shop_order'               => true,
			)
        );
    }
}

new Executive_User_Role();
