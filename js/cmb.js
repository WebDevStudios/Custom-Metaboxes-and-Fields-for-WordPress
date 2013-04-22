/**
 * Custom jQuery for Custom Metaboxes and Fields
 */

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global jQuery, tb_show, tb_remove */

var CMB = {

	_initCallbacks: [],
	_clonedFieldCallbacks: [],
	_deletedFieldCallbacks: [],

	init : function() {
	
		'use strict';
		
		var _this = this;

		jQuery(document).ready( function ($) {

			jQuery( document ).on( 'click', '.delete-field', _this.deleteField );
			jQuery( document ).on( 'click', '.repeat-field', _this.repeatField );

			_this.doneInit();
			
		} );

	},

	repeatField : function( e ) {
		
		e.preventDefault();

	    var btn, newT, field, index, attr;

	    btn  = jQuery(this);
	    newT = btn.prev().clone();

	    newT.removeClass('hidden');
	    newT.find('input[type!="button"]').not('[readonly]').val('');
	    newT.find( '.cmb_upload_status' ).html('');
	    newT.insertBefore( btn.prev() );

	    // Recalculate group ids & update the name fields..
		index = 0;
		field = jQuery(this).closest('.field' );
		attr  = ['id','name','for','data-id','data-name'];	
		
		field.children('.field-item').not('.hidden').each( function() {

			var search  = field.hasClass( 'CMB_Group_Field' ) ? /cmb-group-(\d|x)*/g : /cmb-field-(\d|x)*/g;
			var replace = field.hasClass( 'CMB_Group_Field' ) ? 'cmb-group-' + index : 'cmb-field-' + index;

			jQuery(this).find( '[' + attr.join('],[') + ']' ).each( function() {

				for ( var i = 0; i < attr.length; i++ )
					if ( typeof( jQuery(this).attr( attr[i] ) ) !== 'undefined' )
						jQuery(this).attr( attr[i], jQuery(this).attr( attr[i] ).replace( search, replace ) );
				
			} );

			index += 1;

		} );

		// TODO, can we pass _this, instead of using CMB?
	    CMB.clonedField( newT )

	},

	deleteField : function( e ) {
		
		e.preventDefault();
		
		var fieldItem = jQuery(this).closest( '.field-item' );

		// TODO, can we pass _this, instead of using CMB?
		CMB.deletedField( fieldItem );	

		fieldItem.remove();

	},	

	addCallbackForInit: function( callback ) {

		this._initCallbacks.push( callback )
	
	},

	/**
	 * Fire init callbacks. 
	 * Called when CMB has been set up.
	 */
	doneInit: function() {

		var _this = this,
			callbacks = _this._initCallbacks;
		
		if ( callbacks ) {
			for ( var a = 0; a < callbacks.length; a++) {
				callbacks[a]();
			}
		}

	},
	
	addCallbackForClonedField: function( fieldName, callback ) {
		
		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				CMB.addCallbackForClonedField( fieldName[i], callback );

		this._clonedFieldCallbacks[fieldName] = this._clonedFieldCallbacks[fieldName] ? this._clonedFieldCallbacks[fieldName] : []
		this._clonedFieldCallbacks[fieldName].push( callback )
	
	},
	
	/**
	 * Fire clonedField callbacks. 
	 * Called when a field has been cloned.
	 */
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

	addCallbackForDeletedField: function( fieldName, callback ) {

		if ( jQuery.isArray( fieldName ) )
			for ( var i = 0; i < fieldName.length; i++ )
				CMB.addCallbackForClonedField( fieldName[i], callback );
	
		this._deletedFieldCallbacks[fieldName] = this._deletedFieldCallbacks[fieldName] ? this._deletedFieldCallbacks[fieldName] : []
		this._deletedFieldCallbacks[fieldName].push( callback )
	
	},

	/**
	 * Fire deletedField callbacks. 
	 * Called when a field has been cloned.
	 */
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

CMB.init();