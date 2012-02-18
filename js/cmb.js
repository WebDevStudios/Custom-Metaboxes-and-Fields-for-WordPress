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

	/**
	 * Initialize timepicker (this will be moved inline in a future release)
	 */
	$('.cmb_timepicker').each(function () {
		$('#' + jQuery(this).attr('id')).timePicker({
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
		$('#' + jQuery(this).attr('id')).datepicker();
		// $('#' + jQuery(this).attr('id')).datepicker({ dateFormat: 'yy-mm-dd' });
		// For more options see http://jqueryui.com/demos/datepicker/#option-dateFormat
	});
	// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
	$("#ui-datepicker-div").wrap('<div class="cmb_element" />');

	/**
	 * Initialize color picker
	 */
    $('input:text.cmb_colorpicker').each(function (i) {
        $(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
        $('#picker-' + i).hide().farbtastic($(this));
    })
    .focus(function() {
        $(this).next().show();
    })
    .blur(function() {
        $(this).next().hide();
    });

	/**
	 * File and image upload handling
	 */
	$('.cmb_upload_file').change(function () {
		formfield = $(this).attr('name');
		$('#' + formfield + '_id').val("");
	});

	$('.cmb_upload_button').live('click', function () {
		var buttonLabel;
		formfield = $(this).prev('input').attr('name');
		buttonLabel = 'Use as ' + $('label[for=' + formfield + ']').text();
		tb_show('', 'media-upload.php?post_id=' + $('#post_ID').val() + '&type=file&cmb_force_send=true&cmb_send_label=' + buttonLabel + '&TB_iframe=true');
		return false;
	});

	$('.cmb_remove_file_button').live('click', function () {
		formfield = $(this).attr('rel');
		$('input#' + formfield).val('');
		$('input#' + formfield + '_id').val('');
		$(this).parent().remove();
		return false;
	});

	window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function (html) {
		var itemurl, itemclass, itemClassBits, itemid, htmlBits, itemtitle,
			image, uploadStatus = true;

		if (formfield) {

	        if ($(html).html(html).find('img').length > 0) {
				itemurl = $(html).html(html).find('img').attr('src'); // Use the URL to the size selected.
				itemclass = $(html).html(html).find('img').attr('class'); // Extract the ID from the returned class name.
				itemClassBits = itemclass.split(" ");
				itemid = itemClassBits[itemClassBits.length - 1];
				itemid = itemid.replace('wp-image-', '');
	        } else {
				// It's not an image. Get the URL to the file instead.
				htmlBits = html.split("'"); // jQuery seems to strip out XHTML when assigning the string to an object. Use alternate method.
				itemurl = htmlBits[1]; // Use the URL to the file.
				itemtitle = htmlBits[2];
				itemtitle = itemtitle.replace('>', '');
				itemtitle = itemtitle.replace('</a>', '');
				itemid = ""; // TO DO: Get ID for non-image attachments.
			}

			image = /(jpe?g|png|gif|ico)$/gi;

			if (itemurl.match(image)) {
				uploadStatus = '<div class="img_status"><img src="' + itemurl + '" alt="" /><a href="#" class="cmb_remove_file_button" rel="' + formfield + '">Remove Image</a></div>';
			} else {
				// No output preview if it's not an image
				// Standard generic output if it's not an image.
				html = '<a href="' + itemurl + '" target="_blank" rel="external">View File</a>';
				uploadStatus = '<div class="no_image"><span class="file_link">' + html + '</span>&nbsp;&nbsp;&nbsp;<a href="#" class="cmb_remove_file_button" rel="' + formfield + '">Remove</a></div>';
			}

			$('#' + formfield).val(itemurl);
			$('#' + formfield + '_id').val(itemid);
			$('#' + formfield).siblings('.cmb_upload_status').slideDown().html(uploadStatus);
			tb_remove();

		} else {
			window.original_send_to_editor(html);
		}

		formfield = '';
	};

	/**
	 * Repeatable fieldsets
	 *
	 * Makes a fieldset repeatable, handles id's and for attributes.
	 * Strips value attributes and duplicate descriptoin text.
	 *
	 * @todo repeatable tinymce
	 */
	if ( $( '.cmb_repeatable' ).size() ) {

		var removeButton = '<button type="button" class="button cmb_remove_repeated_fieldset">Remove Fieldset</button>';

		// Insert the new fieldset button
		$( '.cmb_repeatable' ).closest( '.inside' ).append( '<button type="button" class="button cmb_repeat_fieldset">New FieldSet</button>' );

		$( '.cmb_repeatable, .cmb_repeated' ).each( function() {

			// Append [] to name so fields are stored as arrays
			$( this ).find( '[name]' ).each( function() {
				$( this ).attr( 'name', $( this ).attr( 'name' ) + '[]' );
			} );

		} );

		$( '.cmb_repeatable' ).each( function() {

			// Store the original fieldset in data
			$( this ).data( 'fieldSet', $( $( this ).clone() ).find( 'input' ).val( '' ).end() );

		} );

		$( '.cmb_repeated' ).after( removeButton );

		// Setup the add fieldset button click handler
		$( document ).on( 'click', '.cmb_repeat_fieldset', function( e ) {

		    var fieldSetCount = $( this ).closest( '.inside' ).find( 'table' ).size();

		    $( this ).before( $( $( this ).closest( '.inside' ).find( '.cmb_repeatable' ).data( 'fieldSet' ) ).find( '[id], [for]' ).each( function() {

		    	if ( $( this ).attr( 'id' ) )
		    		$( this ).attr( 'id', $( this ).attr( 'id' ) + '_' + Number( fieldSetCount ) );

		    	if ( $( this ).attr( 'for' ) )
		    		$( this ).attr( 'for', $( this ).attr( 'for' ) + '_' + Number( fieldSetCount ) );

		    } ).end().find( '.cmb_metabox_description' ).remove().end().removeClass( 'cmb_repeatable' ).addClass( 'cmb_repeated' ).clone() ).before( removeButton );

		    e.preventDefault();

		} );

		$( document ).on( 'click', '.cmb_remove_repeated_fieldset', function( e ) {

			$( this ).prev( '.cmb_repeated' ).remove().end().remove();

		} );

	}

});