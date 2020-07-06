<?php
/**
 * Class WC_Email_Order_Certificate file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Order_Certificate', false ) ) :

	/**
	 * Customer Invoice.
	 *
	 * Get rating from customer.
	 *
	 * @class       WC_Email_Order_Certificate
	 * @version     1.0.0
	 * @package     WooCommerce/Classes/Emails
	 * @extends     WC_Email
	 */
	class WC_Email_Order_Certificate extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'order_certificate';
			$this->customer_email = true;
			$this->title          = __( 'Order Certificate' );
			$this->description    = __( 'Customer\'s certificate email can be sent to customers containing their downlodable certificate link.' );
			$this->template_html  = 'emails/order-certificate.php';
			$this->template_plain = 'emails/plain/order-certificate.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Call parent constructor.
			parent::__construct();

			$this->manual = true;
		}

		/**
		 * Get email subject.
		 *
		 * @param bool $paid Whether the order has been paid or not.
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_subject( $paid = false ) {
			if ( $paid ) {
				return __( 'Certificate for order #{order_number} on {site_title}' );
			} else {
				return __( 'Certificate for service from {site_title}' );
			}
		}

		/**
		 * Get email heading.
		 *
		 * @param bool $paid Whether the order has been paid or not.
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_heading( $paid = false ) {
            return __( 'Certificate for order #{order_number}' );
		}

		/**
		 * Get email body.
		 *
		 * @param bool $paid Whether the order has been paid or not.
		 * @since  1.0.0
		 * @return string
		 */
		public function get_default_mail_body( $paid = false ) {

			$mail_body = 'Thank you for shopping with us!
                                    
			Here is your certificate for your order #{order_number}. Click the button below and get your certificate!

			<a href="{certificate_url}" target="_blank" download> Download Certificate </a>

			<a href="{certificate_url_2}" target="_blank" download> Download Certificate </a>';

            return $mail_body;
		}

		/**
		 * Get email subject.
		 *
		 * @return string
		 */
		public function get_subject() {

            if ( $this->object->has_status( array( 'completed', 'processing' ) ) ) {
                $subject = $this->get_option( 'subject_paid', $this->get_default_subject( true ) );
				return apply_filters( 'woocommerce_email_subject_order_certificate_paid', $this->format_string( $subject ), $this->object, $this );
			}

			$subject = $this->get_option( 'subject', $this->get_default_subject() );
			return apply_filters( 'woocommerce_email_subject_order_certificate', $this->format_string( $subject ), $this->object, $this );
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 */
		public function get_heading() {

			if ( $this->object->has_status( wc_get_is_paid_statuses() ) ) {
                $heading = $this->get_option( 'heading_paid', $this->get_default_heading( true ) );
				return apply_filters( 'woocommerce_email_heading_order_certificate_paid', $this->format_string( $heading ), $this->object, $this );
			}

			$heading = $this->get_option( 'heading', $this->get_default_heading() );
			return apply_filters( 'woocommerce_email_heading_order_certificate', $this->format_string( $heading ), $this->object, $this );
		}

		/**
		 * Get email body.
		 *
		 * @return string
		 */
		public function get_mail_body() {
			$body = $this->get_option( 'email_body', $this->get_default_mail_body() );
			return apply_filters( 'woocommerce_email_body_order_certificate', $this->format_string( $body ), $this->object, $this );
		}

		/**
		 * Get order-recieved page.
		 *
		 * @return string
		 */
		public function get_order_certificate_url( $order_id ) {
			$certificate_file_url = get_post_meta( $order_id, 'certificate_file_url', true );
			return $certificate_file_url;
		}

		/**
		 * Get order-recieved page.
		 *
		 * @return string
		 */
		public function get_order_certificate_url_2( $order_id ) {
			$certificate_file_url_2 = get_post_meta( $order_id, 'certificate_file_url_2', true );
			return $certificate_file_url_2;
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_additional_content() {
			return __( 'Thanks for using {site_url}!' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int      $order_id The order ID.
		 * @param WC_Order $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {

			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              		=> $this->object,
					'email_heading'      		=> $this->get_heading(),
					'additional_content' 		=> $this->get_additional_content(),
					'email_body'		 		=> $this->get_mail_body(),
					'get_order_certificate_url' => $this->get_order_certificate_url( $this->object->get_order_number() ),
					'get_order_certificate_url_2' => $this->get_order_certificate_url_2( $this->object->get_order_number() ),
					'sent_to_admin'      		=> false,
					'plain_text'         		=> false,
					'email'              		=> $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              		=> $this->object,
					'email_heading'      		=> $this->get_heading(),
					'additional_content' 		=> $this->get_additional_content(),
					'email_body'		 		=> $this->get_mail_body(),
					'get_order_certificate_url' => $this->get_order_certificate_url( $this->object->get_order_number() ),
					'get_order_certificate_url_2' => $this->get_order_certificate_url_2( $this->object->get_order_number() ),
					'sent_to_admin'      		=> false,
					'plain_text'         		=> true,
					'email'              		=> $this,
				)
			);
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			/* translators: %s: list of placeholders */
			$placeholder_text  = sprintf( __( 'Available placeholders: %s' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
			$this->form_fields = array(
				'subject'            => array(
					'title'       => __( 'Subject' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email heading' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'subject_paid'       => array(
					'title'       => __( 'Subject (Paid)' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_subject( true ),
					'default'     => '',
				),
				'heading_paid'       => array(
					'title'       => __( 'Email heading (Paid)' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_heading( true ),
					'default'     => '',
				),
				'additional_content' => array(
					'title'       => __( 'Additional content' ),
					'description' => __( 'Text to appear below the main email content.' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => $this->get_default_additional_content(),
					'type'        => 'textarea',
					'default'     => '',
					'desc_tip'    => true,
				),
				'email_body' => array(
					'title'       => __( 'Email Body' ),
					'description' => __( 'Main Email body.' ),
					'css'         => 'width:600px; height: 200px;',
					'placeholder' => __( 'N/A' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_mail_body(),
					'desc_tip'    => true,
				),
				'email_type'         => array(
					'title'       => __( 'Email type' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
	}

endif;

return new WC_Email_Order_Certificate();
