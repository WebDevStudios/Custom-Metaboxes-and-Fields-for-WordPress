/**
 * Custom jQuery for Custom Metaboxes and Fields
 */

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global jQuery, tb_show, tb_remove */

'use strict';

var CMB = {

	_initCallbacks: [],
	_clonedFieldCallbacks: [],
	_deletedFieldCallbacks: [],

	init : function() {
			
		var _this = this;

		jQuery(document).ready( function () {
				
			jQuery( document ).on( 'click', '.delete-field', function(e) { _this.deleteField.call( _this, e, this ); } );
			
			jQuery( document ).on( 'click', '.repeat-field', function(e) { _this.repeatField.call( _this, e, this ); } );

			_this.doneInit();
			
		} );

	},

	repeatField : function( e, btn ) {
			
		e.preventDefault();
		
	    var _this, btn, newT, field, index, attr;

	    _this = this;
	    
	    newT = jQuery(btn).prev().clone();
	    newT.removeClass('hidden');
	    newT.find('input[type!="button"]').not('[readonly]').val('');
	    newT.find( '.cmb_upload_status' ).html('');
	    newT.insertBefore( jQuery(btn).prev() );

	    // Recalculate group ids & update the name fields..
		index = 0;
		field = jQuery(btn).closest('.field' );
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

	    _this.clonedField( newT )

	},

	deleteField : function( e, btn ) {
		
		e.preventDefault();
	
		var fieldItem = jQuery(btn).closest( '.field-item' );

		this.deletedField( fieldItem );	

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
		el.add( el.find( 'div[data-class]' ) ).each( function( i, el ) {

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
				CMB._deletedFieldCallbacks( fieldName[i], callback );
	
		this._deletedFieldCallbacks[fieldName] = this._deletedFieldCallbacks[fieldName] ? this._deletedFieldCallbacks[fieldName] : []
		this._deletedFieldCallbacks[fieldName].push( callback )
	
	},

	/**
	 * Fire deletedField callbacks. 
	 * Called when a field has been cloned.
	 */
	deletedField: function( el ) {

		var _this = this;
		
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