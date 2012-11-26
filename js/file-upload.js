jQuery( document ).ready( function() {


	jQuery( document ).on( 'click', '.cmb-file-upload', function(e) {
		e.preventDefault();

		var container = jQuery( this ).parent();
		var link = jQuery( this );

		var frame = wp.media( {
			title: 'Select file'
		});

		frame.toolbar.on( 'activate:select', function() {
			frame.toolbar.view().set({
				select: {
					style: 'primary',
					text:  'Update file',

					click: function() {
						var selection = frame.state().get('selection'),
							model = selection.first();

						jQuery( container ).find( '.cmb-file-upload-input' ).val( model.id );
						link.hide();

						frame.close();
						container.find( '.cmb-file-holder' ).html( '' );
						container.find( '.cmb-file-holder' ).parent().show();
						jQuery( '<img />', {
							src:    model.attributes.icon
						}).prependTo( container.find( '.cmb-file-holder' ) );

						container.find( '.cmb-file-name' ).html( model.attributes.filename );
					}
				}
			});
		});

	} );

	jQuery( document ).on( 'click', '.cmb-remove-file', function(e) {
		e.preventDefault();

		var container = jQuery( this ).parent();
		container.find( '.cmb-file-holder' ).html( '' ).parent().hide();
		container.find( '.cmb-file-upload-input' ).val( '' );

		container.parent().find( '.cmb-file-upload' ).show();
	} );
	
} );