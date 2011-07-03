<?php

// Include & setup custom metabox and fields
$prefix = 'cmb_';
$meta_boxes = array();

$meta_boxes[] = array(
    'id' => 'test_metabox',
    'title' => 'Test Metabox',
    'pages' => array('page'), // post type
	'context' => 'normal',
	'priority' => 'high',
	'show_names' => true, // Show field names on the left
    'fields' => array(
        array(
            'name' => 'Test Text',
            'desc' => 'field description (optional)',
            'id' => $prefix . 'test_text',
            'type' => 'text'
        ),
		array(
            'name' => 'Test Text Small',
            'desc' => 'field description (optional)',
            'id' => $prefix . 'test_textsmall',
            'type' => 'text_small'
        ),
		array(
            'name' => 'Test Text Medium',
            'desc' => 'field description (optional)',
            'id' => $prefix . 'test_textmedium',
            'type' => 'text_medium'
        ),
		array(
	        'name' => 'Test Date Picker',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_textdate',
	        'type' => 'text_date'
	    ),
		array(
	        'name' => 'Test Money',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_textmoney',
	        'type' => 'text_money'
	    ),
	    array(
	        'name' => 'Test Text Area',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_textarea',
	        'type' => 'textarea'
	    ),
		array(
	        'name' => 'Test Text Area Small',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_textareasmall',
	        'type' => 'textarea_small'
	    ),
		array(
	        'name' => 'Test Title Weeeee',
	        'desc' => 'This is a title description',
	        'type' => 'title'
	    ),
		array(
		       'name' => 'Test Select',
		       'desc' => 'field description (optional)',
		       'id' => $prefix . 'test_select',
		       'type' => 'select',
				'options' => array(
					array('name' => 'Option One', 'value' => 'standard'),
					array('name' => 'Option Two', 'value' => 'custom'),
					array('name' => 'Option Three', 'value' => 'none')				
				)
		),
		array(
	        'name' => 'Test Radio inline',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_radio',
	        'type' => 'radio_inline',
			'options' => array(
				array('name' => 'Option One', 'value' => 'standard'),
				array('name' => 'Option Two', 'value' => 'custom'),
				array('name' => 'Option Three', 'value' => 'none')				
			)
	    ),
		array(
	        'name' => 'Test Radio',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_radio',
	        'type' => 'radio',
			'options' => array(
				array('name' => 'Option One', 'value' => 'standard'),
				array('name' => 'Option Two', 'value' => 'custom'),
				array('name' => 'Option Three', 'value' => 'none')				
			)
	    ),
		array(
	        'name' => 'Test Checkbox',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_checkbox',
	        'type' => 'checkbox'
	    ),
		array(
	        'name' => 'Test Multi Checkbox',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_multicheckbox',
	        'type' => 'multicheck',
			'options' => array(
				'check1' => 'Check One',
				'check2' => 'Check Two',
				'check3' => 'Check Three',
			)
	    ),
		array(
	        'name' => 'Test wysiwyg',
	        'desc' => 'field description (optional)',
	        'id' => $prefix . 'test_wysiwyg',
	        'type' => 'wysiwyg'
	    ),
        array(
            'name' => 'Test Audio',
            'desc' => 'Upload an audio file.',
            'id' => $prefix .'audio_embed',  //Must use This id to enable Audio Shortcode
            'type' => 'file_audio'
        ),

		array(
	        'name' => 'Test Image',
	        'desc' => 'Upload an image or enter an URL.',
	        'id' => $prefix . 'test_image',
	        'type' => 'file'
	    ),
    )
);

require_once('metabox/init.php');