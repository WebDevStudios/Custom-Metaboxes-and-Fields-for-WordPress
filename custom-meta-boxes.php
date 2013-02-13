<?php
/*
Script Name: 	Custom Metaboxes and Fields
Contributors: 	Andrew Norcross (@norcross / andrewnorcross.com)
				Jared Atchison (@jaredatch / jaredatchison.com)
				Bill Erickson (@billerickson / billerickson.net)
				Human Made Limited (@humanmadeltd)
Description: 	This will create metaboxes with custom fields that will blow your mind.
Version: 		1.0 - Beta 1
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
define( 'CMB_PATH', str_replace( '\\', '/', dirname( __FILE__ ) ) );
define( 'CMB_URL', str_replace( str_replace( '\\', '/', WP_CONTENT_DIR ), str_replace( '\\', '/', WP_CONTENT_URL ), CMB_PATH ) );

include_once( CMB_PATH . '/classes.fields.php' );
include_once( CMB_PATH . '/class.cmb-meta-box.php' );
//include_once( CMB_PATH . '/example-functions.php' );

// Get all the meta boxes on init
function cmb_init() {

	if ( ! is_admin() )
		return;

	$meta_boxes = apply_filters( 'cmb_meta_boxes', array() );

	if ( ! empty( $meta_boxes ) )
		foreach ( $meta_boxes as $meta_box )
			new CMB_Meta_Box( $meta_box );

}
add_action( 'init', 'cmb_init' );

/**
 * Adding scripts and styles
 */
function cmb_scripts( $hook ) {

	if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' || did_action( 'cmb_init_fields' ) ) {
		wp_register_script( 'cmb-timepicker', CMB_URL . '/js/jquery.timePicker.min.js' );
		wp_register_script( 'cmb-scripts', CMB_URL . '/js/cmb.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'media-upload', 'thickbox', 'farbtastic' ) );
		wp_enqueue_script( 'cmb-timepicker' );
		wp_enqueue_script( 'cmb-scripts' );
		wp_register_style( 'cmb-styles', CMB_URL . '/style.css', array( 'thickbox', 'farbtastic' ) );
		wp_enqueue_style( 'cmb-styles' );
	}
}
add_action( 'admin_enqueue_scripts', 'cmb_scripts', 10 );

function _cmb_field_class_for_type( $type ) {

	$map = apply_filters( 'cmb_field_types', array(

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
		'textarea_code'		=> 'CMB_Textarea_Field_Code',
		'taxonomy_select'	=> 'CMB_Taxonomy',
		'select'			=> 'CMB_Select',
		'wysiwyg'			=> 'CMB_wysiwyg',
		'checkbox'			=> 'CMB_Checkbox',
		'post_select'		=> 'CMB_Post_Select',
		'radio'				=> 'CMB_Radio_Field'
	) );

	if ( isset( $map[$type] ) )
		return $map[$type];

	return $type;

}

// Draw the meta boxes in places other than the post edit screen
function cmb_draw_meta_boxes( $pages, $context = 'normal', $object = null ) {

	cmb_do_meta_boxes( $pages, $context, $object );

	wp_enqueue_script('post');

}

/**
 * Meta-Box template function
 *
 * @since 2.5.0
 *
 * @param string|object $screen Screen identifier
 * @param string $context box context
 * @param mixed $object gets passed to the box callback function as first parameter
 * @return int number of meta_boxes
 */
function cmb_do_meta_boxes( $screen, $context, $object ) {

	global $wp_meta_boxes;

	static $already_sorted = false;

	if ( empty( $screen ) )
		$screen = get_current_screen();

	elseif ( is_string( $screen ) )
		$screen = convert_to_screen( $screen );

	$page = $screen->id;

	$hidden = get_hidden_meta_boxes( $screen );

	$i = 0;

	do {
		// Grab the ones the user has manually sorted. Pull them out of their previous context/priority and into the one the user chose

		if ( ! $already_sorted && $sorted = get_user_option( "meta-box-order_$page" ) )
			foreach ( $sorted as $box_context => $ids )
				foreach ( explode(',', $ids ) as $id )
					if ( $id && 'dashboard_browser_nag' !== $id )
						add_meta_box( $id, null, null, $screen, $box_context, 'sorted' );

		$already_sorted = true;

		if ( ! isset( $wp_meta_boxes ) || ! isset( $wp_meta_boxes[$page] ) || ! isset( $wp_meta_boxes[$page][$context] ) )
			break;

		foreach ( array( 'high', 'sorted', 'core', 'default', 'low' ) as $priority ) {

			if ( isset( $wp_meta_boxes[$page][$context][$priority] ) ) {

				foreach ( (array) $wp_meta_boxes[$page][$context][$priority] as $box ) {

					if ( false == $box || ! $box['title'] )
						continue;

					$i++;

					$hidden_class = in_array($box['id'], $hidden) ? ' hide-if-js' : ''; ?>

					<div id="<?php esc_attr_e( $box['id'] ); ?>" class="<?php esc_attr_e( postbox_classes( $box['id'], $page ) . $hidden_class ); ?>">

						<?php call_user_func( $box['callback'], $object, $box ); ?>

					</div>

				<?php }

			}

		}
	} while( 0 );

	return $i;

}