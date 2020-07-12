(function($) {

	/**
	 * WooCommerce Checkout Page Fields Handler JS
	 *
	 * @since 1.0.0
	 */
	var Extended_Checkout_Fields_Handler = {

		init: function() {
			this._bind();
            this._hide_all_fields();
        },

        /**
		 * Binds events for the theme layout admin edit interface.
		 *
		 * @since 1.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
            // Type of Organization select-field.
            $( document.body ).on( 'change', '#billing_wooccm11', this._change_options_based_on_type_of_organization );

            // Modification in Entity PAN Details.
            $( document.body ).on( 'change', '#billing_wooccm45', this._hide_conditional_entity_pan_details );

            // Modification/Change in Name & Address of Proprietor.
            $( document.body ).on( 'change', '#billing_wooccm46', this._hide_conditional_proprietor_name_n_address );

            // Change in Entity/Business/Branch Address.
            $( document.body ).on( 'change', '#billing_wooccm47', this._hide_conditional_entity_business_branch_address );

            // Change in Bank Details.
            $( document.body ).on( 'change', '#billing_wooccm48', this._hide_conditional_change_bank_details );

            // Changes in other Business Details.
            $( document.body ).on( 'change', '#billing_wooccm49', this._hide_conditional_change_other_business_details );

            // Modification/Change in Partner.
            $( document.body ).on( 'change', '#billing_wooccm52', this._hide_conditional_proprietor_name_n_address );

            // Modification/Change in Partner.
            $( document.body ).on( 'change', '#billing_wooccm53', this._hide_conditional_entity_business_branch_address );

            // Modification/Change in Director.
            $( document.body ).on( 'change', '#billing_wooccm58', this._hide_conditional_proprietor_name_n_address );

            // Modification/Change in CEO, Secretary & Managing Trustee.
            $( document.body ).on( 'change', '#billing_wooccm87', this._hide_conditional_proprietor_name_n_address );

            // Modification in Entity PAN Details (Karta).
            $( document.body ).on( 'change', '#billing_wooccm93', this._hide_conditional_entity_pan_details );

            // Modification/Change in Name & Address of Karta.
            $( document.body ).on( 'change', '#billing_wooccm94', this._hide_conditional_proprietor_name_n_address );
        },

		/**
		 * Render Selectbox based fields
		 *
		 * @since 1.0.0
		 */
		_change_options_based_on_type_of_organization: function( e ) {

			e.preventDefault();

            var type_of_org = this.value;

            if( type_of_org.indexOf( 'Section' ) != -1 || type_of_org.indexOf( 'Govt' ) != -1 ) {
                var dont_exclude = ['wooccm56','wooccm45','wooccm58','wooccm53','wooccm48','wooccm49'];
                Extended_Checkout_Fields_Handler._hide_all_fields();
                Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
            }            

            switch( type_of_org ) {

                case 'Proprietorship':
                    var dont_exclude = ['wooccm44','wooccm45','wooccm46','wooccm47','wooccm48','wooccm49'];
                    Extended_Checkout_Fields_Handler._hide_all_fields();
                    Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                    Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
                break;

                case 'Partnership':
                    var dont_exclude = ['wooccm50','wooccm45','wooccm52','wooccm53','wooccm48','wooccm49'];
                    Extended_Checkout_Fields_Handler._hide_all_fields();
                    Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                    Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
                break;

                case 'Limited Liability Partnership':
                case 'Private Limited Company':
                case 'Public Limited Company':
                    var dont_exclude = ['wooccm56','wooccm45','wooccm58','wooccm53','wooccm48','wooccm49'];
                    Extended_Checkout_Fields_Handler._hide_all_fields();
                    Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                    Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
                break;

                case 'Society':
                    var dont_exclude = ['wooccm86','wooccm45','wooccm87','wooccm53','wooccm48','wooccm49'];
                    Extended_Checkout_Fields_Handler._hide_all_fields();
                    Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                    Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
                break;

                case 'Trust':
                    var dont_exclude = ['wooccm86','wooccm45','wooccm87','wooccm53','wooccm48','wooccm49'];
                    Extended_Checkout_Fields_Handler._hide_all_fields();
                    Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                    Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
                break;

                case 'HUF':
                    var dont_exclude = ['wooccm92','wooccm93','wooccm94','wooccm53','wooccm48','wooccm49'];
                    Extended_Checkout_Fields_Handler._hide_all_fields();
                    Extended_Checkout_Fields_Handler._hide_all_checkboxes_fields();
                    Extended_Checkout_Fields_Handler._hide_conditional_fields( dont_exclude );
                break;
            }
        },
        
        /**
		 * Hide all fields on ready.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_all_fields
		 */
		_hide_all_fields: function()
		{
            var update_fields = ['city','state','wooccm13','wooccm45','wooccm48','wooccm49','wooccm53','wooccm56','wooccm58','wooccm86','wooccm87','wooccm14','wooccm15','postcode','wooccm16','wooccm17','wooccm18','wooccm19','wooccm20','wooccm21','wooccm22','wooccm23','wooccm24','wooccm25','wooccm26','wooccm27','wooccm28','wooccm29','wooccm30','wooccm31','wooccm32','wooccm33','wooccm34','wooccm35','wooccm36'];

            $.each( update_fields , function( index, field ) {
                $('#billing_' + field + '_field').hide();
            });
        },

        /**
		 * Hide all fields on ready.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_all_checkboxes_fields
		 */
		_hide_all_checkboxes_fields: function() 
		{
            var update_fields = ['wooccm45','wooccm46','wooccm47','wooccm48','wooccm49','wooccm21','wooccm52','wooccm53','wooccm58','wooccm87','wooccm93','wooccm94'];

            $.each( update_fields , function( index, field ) {
    			if( $( '#billing_' + field ).is( ':checked' ) ) {
                    $('#billing_' + field).prop( "checked", false );
                }
            });
        },

        /**
		 * Hide dependent fields conditionally.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_conditional_fields
		 */
		_hide_conditional_fields: function( except_fields )
		{
            $.each( except_fields , function( index, field ) {
                $('#billing_' + field + '_field').show();
            });
        },

        /**
		 * Callback for when the button to launch the field is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_conditional_entity_pan_details
		 */
		_hide_conditional_entity_pan_details: function( e )
		{
			if( $( this ).is( ':checked' ) ) {
                $('#billing_wooccm25_field').show();
			} else {
				$('#billing_wooccm25_field').hide();
			}
        },

        /**
		 * Callback for when the button to launch the field is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_conditional_proprietor_name_n_address
		 */
		_hide_conditional_proprietor_name_n_address: function( e )
		{
            var update_fields = ['wooccm24','wooccm26','wooccm27','wooccm28','wooccm29','wooccm30','wooccm31','wooccm32','wooccm33','wooccm34','wooccm35'];

            if( $( this ).is( ':checked' ) ) {
                $.each( update_fields , function( index, field ) {
                    $('#billing_' + field + '_field').show();
                });
			} else {
                $.each( update_fields , function( index, field ) {
                    $('#billing_' + field + '_field').hide();
                });
			}
        },

        /**
		 * Callback for when the button to launch the field is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_conditional_entity_business_branch_address
		 */
		_hide_conditional_entity_business_branch_address: function( e )
		{
            var update_fields = ['wooccm29','wooccm30','wooccm31','wooccm32','wooccm33','wooccm34'];

			if( $( this ).is( ':checked' ) ) {
                $.each( update_fields , function( index, field ) {
                    $('#billing_' + field + '_field').show();
                });
			} else {
                $.each( update_fields , function( index, field ) {
                    $('#billing_' + field + '_field').hide();
                });
			}
        },

        /**
		 * Callback for when the button to launch the field is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_conditional_change_bank_details
		 */
		_hide_conditional_change_bank_details: function( e )
		{

            var update_fields = ['wooccm17','wooccm18','wooccm19','wooccm20','wooccm21'];

			if( $( this ).is( ':checked' ) ) {
                $.each( update_fields , function( index, field ) {
                    $('#billing_' + field + '_field').show();
                });
			} else {
				$.each( update_fields , function( index, field ) {
                    $('#billing_' + field + '_field').hide();
                });
			}
        },

        /**
		 * Callback for when the button to launch the field is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _hide_conditional_change_other_business_details
		 */
		_hide_conditional_change_other_business_details: function( e )
		{
			if( $( this ).is( ':checked' ) ) {
                $('#billing_wooccm16_field').show();
			} else {
				$('#billing_wooccm16_field').hide();
			}
        },
	};

	$( document ).ready( function( $ ) {
		Extended_Checkout_Fields_Handler.init();
	});

})(jQuery);
