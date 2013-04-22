/**
 * Custom jQuery for Custom Metaboxes and Fields
 */

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global jQuery, tb_show, tb_remove */

/**
 * Callback handler.
 *
 * Use the methods addCallbackForInit, addCallbackForClonedField and addCallbackForDeleteField
 * Use these to add custom code for your fields.
 */
var CMB = {
	
	_callbacks: [],
	
	addCallbackForClonedField: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				CMB.addCallbackForClonedField( fieldName[i], callback );

		this._callbacks[fieldName] = this._callbacks[fieldName] ? this._callbacks[fieldName] : []
		this._callbacks[fieldName].push( callback )
	},
	
	clonedField: function( el ) {

		var _this = this
		
		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function(i, el) {

			el = jQuery( el )
			var callbacks = _this._callbacks[el.attr( 'data-class') ]
		
			if ( callbacks ) {
				for (var a = 0; a < callbacks.length; a++) {
					callbacks[a]( el )
				}
			}

		})
	},

	_initCallbacks: [],

	addCallbackForInit: function( callback ) {

		this._initCallbacks.push( callback )
	
	},

	init: function() {

		var _this = this;
		
		// also check child elements
		
		var callbacks = _this._initCallbacks;
		
		if ( callbacks )
			for ( var a = 0; a < callbacks.length; a++)
				callbacks[a]();
			
	}

};

jQuery(document).ready(function ($) {

	'use strict';

	var formfield;
	var formfieldobj;

	CMB.init();

	jQuery( document ).on( 'click', '.delete-field', function( e ) {

		e.preventDefault();
		var a = jQuery( this );

		a.closest( '.field-item' ).remove();

	} );

	jQuery( document ).on( 'click', '.repeat-field', function( e ) {

	    e.preventDefault();
	    var el = jQuery( this );

	    var newT = el.prev().clone();

	    newT.removeClass('hidden');
	    
	    newT.find('input[type!="button"]').not('[readonly]').val('');
	    newT.find( '.cmb_upload_status' ).html('');
	    newT.insertBefore( el.prev() );

	    // Recalculate group ids & update the name fields..
		var index = 0;
		var field = $(this).closest('.field' );
		var attrs = ['id','name','for'];	
		
		field.children('.field-item').not('.hidden').each( function() {

			var search  = field.hasClass( 'CMB_Group_Field' ) ? /cmb-group-(\d|x)*/ : /cmb-field-(\d|x)*/;
			var replace = field.hasClass( 'CMB_Group_Field' ) ? 'cmb-group-' + index : 'cmb-field-' + index;

			$(this).find('[id],[for],[name]').each( function() {

				for ( var i = 0; i < attrs.length; i++ )
					if ( typeof( $(this).attr( attrs[i] ) ) !== 'undefined' )
						$(this).attr( attrs[i], $(this).attr( attrs[i] ).replace( search, replace ) );
				
			} );

			index += 1;

		} );

	    CMB.clonedField( newT )

	} );

});


/**
 * ColorPickers
 */

CMB.addCallbackForInit( function() {

	// Colorpicker
	jQuery('input:text.cmb_colorpicker').wpColorPicker();

} );

CMB.addCallbackForClonedField( 'CMB_Color_Picker', function( newT ) {

	// Reinitialize colorpickers
    newT.find('.wp-color-result').remove();
	newT.find('input:text.cmb_colorpicker').wpColorPicker();

} );


/**
 * Date & Time Fields
 */

CMB.addCallbackForClonedField( ['CMB_Date_Field', 'CMB_Time_Field', 'CMB_Date_Timestamp_Field', 'CMB_Datetime_Timestamp_Field' ], function( newT ) {

	// Reinitialize all the datepickers
	newT.find( '.cmb_datepicker' ).each(function () {
		jQuery(this).attr( 'id', '' ).removeClass( 'hasDatepicker' ).removeData( 'datepicker' ).unbind().datepicker();
	});

	// Reinitialize all the timepickers.
	newT.find('.cmb_timepicker' ).each(function () {
		jQuery(this).timePicker({
			startTime: "07:00",
			endTime: "22:00",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	});

} );

CMB.addCallbackForInit( function() {

	// Datepicker
	jQuery('.cmb_datepicker').each(function () {
		jQuery(this).datepicker();
	});
	
	// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
	jQuery("#ui-datepicker-div").wrap('<div class="cmb_element" />');

	// Timepicker
	jQuery('.cmb_timepicker').each(function () {
		jQuery(this).timePicker({
			startTime: "07:00",
			endTime: "22:00",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	} );

});
