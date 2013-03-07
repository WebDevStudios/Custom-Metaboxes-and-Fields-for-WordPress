jQuery( document ).ready( function() {

	jQuery( document ).on( 'click', '.cmb-file-upload', function(e) {

		e.preventDefault();

		var container = jQuery( this ).parent();
		var link = jQuery( this );

		var frame = wp.media( {
			state: 'cmb-file',
			states: [ new CMBSelectFileController() ]
		})

		frame.on( 'toolbar:create:cmb-file', function( toolbar ) {
			this.createSelectToolbar( toolbar, {
				text: 'Select file'
			});
		}, frame );

		frame.open();

		frame.state('cmb-file').on( 'select', function() {

			var selection = frame.state().get('selection'),
				model = selection.first(),
				fileHolder = container.find( '.cmb-file-holder' );

			jQuery( container ).find( '.cmb-file-upload-input' ).val( model.id );
			link.hide();

			frame.close();

			var fileClass = ( model.attributes.type === 'image' ) ? 'type-image' : 'type-file';
			fileHolder.addClass( fileClass );
			fileHolder.html( '' );
			fileHolder.parent().show();

			jQuery( '<img />', {
				src: model.attributes.type === 'image' ? model.attributes.sizes.thumbnail.url : model.attributes.icon
			}).prependTo( container.find( '.cmb-file-holder' ) );

			if ( model.attributes.type !== 'image' )
				fileHolder.append( jQuery('<div class="cmb-file-name" />').html( '<strong>' + model.attributes.filename + '</strong>' ) );

		});

	} );

	jQuery( document ).on( 'click', '.cmb-remove-file', function(e) {
		e.preventDefault();
		var container = jQuery( this ).parent().parent();
		container.find( '.cmb-file-holder' ).html( '' ).parent().hide();
		container.find( '.cmb-file-upload-input' ).val( '' );
		container.find( '.cmb-file-upload' ).show();
	} );

	// wp.media.controller.FeaturedImage
	// ---------------------------------
	var CMBSelectFileController = wp.media.controller.Library.extend({
		defaults: _.defaults({
			id:         'cmb-file',
			filterable: 'uploaded',
			multiple:   false,
			toolbar:    'cmb-file',
			title:      'Select File',
			priority:   60,
			syncSelection: false
		}, wp.media.controller.Library.prototype.defaults ),

		initialize: function() {
			var library, comparator;

			wp.media.controller.Library.prototype.initialize.apply( this, arguments );

			library    = this.get('library');
			comparator = library.comparator;
		},

		activate: function() {
			this.updateSelection();
			this.frame.on( 'open', this.updateSelection, this );
			wp.media.controller.Library.prototype.activate.apply( this, arguments );
		},

		deactivate: function() {
			this.frame.off( 'open', this.updateSelection, this );
			wp.media.controller.Library.prototype.deactivate.apply( this, arguments );
		},

		updateSelection: function() {
			var selection = this.get('selection'),
				id = wp.media.view.settings.post.featuredImageId,
				attachment;

			if ( '' !== id && -1 !== id ) {
				attachment = wp.media.model.Attachment.get( id );
				attachment.fetch();
			}

			selection.reset( attachment ? [ attachment ] : [] );
		}
	});

} );