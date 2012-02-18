<?php

/**
 * Render an oembed text field
 *
 * @param string $field
 * @return null
 */
function cmb_render_oembed( $field, $meta ) {
	
	echo '<input class="cmb_oembed code" type="text" name="', $field['name_attr'], '" id="', $field['id'], '" value="', '' !== $meta ? esc_url( $meta ) : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
}
add_action( 'cmb_render_oembed', 'cmb_render_oembed', 10, 2 );

/**
 * Validate the oembed text field data
 *
 * @param array $data
 * @return array
 */
function cmb_validate_oembed( $new, $post_id, $field ) {

	delete_post_meta( $post_id, $field['id'] . '_code' );

	// Run each url through wp_oembed_get and save the embed code
	foreach( (array) $new as $url ) {

		$oembed = wp_oembed_get( $url, array( 'height' => 75 ) );
	
		if ( $oembed )
			add_post_meta( $post_id, $field['id'] . '_code', $oembed );
			
	}
		
	return $new;

}
add_filter( 'cmb_validate_oembed', 'cmb_validate_oembed', 10, 3 );