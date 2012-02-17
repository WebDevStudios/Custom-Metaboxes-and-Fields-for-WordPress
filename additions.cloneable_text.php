<?php

/**
 * Render a cloneable text field
 *
 * @param string $field
 * @return null
 */
function cmb_render_text_cloneable( $field, $meta ) { ?>

	<div class="cmb-cloneable-text-inputs">

	<?php foreach ( $meta as $key => $meta_value ) : ?>

	    <input style="width: 92%" type="text" name="<?php echo $field['id'] ?>[]" value="<?php echo $meta_value ?>">

	<?php endforeach; ?>

	<?php if ( ! $meta ) : ?>

	    <input style="width: 92%" type="text" name="<?php echo $field['id'] ?>[]">

	<?php endif; ?>

	</div>

	<p>
		<button class="button cmb-cloneable-text-add-new">Add Field</button>
	</p>

	<?php

	// Only add the javascript once
	static $added_js;

	if ( ! $added_js ) :

		$added_js = true; ?>

		<script type="text/javascript">
			jQuery( document ).ready( function() {

				closeButton = '<button class="button cmb-cloneable-text-remove">Remove</button>';
				field = '<input style="width: 92%" type="text" name="<?php echo $field['id'] ?>[]" value="">';

				jQuery( '.cmb-cloneable-text-inputs input:not(:first)' ).each( function() {

					console.log( this );

					jQuery( this ).after( closeButton );

				} );

				jQuery( '.cmb-cloneable-text-add-new' ).live( 'click', function( e ) {

					e.preventDefault();

					jQuery( '.cmb-cloneable-text-inputs' ).append( field ).append( closeButton );

				} )

				jQuery( '.cmb-cloneable-text-remove' ).live( 'click', function( e ) {

					e.preventDefault();

					jQuery( this ).prev().remove();
					jQuery( this ).remove();

				} )

			} );
		</script>

	<?php endif;

}
add_action( 'cmb_render_text_cloneable', 'cmb_render_text_cloneable', 10, 2 );

/**
 * Validate the cloneable text field data
 *
 * @param array $data
 * @return array
 */
function cmb_validate_text_cloneable( array $data ) {

	sort( $data );
	return array_filter( $data );

}
add_filter( 'cmb_validate_text_cloneable', 'cmb_validate_text_cloneable' );