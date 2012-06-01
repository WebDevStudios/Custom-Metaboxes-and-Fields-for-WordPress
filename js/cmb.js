/**
 * Controls the behaviours of custom metabox fields.
 *
 * @author Andrew Norcross
 * @author Jared Atchison
 * @author Bill Erickson
 * @see    https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

/**
 * CMB needs its own instance of a send to editor function to prevent conflicting with all Woo Themes
 * @see wp-admin/includes/media-upload.dev.js
 */
var CMBActiveEditor;
function CMB_send_to_editor(h) {
    'use strict';
	var ed, mce = typeof (tinymce) !== 'undefined', qt = typeof (QTags) !== 'undefined';

	if (!CMBActiveEditor) {
		if (mce && tinymce.activeEditor) {
			ed = tinymce.activeEditor;
			CMBActiveEditor = ed.id;
		} else if (!qt) {
			return false;
		}
	} else if (mce) {
		if (tinymce.activeEditor && (tinymce.activeEditor.id === 'mce_fullscreen' || tinymce.activeEditor.id === 'wp_mce_fullscreen')) {
            ed = tinymce.activeEditor;
        } else {
		    ed = tinymce.get(CMBActiveEditor);
        }
	}

	if (ed && !ed.isHidden()) {
		// restore caret position on IE
		if (tinymce.isIE && ed.windowManager.insertimagebookmark)
		ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);

		if (h.indexOf('[caption') === 0) {
			if (ed.wpSetImgCaption)
				h = ed.wpSetImgCaption(h);
		} else if ( h.indexOf('[gallery') === 0) {
			if (ed.plugins.wpgallery)
				h = ed.plugins.wpgallery._do_gallery(h);
		} else if (h.indexOf('[embed') === 0) {
			if (ed.plugins.wordpress)
				h = ed.plugins.wordpress._setEmbed(h);
		}

		ed.execCommand('mceInsertContent', false, h);
	} else if (qt) {
		QTags.insertContent(h);
	} else {
		document.getElementById(CMBActiveEditor).value += h;
	}

	try { tb_remove(); } catch (e) {};
}
/**
 * Defines the CMBMeta object namespace to prevent conflict with other plugins using similar javascript
 *
 */
(function ($) {
    'use strict';

    var CMBMeta;
    CMBMeta = {

        /**
         * Initialize timepicker (this will be moved inline in a future release)
         */
        TimePicker: function () {

            $('.cmb_timepicker').each(function () {
                $('#' + $(this).attr('id')).timePicker({
                    startTime: "07:00",
                    endTime: "22:00",
                    show24Hours: false,
                    separator: ':',
                    step: 30
                });
            });

        },

        /**
         * Initialize jQuery UI datepicker (this will be moved inline in a future release)
         */
        DatePicker: function () {

            $('.cmb_datepicker').each(function () {
                $('#' + $(this).attr('id')).datepicker();
                // $('#' + jQuery(this).attr('id')).datepicker({ dateFormat: 'yy-mm-dd' });
                // For more options see http://jqueryui.com/demos/datepicker/#option-dateFormat
            });
            // Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
            $("#ui-datepicker-div").wrap('<div class="cmb_element" />');

        }, // End DatePicker

        /**
         * Initialize color picker
         */
        ColorPicker: function () {

            $('input:text.cmb_colorpicker').each(function (i) {
                $(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
                $('#picker-' + i).hide().farbtastic($(this));

                $(this).focus(function () {
                    $(this).next().show();
                })
                        .blur(function () {
                        $(this).next().hide();
                    });
            });

        }, // End ColorPicker

        /**
         * File and image upload handling
         */
        FileUpload: function () {
            var divID = $(".file-div").attr("id");
            jQuery.noConflict();

            var itemurl, itemclass, itemClassBits, itemid, htmlBits, itemtitle,
                image, formfield, uploadStatus = true;


            $('.cmb_upload_file').change(function () {
                formfield = $(this).attr('name');
                $('#' + formfield + '_id').val("");

            });

            $("#" + divID).delegate('.cmb_upload_button', 'click', function () {
                var buttonLabel;
                formfield = $(this).prev('input').attr('name');
                console.log(formfield);
                buttonLabel = 'Use as ' + $('label[for=' + formfield + ']').text();
                tb_show('', 'media-upload.php?post_id=' + $('#post_ID').val() + '&type=file&cmb_force_send=true&cmb_send_label=' + buttonLabel + '&TB_iframe=true');
                return false;
            });

            $("#" + divID).delegate('.cmb_remove_file_button', 'click', function () {
                formfield = $(this).attr('rel');
                $('input#' + formfield).val('');
                $('input#' + formfield + '_id').val('');
                $(this).parent().remove();
                return false;
            });

            window.original_cmb_to_editor = window.CMB_send_to_editor;
            window.CMB_send_to_editor = function (html) {
                if (!formfield) {
                    return original_cmb_to_editor(html); // window.original_send_to_editor(html);
                } else {

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

                }
                formfield = '';
                tb_remove();
                return false;
            };

        }  // End FileUpload

    }; //End CMBMeta object

    $(document).ready(function () {

        CMBMeta.TimePicker();
        CMBMeta.DatePicker();
        CMBMeta.ColorPicker();
        CMBMeta.FileUpload();
    });

}(jQuery));