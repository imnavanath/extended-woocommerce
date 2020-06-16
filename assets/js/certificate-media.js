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

		// Prevents the default action from occuring.
		e.preventDefault();

		// Sets up the media library frame
		metaImageFrame = wp.media.frames.metaImageFrame = wp.media({
            title: 'Select File',
            button: { text:  'Attach this file' },
            library : { type: 'application/pdf' },
            multiple: false
        });

		// Runs when an image is selected.
		metaImageFrame.on('select', function() {

			// Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = metaImageFrame.state().get('selection').first().toJSON();

			// Sends the attachment URL to our custom image input field.
            $( certificate_name ).val(media_attachment.filename);
			$( certificate_url ).val(media_attachment.url);
		});

		// Opens the media library frame.
		metaImageFrame.open();
	});
});