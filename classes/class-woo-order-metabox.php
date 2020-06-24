<?php
/**
 * Post Meta Box
 *
 * @package     Extended WooCommerce
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
if ( ! class_exists( 'Woo_Order_Metabox' ) ) {

	/**
	 * Meta Boxes setup
	 */
	class Woo_Order_Metabox {

		/**
		 * Meta Option
		 *
		 * @var $meta_option
		 */
		private static $meta_option;

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

		/**
		 *  Init Metabox
		 */
		public function init_metabox() {

			add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
            add_action( 'save_post', array( $this, 'save_meta_box' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
        }
        
        /**
		 *  Add admin script to check filetype & enable button to perform the action.
		 */
		public function add_admin_scripts( $hook ) {

            global $post;

            if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'shop_order' === $post->post_type ) {

                wp_enqueue_media();

                // Registers and enqueues the required javascript.
                wp_enqueue_script( 'certificate_uploader_script', EXTENDED_WOOCOMMERCE_URI . 'assets/js/certificate-media.js', array( 'jquery' ), EXTENDED_WOOCOMMERCE_VER );
            }
        }

		/**
		 *  Setup Metabox
		 */
		public function setup_meta_box() {
            add_meta_box( 
                'customer_certificate_wrt_order',
                __( 'Order Certificate' ),
                array( $this, 'customer_certificate_markup' ), 
                'shop_order',
                'side',
                'high'
            );
        }
        
        /**
         * Custom attachment metabox markup.
         */
        public function customer_certificate_markup( $post ) {

            // Security field nonce.
    		wp_nonce_field( 'save_customer_certificate_wrt_order', 'customer_certificate_wrt_order' );

            // Saved PDF certificate.
            $saved_file = get_post_meta( $post->ID, 'certificate_file_name', true );
            $certificate_file_url = get_post_meta( $post->ID, 'certificate_file_url', true );

            $output_notice = isset( $saved_file ) && '' !== $saved_file ? $saved_file : __( 'Please upload valid PDF file here.' );

            ?>
                <fieldset>
                    <div>
                        <p class="description"> <?php _e( 'Upload customer\'s PDF certificate here.' ); ?> </p>
                        <p class="description"> <?php _e( 'Then to Notify Customer enable the checkbox & click on the Update.' ); ?> </p> <br />

                        <input type="url" readonly class="large-text" name="certificate_file_name" id="certificate_file_name" value="<?php echo esc_attr( $output_notice ); ?>"><br><br>
                        <input type="hidden" name="certificate_file_url" id="certificate_file_url" value="<?php echo esc_attr( $certificate_file_url ); ?>">
                        <input type="hidden" name="order_id" id="order_id" value="<?php echo esc_attr( $post->ID ); ?>">

                        <button type="button" class="button" id="certificate_upload_btn" style="vertical-align: middle;" data-certificate_name="#certificate_file_name" data-certificate_url="#certificate_file_url"><?php _e( 'Upload Certificate' )?></button>

                        <?php
                            $is_notified = get_post_meta( $post->ID, 'notify_customer_with_certificate', true );
                            $is_notified_checked = isset( $is_notified ) && 'yes' === $is_notified ? 'checked="checked"' : '';
                        ?>

                        <label for="notify_customer_with_certificate" style="margin-left: 10px;">
                            <input type="checkbox" name="notify_customer_with_certificate" id="notify_customer_with_certificate" value="yes" <?php echo esc_attr( $is_notified_checked ); ?> />
                            <?php _e( 'Notify user?', 'prfx-textdomain' )?>
                        </label>

                    </div>
                </fieldset>
            <?php
        }

		/**
		 * Metabox Save
		 *
		 * @param  number $post_id Post ID.
		 * @return void
		 */
		public function save_meta_box( $post_id ) {

			// Checks save status.
			$is_autosave    = wp_is_post_autosave( $post_id );
            $is_revision    = wp_is_post_revision( $post_id );
            $is_valid_nonce = ( isset( $_POST['customer_certificate_wrt_order'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['customer_certificate_wrt_order'] ) ), 'save_customer_certificate_wrt_order' ) ) ? true : false;

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
				return;
            }

            if( isset( $_POST['certificate_file_name'] ) && isset( $_POST['certificate_file_url'] ) ) {

                // Certificate file ID.
                $certificate_file_name = sanitize_text_field( $_POST['certificate_file_name'] );
                update_post_meta( $post_id, 'certificate_file_name', $certificate_file_name );

                // Certificate file URL.
                $certificate_file_url = sanitize_text_field( $_POST['certificate_file_url'] );
                update_post_meta( $post_id, 'certificate_file_url', $certificate_file_url );

                // Record order note.
                $order_note = __( 'Certificate uploaded successfully.' );
                $order      = wc_get_order( $post_id );

                if( is_callable( $order, 'add_order_note' ) ) {
                    $comment_id = $order->add_order_note( $order_note, 0, true );
                }
            }

            if( ! isset( $_POST['notify_customer_with_certificate'] ) || null === $_POST['notify_customer_with_certificate'] ) {
                update_post_meta( $post_id, 'notify_customer_with_certificate', 'no' );
            }

            // Handle button actions.
            if ( isset( $_POST['notify_customer_with_certificate'] ) && 'yes' === $_POST['notify_customer_with_certificate'] ) {

                // Send this order certificate to customer.
                WC()->mailer()->emails['WC_Email_Order_Certificate']->trigger( $order->get_id(), $order );
                update_post_meta( $post_id, 'notify_customer_with_certificate', 'yes' );

                // Record certifiacte delivering status.
                $order      = wc_get_order( $post_id );
                $order->update_status( 'completed', __( 'Certificate sent successfully & ' ), true );
            }
        }
	}
}

/**
 * Kicking this off by creating 'new' instance,
 */
new Woo_Order_Metabox();
