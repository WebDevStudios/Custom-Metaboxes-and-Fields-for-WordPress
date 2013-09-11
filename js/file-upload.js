jQuery( document ).ready( function() {

	jQuery( document ).on( 'click', '.cmb-file-upload', function(e) {

		e.preventDefault();

		var link = jQuery( this );
		var container = jQuery( this ).parent();

		var frameArgs = {
			multiple: false,
			title: 'Select File',
		}

		library = container.attr( 'data-type' ).split(',');
		if ( library.length > 0 )
			frameArgs.library = { type: library }

		var CMB_Frame = wp.media( frameArgs );

		CMB_Frame.on( 'select', function() {

			var selection = CMB_Frame.state().get('selection'),
				model = selection.first(),
				fileHolder = container.find( '.cmb-file-holder' );

			jQuery( container ).find( '.cmb-file-upload-input' ).val( model.id );

			link.hide(); // Hide 'add media' button

			CMB_Frame.close();

			var fileClass = ( model.attributes.type === 'image' ) ? 'type-image' : 'type-file';
			fileHolder.addClass( fileClass );
			fileHolder.html( '' );
			fileHolder.parent().show();

			if ( model.attributes.type === 'image' ) {

				var data = {
					action: 'cmb_request_image',
					id: model.attributes.id,
					width: container.width(),
					height: container.height(),
					crop: fileHolder.attr('data-crop')
				}

				jQuery.post( ajaxurl, data, function( src ) {
					// Insert image
					jQuery( '<img />', { src: src } ).prependTo( fileHolder );
				}).fail( function() {
					// Fallback - insert full size image.
					jQuery( '<img />', { src: model.attributes.url } ).prependTo( fileHolder );
				});

			} else {

				jQuery( '<img />', { src: model.attributes.icon } ).prependTo( fileHolder );
				fileHolder.append( jQuery('<div class="cmb-file-name" />').html( '<strong>' + model.attributes.filename + '</strong>' ) );

			}

		});

		CMB_Frame.open();

	} );

	jQuery( document ).on( 'click', '.cmb-remove-file', function(e) {

		e.preventDefault();

		var container = jQuery( this ).parent().parent();

		container.find( '.cmb-file-holder' ).html( '' ).parent().hide();
		container.find( '.cmb-file-upload-input' ).val( '' );
		container.find( '.cmb-file-upload' ).show().css( 'display', 'inline-block' );

	} );

} );