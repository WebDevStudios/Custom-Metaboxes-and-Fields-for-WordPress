/**
 * Controls the behaviours of custom metabox fields.
 *
 * @author Andrew Norcross
 * @author Jared Atchison
 * @author Bill Erickson
 * @see    https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global jQuery, tb_show, tb_remove */

/**
 * Custom jQuery for Custom Metaboxes and Fields
 */
jQuery(document).ready(function ($) {
	'use strict';

	var formfield;
	var formfieldobj;

	jQuery( document ).on( 'click', '.delete-field', function( e ) {

		e.preventDefault();
		var a = jQuery( this );

		a.closest( '.field-item' ).remove();

	} );

	/**
	 * Initialize timepicker (this will be moved inline in a future release)
	 */
	$('.cmb_timepicker').each(function () {
		$( this ).timePicker({
			startTime: "07:00",
			endTime: "22:00",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	});

	/**
	 * Initialize jQuery UI datepicker (this will be moved inline in a future release)
	 */
	$('.cmb_datepicker').each(function () {
		$( this ).datepicker();
		// $('#' + jQuery(this).attr('id')).datepicker({ dateFormat: 'yy-mm-dd' });
		// For more options see http://jqueryui.com/demos/datepicker/#option-dateFormat
	});
	// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
	$("#ui-datepicker-div").wrap('<div class="cmb_element" />');

	/**
	 * Initialize color picker
	 */
	if (typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function') {
		$('input:text.cmb_colorpicker').wpColorPicker();
	} else {
		$('input:text.cmb_colorpicker').each(function (i) {
			$(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
			$('#picker-' + i).hide().farbtastic($(this));
		})
		.focus(function () {
			$(this).next().show();
		})
		.blur(function () {
			$(this).next().hide();
		});
	}

	jQuery( document ).on( 'click', '.repeat-field', function( e ) {

	    e.preventDefault();
	    var el = jQuery( this );

	    var newT = el.prev().clone();

	    //Make a colorpicker field repeatable
	    newT.find('.wp-color-result').remove();
		newT.find('input:text.cmb_colorpicker').wpColorPicker();

	    newT.removeClass('hidden');
	    newT.find('input[type!="button"]').val('');
	    newT.find( '.cmb_upload_status' ).html('');
	    newT.insertBefore( el.prev() );

	    // Reinitialize all the datepickers
		jQuery('.cmb_datepicker' ).each(function () {
			$(this).attr( 'id', '' ).removeClass( 'hasDatepicker' ).removeData( 'datepicker' ).unbind().datepicker();
		});

		// Reinitialize all the timepickers.
		jQuery('.cmb_timepicker' ).each(function () {
			$(this).timePicker({
				startTime: "07:00",
				endTime: "22:00",
				show24Hours: false,
				separator: ':',
				step: 30
			});
		});

	} );

	// Make group sortable
	var textarea_id;
	$('.CMB_textarea_wysiwyg:not(:hidden)').each(function(){
		textarea_id = $(this).find('textarea').attr('id');
		tinyMCE.execCommand('mceAddControl', false, textarea_id);

		//Add toggle to go back to textarea
		$(this).find('.field-title').append("<button class='button togglewysiwyg ui-state-default' data-id='"+textarea_id+"''>⇄</button>");
		$(".togglewysiwyg").toggle(
			function(event){
				tinyMCE.execCommand('mceRemoveControl', false, $(this).data('id'));
			},
			function(){
				tinyMCE.execCommand('mceAddControl', false, $(this).data('id'));
			}
		);
	});

	$('.CMB_Group_Field').sortable({
		cancel: '.mceStatusbar', 
		handle: '.move-field',
		items: "> .field-item",
		start: function(event, ui) { // turn TinyMCE off while sorting (if not, it won't work when resorted)
			textarea_id = $(ui.item).find('.CMB_textarea_wysiwyg textarea').attr('id');
			tinyMCE.execCommand('mceRemoveControl', false, textarea_id);
		},
		stop: function(event, ui) { // re-initialize TinyMCE when sort is completed
			tinyMCE.execCommand('mceAddControl', false, textarea_id);
		}
	});

});
