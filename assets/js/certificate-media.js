/**
 * Load media uploader on pages with our custom metabox
 */
jQuery(document).ready(function($){

	'use strict';

	// Instantiates the variable that holds the media library frame.
	var metaImageFrame;

	// Runs when the media button is clicked.
	$( 'body' ).click(function(e) {

		// Get the btn
		var btn = e.target;

		// Check if it's the upload button
		if ( ! btn || ! $( btn ).attr( 'data-certificate_name' ) ) return;

		// Get the field target
        var certificate_name = $( btn ).data( 'certificate_name' );
		var certificate_url = $( btn ).data( 'certificate_url' );

		var certificate_name_2 = $( btn ).data( 'certificate_name_2' );
		var certificate_url_2 = $( btn ).data( 'certificate_url_2' );

		// Prevents the default action from occuring.
		e.preventDefault();

		// Sets up the media library frame
		metaImageFrame = wp.media.frames.metaImageFrame = wp.media({
            title: 'Select Certificate',
            button: { text:  'Attach this file' },
            library : { type: 'application/pdf' },
            multiple: true,
        });

		// Runs when an image is selected.
		metaImageFrame.on('select', function() {

			// Grabs the attachment selection and creates a JSON representation of the model.
			var media_attachment = metaImageFrame.state().get('selection').map(

                function( attachment ) {

                    attachment.toJSON();
                    return attachment;
				}
			);

			//loop through the array and do things with each attachment.
			var i;

			for ( i = 0; i < media_attachment.length; ++i ) {
				if( 0 == i ) {
					// Sends the attachment URL to our custom image input field.
					$( certificate_name ).val(media_attachment[i].attributes.filename);
					$( certificate_url ).val(media_attachment[i].attributes.url);
				}

				if( 1 == i ) {
					$( certificate_name_2 ).val(media_attachment[i].attributes.filename);
					$( certificate_url_2 ).val(media_attachment[i].attributes.url);
				}
			}

		});


		// Opens the media library frame.
		metaImageFrame.open();
	});
});
