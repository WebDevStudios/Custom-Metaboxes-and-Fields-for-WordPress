<?php

/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function cmb_sample_metaboxes( array $meta_boxes ) {

	$meta_boxes[] = array(
		'title' => 'Test Meta Box',
		'pages' => 'post',
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(

			array( 'id' => 'input', 'name' => 'A Normal text input', 'type' => 'text', 'cols' => 12, 'readonly' => true ),
			array( 'id' => 'input2', 'name' => 'Test Repeatable Field', 'type' => 'text', 'cols' => 4, 'repeatable' => true ),
			array( 'id' => 'input3', 'name' => 'URL Text Field', 'type' => 'url', 'cols' => 8 ),
			array( 'id' => 'input4', 'name' => 'File field', 'type' => 'file' ),
			
			array( 'id' => 'input5', 'name' => 'Image', 'type' => 'image' ),
			array( 'id' => 'input6', 'name' => 'Select Category', 'type' => 'taxonomy_select', 'taxonomy' => 'category' ),
			array( 'id' => 'input7', 'name' => 'Checkbox 1', 'type' => 'checkbox' ),

			array( 'id' => 'group-1', 'name' => 'Group of Fields', 'type' => 'group', 'style' => 'background: #f1f1f1; border-radius: 4px; border: 1px solid #e2e2e2; margin-bottom: 10px; padding: 10px', 'repeatable' => false, 'fields' => array(
				array( 'id' => 'group-1-1', 'name' => 'A Normal text input', 'type' => 'text' ),
				array( 'id' => 'group-1-2', 'name' => 'Image', 'type' => 'image', 'cols' => 3, 'size' => 'width=200&height=120' ),
				array( 'id' => 'group-1-3', 'name' => 'Select Category', 'type' => 'taxonomy_select', 'cols' => 5, 'taxonomy' => 'category' ),
				array( 'id' => 'group-1-4', 'name' => 'Checkbox 1', 'type' => 'checkbox', 'cols' => 2, 'style' => 'margin-top: 35px; margin-left: 20px;' ),
				array( 'id' => 'group-1-5', 'name' => 'Checkbox 2', 'type' => 'checkbox', 'cols' => 2, 'style' => 'margin-top: 35px' )
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
				'query' => 'showposts=5'
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

	// repeatble groups test
	$meta_boxes[] = array(
		'title' => 'Repeatable Group',
		'pages' => 'post',
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(

			array( 
				'id' => 'simple-group', 
				'name' => 'Simple Repeatable Group', 
				'type' => 'group', 
				'cols' => 12, 
				'repeatable' => true,
				'fields' => array( 
					array(
						'id' => 'simple-group-text-1',
						'name' => 'Text Input 1',
						'type' => 'text'
					),
					array(
						'id' => 'simple-group-text-2',
						'name' => 'Text Input 2',
						'type' => 'text'
					)
				)
			)
		)
	);

	return $meta_boxes;

}
add_filter( 'cmb_meta_boxes', 'cmb_sample_metaboxes' );
