<?php

class GroupFieldTestCase extends WP_UnitTestCase {

	function testAddField() {

		$group = new CMB_Group_Field( 'group', 'Group Title', array() );
		$field = new CMB_Text_Field( 'foo', 'Title', array( 1, 2 ) );

		$group->add_field( $field );
	
		$this->assertEquals( $field->name, 'group[foo][][]' );
	
	}

	function testSetValues() {
		
		$group = new CMB_Group_Field( 'group', 'Group Title', array() );
		$field = new CMB_Text_Field( 'foo', 'Title', array() );

		$group->add_field( $field );
		
		$group->set_values( $values = array( 
			array( 'foo' => array( 1, 2 ) ) 
		) );

		$this->assertEquals( $group->get_values(), $values );
	
	}

	function testParseSaveValues() {

		$group = new CMB_Group_Field( 'group', 'Group Title', array() );
		$field = new CMB_Text_Field( 'foo', 'Title', array() );

		$group->add_field( $field );
		$group->values = $values = array( 
				'foo' => array( 1, 2 ) 
		);
		
		$group->parse_save_values();

		error_log( print_r( $group->get_values(), true ) );
		
		$this->assertEquals( $group->get_values(), $values );

	}
}