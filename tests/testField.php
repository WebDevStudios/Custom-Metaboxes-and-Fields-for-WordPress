<?php

class FieldTestCase extends WP_UnitTestCase {

	function setUp() {

	}

	function testGetValues() {

		$field = new CMB_Text_Field( 'foo', 'Title', array( 1, 2 ) );
		$this->assertEquals( $field->get_values(), array( 1, 2 ) );

		$field = new CMB_Text_Field( 'foo', 'Title', array( 1, 2 ), array( 'repeatable' => true ) );
		$this->assertEquals( $field->get_values(), array( 1, 2 ) );
	}

	function testSaveValues() {
		
		$field = new CMB_Text_Field( 'foo', 'Title', array( 1, 2 ) );

		$post = reset( get_posts( 'showposts=1' ) );

		if ( ! $post )
			$this->markTestSkipped( 'Post not found' );

		$field->save( $post->ID, array( 1, 2 ) );

		$meta = get_post_meta( $post->ID, 'foo', false );
		delete_post_meta( $post->ID , 'foo' );
		
		// Order is not guaranteed
		sort( $meta );

		$this->assertEquals( $meta, array( 1 , 2 ) );

	}

	function testSaveValuesOnRepeatable() {
		
		$field = new CMB_Text_Field( 'foo', 'Title', array( 1, 2 ), array( 'repeatable' => true ) );

		$post = reset( get_posts( 'showposts=1' ) );

		if ( ! $post )
			$this->markTestSkipped( 'Post not found' );

		$field->save( $post->ID, array( 1, 2 ) );

		$meta = get_post_meta( $post->ID, 'foo', false );

		delete_post_meta( $post->ID , 'foo' );

		// Order is not guerenteed
		sort( $meta );
		
		$this->assertEquals( $meta, array( 1, 2 ) );

	}

}