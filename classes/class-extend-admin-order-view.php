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
 * Extend Woo Order Page setup
 *
 * @since 1.0.0
 */
class Extend_Admin_Order_View {

    /**
     *  Constructor
     */
    public function __construct() {

        /**
         * Custom styling for orders.
         */
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        /**
         * Manage Order CPT post columns.
         */
        add_filter( 'manage_shop_order_posts_columns', array( $this, 'setup_shop_order_admin_column_list' ), 11 );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_shop_order_custom_columns' ), 11, 2 );
        
        /**
         * Rating / Feedback email template.
         */
        add_filter( 'woocommerce_email_classes', array( $this, 'load_new_email_templates' ) );

        /**
         * Register new operations & respective their actions in Order actions. 
         */
        add_filter( 'woocommerce_order_actions', array( $this, 'ask_customer_for_rating' ) );
        add_action( 'woocommerce_order_action_rating_from_customer', array( $this, 'perform_rating_functionality' ) );

        /**
         * Add HTML editor field to email template.
         */
        add_action( 'woocommerce_email_settings_after', array( $this, 'add_html_editor_custom_field' ) );

        /**
         * Add Positive / Negative review for individual order.
         */
		add_action( 'wp_enqueue_scripts' , array( $this, 'enqueue_front_scripts' ) );
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'feedback_from_customer_on_order_received' ) );

        /**
         * Record feedback from customer for their order
         */
        add_action( 'wp_ajax_woo_order_feedback', array( $this, 'record_woo_order_feedback' ) );
        add_action( 'wp_ajax_nopriv_woo_order_feedback', array( $this, 'record_woo_order_feedback' ) );
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_admin_scripts() {

        wp_register_style( 'extended_woocommerce_order_cpt_page', EXTENDED_WOOCOMMERCE_URI . 'assets/css/order-cpt.css', array(), EXTENDED_WOOCOMMERCE_VER );

        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        
        if( 'edit-shop_order' === $screen_id ) {
            wp_enqueue_style( 'extended_woocommerce_order_cpt_page' );
        }
    }

    /**
     * Manage Order View Columns
     *
     * @since 1.0.0
     * @return void
     */
    public function setup_shop_order_admin_column_list( $columns ) {

        if ( isset( $columns['shipping_address'] ) ) {
			unset( $columns['shipping_address'] );
        }
        
        if ( isset( $columns['order_total'] ) ) {
			unset( $columns['order_total'] );
        }

        if ( isset( $columns['billing_address'] ) ) {
			unset( $columns['billing_address'] );
        }

        if ( isset( $columns['order_status'] ) ) {
			unset( $columns['order_status'] );
        }

        if ( isset( $columns['wc_actions'] ) ) {
			unset( $columns['wc_actions'] );
        }

        return array_merge ( $columns, array (
            'order_date'        =>      __( 'Order initiated at' ),
            'social_details'    =>      __( 'Mobile / Email ID' ),
            'order_status'      =>      __( 'Payment / Order Status' ),
            'ratings_status'    =>      __( 'Ratings / Follow Up Date' ),
            'assign_pay_status' =>      __( 'Assign / Payment Success on' ),
            'wc_actions'        =>      __('Actions'),
        ) );
    }

    /**
     * Display Custom Columns WRT their name
     *
     * @since 1.0.0
     * @return void
     */
    public function render_shop_order_custom_columns( $column, $post_id ) {

        switch ( $column ) {
            // social_details custom column.
            case 'social_details':
                $output = '';
                $email_id  = get_post_meta( $post_id, '_billing_email', true );
                $mobile_no = get_post_meta( $post_id, '_billing_phone', true );

                if( $mobile_no ) {
                    $output .= '<a href="tel:' . $mobile_no . '">' . $mobile_no . '</a>';
                }

                if( $mobile_no && $email_id ) {
                    $output .= ' / <br />';
                }

                if( $email_id ) {
                    $output .= '<a href="mailto:' . $email_id . '">' . $email_id . '</a>';
                }

                echo $output;
            break;

            // ratings_status custom column.
            case 'ratings_status':
                $output = '';
                $order_rating = get_post_meta( $post_id, 'order_feedback', true );

                if( 'positive' === $order_rating ) {
                    $output .= __( 'Positively Rated' );
                } elseif( 'negative' === $order_rating ) {
                    $output .= __( 'Negatively Rated' );
                } elseif( 'asked' === $order_rating ) {
                    $output .= __( 'Asked for Rating' );
                } else {
                    $output .= __( 'Not Asked Yet' );
                }

                $follow_up_email_send_at = get_post_meta( $post_id, 'follow_up_email_send_at', true );
                
                if( isset( $follow_up_email_send_at ) && '' !== $follow_up_email_send_at && 'asked' === $order_rating ) {
                    $output .= ' / <br />';
                    $output .= $follow_up_email_send_at;
                }

                echo $output;
            break;

            // order_status custom column.
            case 'order_status':
                $is_notified_customer = get_post_meta( $post_id, 'notify_customer_with_certificate', true );

                if( isset( $is_notified_customer ) && 'yes' === $is_notified_customer ) {
                    printf( '<mark class="order-status certificate-delivered"><span>%s</span></mark>', esc_html( 'Done' ) );
                }

            break;

            // assign_pay_status custom column.
            case 'assign_pay_status':
                $output = '';
                $paid_date  = get_post_meta( $post_id, '_paid_date', true );

                if( $paid_date ) {
                    $output .= $paid_date;
                }

                echo $output;
            break;
        }
    }

    /**
     * Register new Email-templates - Ratings/Feedback.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_new_email_templates( $emails ) {

        $emails['WC_Email_Order_Ratings'] = include EXTENDED_WOOCOMMERCE_DIR . 'emails/class-wc-email-order-ratings.php';
        $emails['WC_Email_Order_Certificate'] = include EXTENDED_WOOCOMMERCE_DIR . 'emails/class-wc-email-order-certificate.php';

        return $emails;
    }

    /**
     * Ask custom 'Feedback' order action to metabox
     *
     * @since 1.0.0
     * @return void
     */
    public function ask_customer_for_rating( $order_actions ) {

        $order_actions['rating_from_customer'] = __( 'Ask Feedback from customer' );

        return $order_actions;
    }

    /**
     * Rating order action
     *
     * @since 1.0.0
     * @return void
     */
    public function perform_rating_functionality( $order ) {

        $order_id = $order->get_id();

        do_action( 'woocommerce_before_resend_order_emails', $order, 'new_order' );

        WC()->payment_gateways();
        WC()->shipping();
        WC()->mailer()->emails['WC_Email_Order_Ratings']->trigger( $order_id, $order );

        do_action( 'woocommerce_after_resend_order_email', $order, 'new_order' );

        // Change the post saved message.
        add_filter( 'redirect_post_location', 'WC_Meta_Box_Order_Actions::set_email_sent_message' );

        update_post_meta( $order_id, 'order_feedback', 'asked' );

		update_post_meta( $order_id, 'follow_up_email_send_at', date( 'Y-m-d H:i:s' ) );
    }

    /**
     * Add custom HTML editor field to Review option template
     *
     * @since 1.0.0
     * @return void
     */
    public function add_html_editor_custom_field( $email ) {

        if( 'order_rating' === $email->id ) {

            ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th>
                                <label for="woocommerce_order_rating_body"><b><?php esc_html_e( 'Email Body:' ); ?></b></label>
                            </th>
                            <td>
                                <?php
                                    $initial_data = 'Thank you for shopping with us!
                                    
                                    We would love if you could help us and other customers by reviewing products that you recently purchased in order #{order_number}. It only takes a minute and it would really help others. Click the button below and leave your review!
                                    
                                    <a href="{rating_url}" target="_blank"> Review </a>';

                                    wp_editor(
                                        $initial_data,
                                        'woocommerce_order_rating_body',
                                        array(
                                            'media_buttons' => true,
                                            'textarea_rows' => 15,
                                            'tabindex' => 4,
                                            'tinymce'  => array(
                                                'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
                                            ),
                                        )
                                    );

                                    $output = '<br /> <p> <b> Following variables can be used for dynamic content - Paste it in anywhere in the content (including {} backets). </b>';
                                    $output .= '<ol> <li> <b> {order_number} </b> - Customer\'s unique order number </li>';
                                    $output .= '<li> <b> {site_title} </b> - This site title (your site name) </li>';
                                    $output .= '<li> <b> {rating_url} </b> - Rating URL link (apply it to any text/button from the editor) </li>';
                                    $output .= '<li> <b> {site_url} </b> - This site URL </li> </ol>';

                                    echo $output;
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php
        }

        if( 'order_certificate' === $email->id ) {

            ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th>
                                <label for="woocommerce_order_certificate_body"><b><?php esc_html_e( 'Email Body:' ); ?></b></label>
                            </th>
                            <td>
                                <?php
                                    $initial_data = 'Thank you for shopping with us!

                                    Here is your certificate for your order #{order_number}. Click the button below and get your certificate!

                                    <a href="{certificate_url}" target="_blank" download> Download Certificate </a>';

                                    wp_editor(
                                        $initial_data,
                                        'woocommerce_order_certificate_body',
                                        array(
                                            'media_buttons' => true,
                                            'textarea_rows' => 15,
                                            'tabindex' => 4,
                                            'tinymce'  => array(
                                                'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
                                            ),
                                        )
                                    );

                                    $output = '<br /> <p> <b> Following variables can be used for dynamic content - Paste it in anywhere in the content (including {} backets). </b>';
                                    $output .= '<ol> <li> <b> {order_number} </b> - Customer\'s unique order number </li>';
                                    $output .= '<li> <b> {site_title} </b> - This site title (your site name) </li>';
                                    $output .= '<li> <b> {certificate_url} </b> - Certificate downlodable URL link (apply it to any text/button from the editor) </li>';
                                    $output .= '<li> <b> {site_url} </b> - This site URL </li> </ol>';

                                    echo $output;
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php
        }
    }

    /**
     * Add feedback Positive / Negative option on order-received page.
     *
     * @since 1.0.0
     * @return void
     */
    public function feedback_from_customer_on_order_received( $order ) {

        $order_id = $order->get_id();

        $feedback_for_current_order = get_post_meta( $order_id, 'order_feedback', true );

        if( 'positive' === $feedback_for_current_order ) {
            return;
        }

		echo '<style type="text/css"> .order-feedback-wrapper{display:flex;align-items:center;justify-content:center;min-height:60px;padding:20px;background:#fff;border:1px solid #e9e5e5;border-radius:4px;box-shadow:0 3px 6px 0 rgba(0,0,0,.05);font-size:15px;color:#858585}.feedback-label-content{margin-right:12px}.feedback-svg{width:1.5em;height:1.5em;vertical-align:sub;display:inline-block;cursor:pointer;transition:.5s}.positive-feedback{margin-right:7px}.feedback-svg:hover{transform:scale(1.3)} </style>';

        ?>
            <div class="order-feedback-wrapper" data-order-id="<?php echo esc_attr( $order_id ); ?>">
                <div class="feedback-label-content">
                    <label> <?php _e( 'Please review your experience with your order and services that you purchased at ' . get_bloginfo( 'name' ) . '.' ); ?> </label>
                </div>

                <div class="feedback-svg-content">
                    <span id="positive_order_feedback" class="positive-feedback feedback-svg">
                        <svg viewBox="0 0 24 28"><path d="M4 21c0-0.547-0.453-1-1-1s-1 0.453-1 1 0.453 1 1 1 1-0.453 1-1zM22 12c0-1.062-0.953-2-2-2h-5.5c0-1.828 1.5-3.156 1.5-5 0-1.828-0.359-3-2.5-3-1 1.016-0.484 3.406-2 5-0.438 0.453-0.812 0.938-1.203 1.422-0.703 0.906-2.562 3.578-3.797 3.578h-0.5v10h0.5c0.875 0 2.312 0.562 3.156 0.859 1.719 0.594 3.5 1.141 5.344 1.141h1.891c1.766 0 3-0.703 3-2.609 0-0.297-0.031-0.594-0.078-0.875 0.656-0.359 1.016-1.25 1.016-1.969 0-0.375-0.094-0.75-0.281-1.078 0.531-0.5 0.828-1.125 0.828-1.859 0-0.5-0.219-1.234-0.547-1.609 0.734-0.016 1.172-1.422 1.172-2zM24 11.984c0 0.906-0.266 1.797-0.766 2.547 0.094 0.344 0.141 0.719 0.141 1.078 0 0.781-0.203 1.563-0.594 2.25 0.031 0.219 0.047 0.453 0.047 0.672 0 1-0.328 2-0.938 2.781 0.031 2.953-1.984 4.688-4.875 4.688h-2.016c-2.219 0-4.281-0.656-6.344-1.375-0.453-0.156-1.719-0.625-2.156-0.625h-4.5c-1.109 0-2-0.891-2-2v-10c0-1.109 0.891-2 2-2h4.281c0.609-0.406 1.672-1.813 2.141-2.422 0.531-0.688 1.078-1.359 1.672-2 0.938-1 0.438-3.469 2-5 0.375-0.359 0.875-0.578 1.406-0.578 1.625 0 3.187 0.578 3.953 2.094 0.484 0.953 0.547 1.859 0.547 2.906 0 1.094-0.281 2.031-0.75 3h2.75c2.156 0 4 1.828 4 3.984z" fill="currentColor"></path></svg>
                    </span>

                    <span id="negative_order_feedback" class="negative-feedback feedback-svg">
                        <svg viewBox="0 0 24 28"><path d="M4 7c0-0.547-0.453-1-1-1s-1 0.453-1 1 0.453 1 1 1 1-0.453 1-1zM22 16c0-0.578-0.438-1.984-1.172-2 0.328-0.375 0.547-1.109 0.547-1.609 0-0.734-0.297-1.359-0.828-1.859 0.187-0.328 0.281-0.703 0.281-1.078 0-0.719-0.359-1.609-1.016-1.969 0.047-0.281 0.078-0.578 0.078-0.875 0-1.828-1.156-2.609-2.891-2.609h-2c-1.844 0-3.625 0.547-5.344 1.141-0.844 0.297-2.281 0.859-3.156 0.859h-0.5v10h0.5c1.234 0 3.094 2.672 3.797 3.578 0.391 0.484 0.766 0.969 1.203 1.422 1.516 1.594 1 3.984 2 5 2.141 0 2.5-1.172 2.5-3 0-1.844-1.5-3.172-1.5-5h5.5c1.047 0 2-0.938 2-2zM24 16.016c0 2.156-1.844 3.984-4 3.984h-2.75c0.469 0.969 0.75 1.906 0.75 3 0 1.031-0.063 1.969-0.547 2.906-0.766 1.516-2.328 2.094-3.953 2.094-0.531 0-1.031-0.219-1.406-0.578-1.563-1.531-1.078-4-2-5.016-0.594-0.625-1.141-1.297-1.672-1.984-0.469-0.609-1.531-2.016-2.141-2.422h-4.281c-1.109 0-2-0.891-2-2v-10c0-1.109 0.891-2 2-2h4.5c0.438 0 1.703-0.469 2.156-0.625 2.25-0.781 4.203-1.375 6.609-1.375h1.75c2.844 0 4.891 1.687 4.875 4.609v0.078c0.609 0.781 0.938 1.781 0.938 2.781 0 0.219-0.016 0.453-0.047 0.672 0.391 0.688 0.594 1.469 0.594 2.25 0 0.359-0.047 0.734-0.141 1.078 0.5 0.75 0.766 1.641 0.766 2.547z" fill="currentColor"></path></svg>
                    </span>
                </div>
            </div>
        <?php
    }

    /**
     * Enqueue frontend scripts
     *
     * @since 1.0
     */
    public function enqueue_front_scripts() {

        wp_enqueue_script(
			'extended-woo-orders-feeedback',
			EXTENDED_WOOCOMMERCE_URI . 'assets/js/order-feedback.js',
			array( 'jquery' ),
			EXTENDED_WOOCOMMERCE_VER,
			true
		);

        // Use PHP vars in AJAX JS using wp_localize_script().
        wp_localize_script(
            'extended-woo-orders-feeedback',
            'extended_woo_order_vars',
            array(
                'extended_woo_order_nonce' 	=> 		wp_create_nonce( 'extended_woo_order_nonce' ),
                'thank_you_text' 			=> 		apply_filters( 'extended_woo_thank_you_feedback_text', 'Thank you for our valuable feedback!' ),
                'ajaxurl' 					=> 		admin_url( 'admin-ajax.php' ),
            )
        );
    }

    /**
     * Get feedback for customer's order.
     * 
     * @since 1.0
     */
    public function record_woo_order_feedback() {

        check_ajax_referer( 'extended_woo_order_nonce', 'security' );

        (int) $order_id = $_POST['order_id'];
        $button = $_POST['button_id'];

        if( $order_id && isset( $button ) ) {
            // Update current order Feedback.
            if( 'positive_order_feedback' === $button ) {
                update_post_meta( $order_id, 'order_feedback', 'positive' );
            } else {
                update_post_meta( $order_id, 'order_feedback', 'negative' );
            }
        }
    }
}

/**
 * Kicking this off by creating 'new' instance.
 */
new Extend_Admin_Order_View();
