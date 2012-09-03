<?php
/*
Script Name: 	Custom Metaboxes and Fields
Contributors: 	Andrew Norcross (@norcross / andrewnorcross.com)
				Jared Atchison (@jaredatch / jaredatchison.com)
				Bill Erickson (@billerickson / billerickson.net)
				Human Made Limited (@humanmadeltd)
Description: 	This will create metaboxes with custom fields that will blow your mind.
Version: 		1.o
*/

/**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * Defines the url to which is used to load local resources.
 * This may need to be filtered for local Window installations.
 * If resources do not load, please check the wiki for details.
 */
define( 'CMB_PATH', dirname( __FILE__ ) );
define( 'CMB_URL', str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, CMB_PATH ) );

include_once( CMB_PATH . '/classes.fields.php' );
include_once( CMB_PATH . '/class.cmb-meta-box.php' );
//include_once( CMB_PATH . '/example-functions.php' );


// get all the meta boxes on init
add_action( 'init', function() {
	
	if ( ! is_admin() )
		return;

	$meta_boxes = apply_filters( 'cmb_meta_boxes', array() );
	
	if ( ! empty( $meta_boxes ) )
		foreach ( $meta_boxes as $meta_box )
			new CMB_Meta_Box( $meta_box );

}, 99 );

/**
 * Adding scripts and styles
 */
function cmb_scripts( $hook ) {
	if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {
		wp_register_script( 'cmb-timepicker', CMB_URL . '/js/jquery.timePicker.min.js' );
		wp_register_script( 'cmb-scripts', CMB_URL . '/js/cmb.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'media-upload', 'thickbox', 'farbtastic' ) );
		wp_enqueue_script( 'cmb-timepicker' );
		wp_enqueue_script( 'cmb-scripts' );
		wp_register_style( 'cmb-styles', CMB_URL . '/style.css', array( 'thickbox', 'farbtastic' ) );
		wp_enqueue_style( 'cmb-styles' );
	}
}
add_action( 'admin_enqueue_scripts', 'cmb_scripts', 10 );

function cmb_editor_footer_scripts() { ?>
	<?php
	if ( isset( $_GET['cmb_force_send'] ) && 'true' == $_GET['cmb_force_send'] ) { 
		$label = $_GET['cmb_send_label']; 
		if ( empty( $label ) ) $label="Select File";
		?>	
		<script type="text/javascript">
		jQuery(function($) {
			$('td.savesend input').val('<?php echo $label; ?>');
		});
		</script>
		<?php 
	}
}
add_action( 'admin_print_footer_scripts', 'cmb_editor_footer_scripts', 99 );

// Force 'Insert into Post' button from Media Library 
add_filter( 'get_media_item_args', 'cmb_force_send' );
function cmb_force_send( $args ) {
		
	// if the Gallery tab is opened from a custom meta box field, add Insert Into Post button	
	if ( isset( $_GET['cmb_force_send'] ) && 'true' == $_GET['cmb_force_send'] )
		$args['send'] = true;
	
	// if the From Computer tab is opened AT ALL, add Insert Into Post button after an image is uploaded	
	if ( isset( $_POST['attachment_id'] ) && '' != $_POST["attachment_id"] ) {
		
		$args['send'] = true;		

		// TO DO: Are there any conditions in which we don't want the Insert Into Post 
		// button added? For example, if a post type supports thumbnails, does not support
		// the editor, and does not have any cmb file inputs? If so, here's the first
		// bits of code needed to check all that.
		// $attachment_ancestors = get_post_ancestors( $_POST["attachment_id"] );
		// $attachment_parent_post_type = get_post_type( $attachment_ancestors[0] );
		// $post_type_object = get_post_type_object( $attachment_parent_post_type );
	}		
	
	// change the label of the button on the From Computer tab
	if ( isset( $_POST['attachment_id'] ) && '' != $_POST["attachment_id"] ) {

		echo '
			<script type="text/javascript">
				function cmbGetParameterByNameInline(name) {
					name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
					var regexS = "[\\?&]" + name + "=([^&#]*)";
					var regex = new RegExp(regexS);
					var results = regex.exec(window.location.href);
					if(results == null)
						return "";
					else
						return decodeURIComponent(results[1].replace(/\+/g, " "));
				}
							
				jQuery(function($) {
					if (cmbGetParameterByNameInline("cmb_force_send")=="true") {
						var cmb_send_label = cmbGetParameterByNameInline("cmb_send_label");
						$("td.savesend input").val(cmb_send_label);
					}
				});
			</script>
		';
	}
	 
	return $args;

}

function _cmb_field_class_for_type( $type ) {

	$map = array(
	
		'text'				=> 'CMB_Text_Field',
		'text_small' 		=> 'CMB_Text_Small_Field',
		'text_url'			=> 'CMB_URL_Field',
		'url'				=> 'CMB_URL_Field',
		'file'				=> 'CMB_File_Field',
		'image' 			=> 'CMB_Image_Field',
		'group'				=> 'CMB_Group_Field',
		'oembed'			=> 'CMB_Oembed_Field',
		'date'				=> 'CMB_Date_Field', 
		'date_unix'			=> 'CMB_Date_Timestamp_Field',
		'datetime_unix'		=> 'CMB_Datetime_Timestamp_Field',
		'time'				=> 'CMB_Time_Field',
		'textarea'			=> 'CMB_Textarea_Field',
		'taxonomy_select'	=> 'CMB_Taxonomy',
		'select'			=> 'CMB_Select',
		'wysiwyg'			=> 'CMB_wysiwyg',
		'checkbox'			=> 'CMB_Checkbox'
	);
	
	if ( isset( $map[$type] ) )
		return $map[$type];
	
	return null;

}
