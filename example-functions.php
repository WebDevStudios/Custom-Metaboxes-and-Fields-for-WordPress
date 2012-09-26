<?php

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
		'title' => 'Test Meta Box',
		'pages' => 'post',
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(

			array( 'id' => 'input', 'name' => 'A Normal text input', 'type' => 'text', 'cols' => 12 ),
			array( 'id' => 'input2', 'name' => 'Test Repeatable Field', 'type' => 'text', 'cols' => 4, 'repeatable' => true ),
			array( 'id' => 'input3', 'name' => 'URL Text Field', 'type' => 'url', 'cols' => 8 ),
			array( 'id' => 'group-1', 'name' => 'Group of Fields (repeatable)', 'type' => 'group', 'style' => 'background: #f1f1f1; border-radius: 4px; border: 1px solid #e2e2e2; margin-bottom: 10px; padding: 10px', 'repeatable' => true, 'fields' => array(
				array( 'id' => 'input3-1', 'name' => 'Image', 'type' => 'image', 'cols' => 4, 'size' => 'width=200&height=120' ),
				array( 'id' => 'input4', 'name' => 'Select Category', 'type' => 'taxonomy_select', 'cols' => 4, 'taxonomy' => 'category' ),
				array( 'id' => 'input22', 'name' => 'Checbox 1', 'type' => 'checkbox', 'cols' => 2, 'style' => 'margin-top: 50px' ),
				array( 'id' => 'input5', 'name' => 'Checbox 2', 'type' => 'checkbox', 'cols' => 2, 'style' => 'margin-top: 50px' )

			) )

		)
	);
	
	// Posts Select meta boxe
	$meta_boxes[] = array(
		'title' => 'Posts Select',
		'pages' => 'post',
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(

			array( 
				'id' => 'select-posts', 
				'name' => 'Single Posts Select', 
				'type' => 'post_select', 
				'cols' => 6, 
				'allow_none' => true, 
				'multiple' => false,
				'query' => 'showposts=1'
			),

			array( 
				'id' => 'select-multiple', 
				'name' => 'Multiple Posts Select', 
				'type' => 'post_select', 
				'cols' => 6, 
				'allow_none' => true, 
				'multiple' => true 
			),

			array( 
				'id' => 'select-posts-ajax', 
				'name' => 'Single Posts Ajax Select', 
				'type' => 'post_select', 
				'cols' => 6, 
				'allow_none' => true, 
				'multiple' => false ,
				'use_ajax' => true
			),

			array( 
				'id' => 'select-multiple-ajax', 
				'name' => 'Mutliple Posts Ajax Select', 
				'type' => 'post_select', 
				'cols' => 6, 
				'allow_none' => true, 
				'multiple' => true,
				'use_ajax' => true
			)
		)
	);

	return $meta_boxes;
}