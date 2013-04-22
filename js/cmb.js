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
	
	_initCallbacks: [],

	addCallbackForInit: function( callback ) {

		this._initCallbacks.push( callback )
	
	},

	init: function() {

		var _this = this,
			callbacks = _this._initCallbacks;
		
		if ( callbacks ) {
			for ( var a = 0; a < callbacks.length; a++) {
				callbacks[a]();
			}
		}

	},
	
	_clonedFieldCallbacks: [],
	
	addCallbackForClonedField: function( fieldName, callback ) {
		
		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				CMB.addCallbackForClonedField( fieldName[i], callback );

		this._clonedFieldCallbacks[fieldName] = this._clonedFieldCallbacks[fieldName] ? this._clonedFieldCallbacks[fieldName] : []
		this._clonedFieldCallbacks[fieldName].push( callback )
	
	},
	
	clonedField: function( el ) {

		var _this = this
		
		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function(i, el) {

			el = jQuery( el )
			var callbacks = _this._clonedFieldCallbacks[el.attr( 'data-class') ]
		
			if ( callbacks )
				for ( var a = 0; a < callbacks.length; a++ )
					callbacks[a]( el );

		})
	},

	_deletedFieldCallbacks: [],

	addCallbackForDeletedField: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				CMB.addCallbackForClonedField( fieldName[i], callback );
	
		this._deletedFieldCallbacks[fieldName] = this._deletedFieldCallbacks[fieldName] ? this._deletedFieldCallbacks[fieldName] : []
		this._deletedFieldCallbacks[fieldName].push( callback )
	
	},

	deletedField: function( el ) {

		var _this = this
		
		// also check child elements
		el.add( el.find( 'div[data-class]' ) ).each( function(i, el) {
		
			el = jQuery( el )
			var callbacks = _this._deletedFieldCallbacks[el.attr( 'data-class') ]
		
			if ( callbacks )
				for ( var a = 0; a < callbacks.length; a++ )
					callbacks[a]( el )
				
		})
	}

}

jQuery(document).ready(function ($) {

	'use strict';

	var formfield;
	var formfieldobj;

	CMB.init();

	jQuery( document ).on( 'click', '.delete-field', function( e ) {

		e.preventDefault();

		var fieldItem = jQuery(this).closest( '.field-item' );

		CMB.deletedField( fieldItem );	

		fieldItem.remove();

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
		var attrs = ['id','name','for','data-id','data-name'];	
		
		field.children('.field-item').not('.hidden').each( function() {

			var search  = field.hasClass( 'CMB_Group_Field' ) ? /cmb-group-(\d|x)*/g : /cmb-field-(\d|x)*/g;
			var replace = field.hasClass( 'CMB_Group_Field' ) ? 'cmb-group-' + index : 'cmb-field-' + index;

			$(this).find( '[' + attrs.join('],[') + ']' ).each( function() {

				for ( var i = 0; i < attrs.length; i++ )
					if ( typeof( $(this).attr( attrs[i] ) ) !== 'undefined' )
						$(this).attr( attrs[i], $(this).attr( attrs[i] ).replace( search, replace ) );
				
			} );

			index += 1;

		} );

	    CMB.clonedField( newT )

	} );

});
