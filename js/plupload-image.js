var tf_image_uploaders = {};

var ImageWellController = new function() {

    var self = this;

    self.addFileUploadCallbackForImageWell = function( well_id, callback ) {

        if ( ! tf_image_uploaders[ well_id ] ) {

            setTimeout( function() {
                self.addFileUploadCallbackForImageWell( well_id, callback );
            }, 1000 );

            return;
        }

            tf_image_uploaders[ well_id ].bind( 'FileUploaded', function() {

                // we set a timeout, else the input value won;t be set yet.
                setTimeout( function() {

                   callback();

                }, 100 );

            } );

    }

}

jQuery( document ).ready( function($) {
	// Object containing all the plupload uploaders
	var 
		hundredMB				= null,
		max						= null
	;

	$( '.delete-image' ).bind( 
		'click',
		function(e)
		{
			e.preventDefault()
			var uploader = jQuery( this ).closest( '.hm-uploader' )
			
			uploader.removeClass( 'with-image' )
			uploader.find( '.current-image' ).fadeOut('fast', function() {
				uploader.removeClass('with-image');
			})
			uploader.find( '.upload-form' ).show()
			uploader.find( '.field-val' ).val( '' )
		}
	);

	// Using all the image prefixes
	var totalRWMB = $( 'input:hidden.rwmb-image-prefix' ).length
	
	$( 'input:hidden.rwmb-image-prefix' ).each( function() 
	{
		prefix = $( this ).val();
		
		var input = jQuery( this )
		// Adding container, browser button and drag ang drop area
		var tf_well_plupload_init = $.extend( 
			{
				container:		prefix + '-container',
				browse_button:	prefix + '-browse-button',
				drop_element:	prefix + '-dragdrop'
			},
			tf_well_plupload_defaults
		);
			
		
		tf_well_plupload_init.multipart_params.field_id = prefix;
		tf_well_plupload_init.multipart_params.size = input.parent().find( '.upload-form' ).attr( 'data-size' );

		if ( totalRWMB == 1 )
			tf_well_plupload_init.filters[0].extensions = input.parent().find( '.upload-form' ).attr( 'data-extensions' );

		// Create new uploader
		tf_image_uploaders[ prefix ] = new plupload.Uploader( $.extend( true, {}, tf_well_plupload_init ) );

		tf_image_uploaders[ prefix ].init();
		//
		tf_image_uploaders[ prefix ].bind( 
			'FilesAdded', 
			function( up, files )
			{
				hundredMB	= 100 * 1024 * 1024, 
				max			= parseInt( up.settings.max_file_size, 10 );
				plupload.each(
					files, 
					function( file )
					{
						input.closest( '.hm-uploader' ).find( '.loading-block' ).fadeIn('fast', function() {
						
							input.closest( '.hm-uploader' ).addClass( 'loading' );
						
						} );
					}
				);
				up.refresh();
				up.start();
			}
		);

		tf_image_uploaders[ prefix ].bind(
			'FileUploaded', 
			function( up, file, response ) 
			{
				response_xml = $.parseXML( response.response );
				res = wpAjax.parseAjaxResponse( response_xml, 'ajax-response' );
				if ( false === res.errors )
				{
					res		= res.responses[0];
					img_id	= res.data;
					img_src	= res.supplemental.thumbnail;
					img_edit = res.supplemental.edit_link;
					
					$(input).closest('.hm-uploader').find( '.loading-block' ).fadeOut('fast' )
					
					$(input).closest('.hm-uploader').find( '.current-image img' )
						.attr('src',img_src).removeAttr( 'width' ).removeAttr('height');
					
					$(input).closest('.hm-uploader').find( '.current-image' ).show();
					
					setTimeout( function() { $(input).closest('.hm-uploader').find( '.current-image' ).fadeIn('fast', function() {
						
						$(input).closest('.hm-uploader').removeClass( 'loading' );
						$(input).closest('.hm-uploader').addClass( 'with-image' ); 
						
					} ) }, 100 )
					
					$(input).closest('.hm-uploader').find( '.field-val' ).val( img_id )
				}
			});
	});
});