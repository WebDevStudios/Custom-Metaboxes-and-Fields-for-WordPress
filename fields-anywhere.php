<?php 

/**
 * Create CMB Meta boxes anywhere you like (other than the post edit screen).
 *
 * This is functional, but a little hacky.
 */

/**
 * Draw the meta boxes in places other than the post edit screen
 * 
 * @return null
 */
function cmb_draw_meta_boxes( $pages, $context = 'normal', $object = null ) {

	cmb_do_meta_boxes( $pages, $context, $object );

	wp_enqueue_script('post');

}

/**
 * Meta-Box template function
 *
 * @since 2.5.0
 *
 * @param string|object $screen Screen identifier
 * @param string $context box context
 * @param mixed $object gets passed to the box callback function as first parameter
 * @return int number of meta_boxes
 */
function cmb_do_meta_boxes( $screen, $context, $object ) {

	global $wp_meta_boxes;

	static $already_sorted = false;

	if ( empty( $screen ) )
		$screen = get_current_screen();

	elseif ( is_string( $screen ) )
		$screen = convert_to_screen( $screen );

	$page = $screen->id;

	$hidden = get_hidden_meta_boxes( $screen );

	$i = 0;

	do {
		// Grab the ones the user has manually sorted. Pull them out of their previous context/priority and into the one the user chose

		if ( ! $already_sorted && $sorted = get_user_option( "meta-box-order_$page" ) )
			foreach ( $sorted as $box_context => $ids )
				foreach ( explode(',', $ids ) as $id )
					if ( $id && 'dashboard_browser_nag' !== $id )
						add_meta_box( $id, null, null, $screen, $box_context, 'sorted' );

		$already_sorted = true;

		if ( ! isset( $wp_meta_boxes ) || ! isset( $wp_meta_boxes[$page] ) || ! isset( $wp_meta_boxes[$page][$context] ) )
			break;

		foreach ( array( 'high', 'sorted', 'core', 'default', 'low' ) as $priority ) {

			if ( isset( $wp_meta_boxes[$page][$context][$priority] ) ) {

				foreach ( (array) $wp_meta_boxes[$page][$context][$priority] as $box ) {

					if ( false == $box || ! $box['title'] )
						continue;

					$i++;

					$hidden_class = in_array($box['id'], $hidden) ? ' hide-if-js' : ''; ?>

					<div id="<?php esc_attr_e( $box['id'] ); ?>" class="<?php esc_attr_e( postbox_classes( $box['id'], $page ) . $hidden_class ); ?>">

						<?php call_user_func( $box['callback'], $object, $box ); ?>

					</div>

				<?php }

			}

		}
	} while( 0 );

	return $i;

}