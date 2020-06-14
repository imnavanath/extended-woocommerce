(function($){

	/**
	 * WooCommerce Orders Frontend AJAX script data
	 *
	 * @since 1.0.0
	 */
	var extende_woo_orders = {

		init: function() {
			$( document.body ).on( 'click', '.feedback-svg', this._record_ajax_woo_order_feedback );
		},

		/**
		 * Render feedback results
		 *
		 * @since 1.0.0
		 */
		_record_ajax_woo_order_feedback: function( e ) {

			e.preventDefault();

			var order_id = $(this).parents('.order-feedback-wrapper').attr('data-order-id');
			var btn_id = this.id;
			var feedback_wrapper = $('.order-feedback-wrapper');

			$.ajax({
				url  : extended_woo_order_vars.ajaxurl,
				type : 'POST',
				data : {
					action  	: 'woo_order_feedback',
					order_id 	: order_id,
					button_id 	: btn_id,
					security  	: extended_woo_order_vars.extended_woo_order_nonce
				},
				success: function ( data ) {
					setTimeout(function() {
						feedback_wrapper.text( extended_woo_order_vars.thank_you_text );
					}, 500);
				},
			})
			.fail(function( jqXHR ) {
				console.log( jqXHR.status + ' ' + jqXHR.responseText, true );
			})
		},
	};

	$(document).ready(function($) {
		extende_woo_orders.init();
	});

})(jQuery);
