<?php
/**
 * Include and setup custom metaboxes and fields.
 *
 * @category YourThemeOrPlugin
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

add_filter( 'cmb_meta_boxes', 'cmb_sample_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function cmb_sample_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_cmb_';

	$meta_boxes[] = array(
		'id'         => 'test_metabox',
		'title'      => __( 'Test Metabox', 'cmb' ),
		'pages'      => array( 'page', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => __( 'Test Text', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_text',
				'type' => 'text',
			),
			array(
				'name' => __( 'Test Text Small', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textsmall',
				'type' => 'text_small',
			),
			array(
				'name' => __( 'Test Text Medium', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textmedium',
				'type' => 'text_medium',
			),
			array(
				'name' => __( 'Test Date Picker', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textdate',
				'type' => 'text_date',
			),
			array(
				'name' => __( 'Test Date Picker (UNIX timestamp)', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textdate_timestamp',
				'type' => 'text_date_timestamp',
			),
			array(
				'name' => __( 'Test Date/Time Picker Combo (UNIX timestamp)', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_datetime_timestamp',
				'type' => 'text_datetime_timestamp',
			),
			array(
	            'name' => __( 'Test Time', 'cmb' ),
	            'desc' => __( 'field description (optional)', 'cmb' ),
	            'id'   => $prefix . 'test_time',
	            'type' => 'text_time',
	        ),
			array(
				'name'   => __( 'Test Money', 'cmb' ),
				'desc'   => __( 'field description (optional)', 'cmb' ),
				'id'     => $prefix . 'test_textmoney',
				'type'   => 'text_money',
				// 'before' => '£', // override '$' symbol if needed
			),
			array(
	            'name' => __( 'Test Color Picker', 'cmb' ),
	            'desc' => __( 'field description (optional)', 'cmb' ),
	            'id'   => $prefix . 'test_colorpicker',
	            'type' => 'colorpicker',
				'std'  => '#ffffff'
	        ),
			array(
				'name' => __( 'Test Text Area', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textarea',
				'type' => 'textarea',
			),
			array(
				'name' => __( 'Test Text Area Small', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textareasmall',
				'type' => 'textarea_small',
			),
			array(
				'name' => __( 'Test Text Area Code', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textarea_code',
				'type' => 'textarea_code',
			),
			array(
				'name' => __( 'Test Title Weeeee', 'cmb' ),
				'desc' => __( 'This is a title description', 'cmb' ),
				'id'   => $prefix . 'test_title',
				'type' => 'title',
			),
			array(
				'name'    => __( 'Test Select', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_select',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __( 'Option One', 'cmb' ), 'value' => 'standard', ),
					array( 'name' => __( 'Option Two', 'cmb' ), 'value' => 'custom', ),
					array( 'name' => __( 'Option Three', 'cmb' ), 'value' => 'none', ),
				),
			),
			array(
				'name'    => __( 'Test Radio inline', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_radio_inline',
				'type'    => 'radio_inline',
				'options' => array(
					array( 'name' => __( 'Option One', 'cmb' ), 'value' => 'standard', ),
					array( 'name' => __( 'Option Two', 'cmb' ), 'value' => 'custom', ),
					array( 'name' => __( 'Option Three', 'cmb' ), 'value' => 'none', ),
				),
			),
			array(
				'name'    => __( 'Test Radio', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_radio',
				'type'    => 'radio',
				'options' => array(
					array( 'name' => __( 'Option One', 'cmb' ), 'value' => 'standard', ),
					array( 'name' => __( 'Option Two', 'cmb' ), 'value' => 'custom', ),
					array( 'name' => __( 'Option Three', 'cmb' ), 'value' => 'none', ),
				),
			),
			array(
				'name'     => __( 'Test Taxonomy Radio', 'cmb' ),
				'desc'     => __( 'Description Goes Here', 'cmb' ),
				'id'       => $prefix . 'text_taxonomy_radio',
				'type'     => 'taxonomy_radio',
				'taxonomy' => '', // Taxonomy Slug
			),
			array(
				'name'     => __( 'Test Taxonomy Select', 'cmb' ),
				'desc'     => __( 'Description Goes Here', 'cmb' ),
				'id'       => $prefix . 'text_taxonomy_select',
				'type'     => 'taxonomy_select',
				'taxonomy' => '', // Taxonomy Slug
			),
			array(
				'name'		=> __( 'Test Taxonomy Multi Checkbox', 'cmb' ),
				'desc'		=> __( 'field description (optional)', 'cmb' ),
				'id'		=> $prefix . 'test_multitaxonomy',
				'type'		=> 'taxonomy_multicheck',
				'taxonomy'	=> '', // Taxonomy Slug
			),
			array(
				'name' => __( 'Test Checkbox', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_checkbox',
				'type' => 'checkbox',
			),
			array(
				'name'    => __( 'Test Multi Checkbox', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_multicheckbox',
				'type'    => 'multicheck',
				'options' => array(
					'check1' => __( 'Check One', 'cmb' ),
					'check2' => __( 'Check Two', 'cmb' ),
					'check3' => __( 'Check Three', 'cmb' ),
				),
			),
			array(
				'name'    => __( 'Test wysiwyg', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_wysiwyg',
				'type'    => 'wysiwyg',
				'options' => array(	'textarea_rows' => 5, ),
			),
			array(
				'name' => __( 'Test Image', 'cmb' ),
				'desc' => __( 'Upload an image or enter an URL.', 'cmb' ),
				'id'   => $prefix . 'test_image',
				'type' => 'file',
			),
			array(
				'name' => __( 'oEmbed', 'cmb' ),
				'desc' => __( 'Enter a youtube, twitter, or instagram URL. Supports services listed at <a href="http://codex.wordpress.org/Embeds">http://codex.wordpress.org/Embeds</a>.', 'cmb' ),
				'id'   => $prefix . 'test_embed',
				'type' => 'oembed',
			),
		),
	);

	$meta_boxes[] = array(
		'id'         => __( 'about_page_metabox', 'cmb' ),
		'title'      => __( 'About Page Metabox', 'cmb' ),
		'pages'      => array( 'page', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'show_on'    => array( 'key' => 'id', 'value' => array( 2, ), ), // Specific post IDs to display this metabox
		'fields' => array(
			array(
				'name' => __( 'Test Text', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_text',
				'type' => 'text',
			),
		)
	);

	// Add other metaboxes as needed

	return $meta_boxes;
}

add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );
/**
 * Initialize the metabox class.
 */
function cmb_initialize_cmb_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once 'init.php';

}
