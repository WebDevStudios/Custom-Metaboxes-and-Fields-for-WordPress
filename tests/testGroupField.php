<?php

class GroupFieldTestCase extends WP_UnitTestCase {

	function setUp() {

	}

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
		$group->set_values( $arr = array( 
			array( 'foo' => array( 1, 2 ) ) 
		) );

		$this->assertEquals( $group->get_values(), $arr );

		$this->assertEquals( $field->get_values(), array( 1, 2 ) );
	}

	function testParseSaveValues() {

		$group = new CMB_Group_Field( 'group', 'Group Title', array() );
		$field = new CMB_Text_Field( 'foo', 'Title', array() );

		$group->add_field( $field );
		$group->values = array( 'foo' => array( array( 1, 2 ) ) );
		$group->parse_save_values();

		$this->assertEquals( $group->get_values(), array( array( 'foo' => array( 1, 2 ) ) ) );

	}
}