<?php
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function cmb_sample_metaboxes( array $meta_boxes ) {

	// Example of all available fields
	
	$fields = array(
		
		array( 'id' => 'field-1',  'name' => 'Text input field', 'type' => 'text' ),
		array( 'id' => 'field-2', 'name' => 'Read-only text input field', 'type' => 'text', 'readonly' => true, 'default' => 'READ ONLY' ),
 		array( 'id' => 'field-3', 'name' => 'Repeatable text input field', 'type' => 'text', 'desc' => 'Add up to 5 fields.', 'repeatable' => true, 'repeatable_max' => 5 ),

		array( 'id' => 'field-4',  'name' => 'Small text input field', 'type' => 'text_small' ),
		array( 'id' => 'field-5',  'name' => 'URL field', 'type' => 'url' ),
		
		array( 'id' => 'field-6',  'name' => 'Radio input field', 'type' => 'radio', 'options' => array( 'Option 1', 'Option 2' ) ),
		array( 'id' => 'field-7',  'name' => 'Checkbox field', 'type' => 'checkbox' ),
		
		array( 'id' => 'field-8',  'name' => 'WYSIWYG field', 'type' => 'wysiwyg', 'options' => array( 'editor_height' => '100' ) ),

		array( 'id' => 'field-9',  'name' => 'Textarea field', 'type' => 'textarea' ),
		array( 'id' => 'field-10',  'name' => 'Code textarea field', 'type' => 'textarea_code' ),

		array( 'id' => 'field-11', 'name' => 'File field', 'type' => 'file', 'file_type' => 'image', 'repeatable' => 1, 'sortable' => 1 ),
		array( 'id' => 'field-12', 'name' => 'Image upload field', 'type' => 'image', 'repeatable' => true, 'show_size' => true ),
		
		array( 'id' => 'field-13', 'name' => 'Select field', 'type' => 'select', 'options' => array( 'option-1' => 'Option 1', 'option-2' => 'Option 2', 'option-3' => 'Option 3' ), 'allow_none' => true ),
		array( 'id' => 'field-14', 'name' => 'Select field', 'type' => 'select', 'options' => array( 'option-1' => 'Option 1', 'option-2' => 'Option 2', 'option-3' => 'Option 3' ), 'multiple' => true ),
		array( 'id' => 'field-15', 'name' => 'Select taxonomy field', 'type' => 'taxonomy_select',  'taxonomy' => 'category' ),
		array( 'id' => 'field-15b', 'name' => 'Select taxonomy field', 'type' => 'taxonomy_select',  'taxonomy' => 'category',  'multiple' => true ),
		array( 'id' => 'field-16', 'name' => 'Post select field', 'type' => 'post_select', 'use_ajax' => false, 'query' => array( 'cat' => 1 ) ),	
		array( 'id' => 'field-17', 'name' => 'Post select field (AJAX)', 'type' => 'post_select', 'use_ajax' => true ),
		array( 'id' => 'field-17b', 'name' => 'Post select field (AJAX)', 'type' => 'post_select', 'use_ajax' => true, 'query' => array( 'posts_per_page' => 8 ), 'multiple' => true  ),
		
		array( 'id' => 'field-18', 'name' => 'Date input field', 'type' => 'date' ),
		array( 'id' => 'field-19', 'name' => 'Time input field', 'type' => 'time' ),
		array( 'id' => 'field-20', 'name' => 'Date (unix) input field', 'type' => 'date_unix' ),
		array( 'id' => 'field-21', 'name' => 'Date & Time (unix) input field', 'type' => 'datetime_unix' ),
		
		array( 'id' => 'field-22', 'name' => 'Color', 'type' => 'colorpicker' ),

		array( 'id' => 'field-24', 'name' => 'Title Field', 'type' => 'title' ),
	
	);

	$meta_boxes[] = array(
		'title' => 'CMB Test - all fields',
		'pages' => 'post',
		'fields' => $fields
	);

	// Examples of Groups and Columns

	$groups_and_cols = array(
		array( 'id' => 'gac-1',  'name' => 'Text input field', 'type' => 'text', 'cols' => 4 ),
		array( 'id' => 'gac-2',  'name' => 'Text input field', 'type' => 'text', 'cols' => 4 ),
		array( 'id' => 'gac-3',  'name' => 'Text input field', 'type' => 'text', 'cols' => 4 ),
		array( 'id' => 'gac-4', 'name' => 'Group (4 columns)', 'type' => 'group', 'cols' => 4, 'fields' => array(
			array( 'id' => 'gac-4-f-1',  'name' => 'Textarea field', 'type' => 'textarea' )
		) ),
		array( 'id' => 'gac-5', 'name' => 'Group (8 columns)', 'type' => 'group', 'cols' => 8, 'fields' => array(
			array( 'id' => 'gac-4-f-1',  'name' => 'Text input field', 'type' => 'text' ),
			array( 'id' => 'gac-4-f-2',  'name' => 'Text input field', 'type' => 'text' ),
		) ),
	);

	$meta_boxes[] = array(
		'title' => 'Groups and Columns',
		'pages' => 'post',
		'fields' => $groups_and_cols
	);

	// Example of repeatable group. Using all fields.
	// For this example, copy fields from $fields, update I
	$group_fields = $fields;
	foreach ( $group_fields as &$field ) {
		$field['id'] = str_replace( 'field', 'gfield', $field['id'] );
	}

	$meta_boxes[] = array(
		'title' => 'CMB Test - group (all fields)',
		'pages' => 'post',
		'fields' => array(
			array( 
				'id' => 'gp', 
				'name' => 'My Repeatable Group', 
				'type' => 'group', 
				'repeatable' => true,
				'sortable' => true,
				'fields' => $group_fields,
				'desc' => 'This is the group description.'
			)
		)
	);

	return $meta_boxes;

}
add_filter( 'cmb_meta_boxes', 'cmb_sample_metaboxes' );
