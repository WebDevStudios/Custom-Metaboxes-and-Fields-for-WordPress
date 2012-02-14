<?php

add_action( 'cmb_render_cloneable_text', function( $field, $meta ) {

	// meta won't have the multiples so get it again
	global $post;
	
	$meta = get_post_meta( $post->ID, $field['id'], false );
	
	?>
	
	<div class="cmb-cloneable-text-inputs">
		<?php foreach ( $meta as $meta_value ) : ?>
			<input style="width: 92%" type="text" name="<?php echo $field['id'] ?>[]" value="<?php echo $meta_value ?>"><a href="#" class="button cmb-cloneable-text-remove">X</a>
		<?php endforeach; ?>
		
		<?php if ( ! $meta ) : ?>
		
			<input style="width: 92%" type="text" name="<?php echo $field['id'] ?>[]" value=""><a href="#" class="button cmb-cloneable-text-remove">X</a>
		
		<?php endif; ?>
	</div>
	
	<span class="cmb-cloneable-text-template" style="display:none;">
		<input style="width: 92%" type="text" name="<?php echo $field['id'] ?>[]" value=""><a href="#" class="button cmb-cloneable-text-remove">X</a>
	</span>
	
	<p>
		<a class="button cmb-cloneable-text-add-new" href="#">Add New</a>
	</p>
	
	<?php
	
	// only add the javascript once
	static $added_js;
	
	if ( ! $added_js ) : $added_js = true;?>
		
		<script type="text/javascript">
			jQuery( document ).ready( function() {
			
				jQuery( '.cmb-cloneable-text-add-new' ).live( 'click', function(e) {
					e.preventDefault();
					
					var newT = jQuery( '.cmb-cloneable-text-template' ).children().clone();
					jQuery( '.cmb-cloneable-text-inputs' ).append( newT );
					
				} )
				
				jQuery( '.cmb-cloneable-text-remove' ).live( 'click', function(e) {
					e.preventDefault();
					
					jQuery( this ).prev().remove();
					jQuery( this ).remove();
					
				} )
			
			} );
		</script>
	<?php endif;

}, 10, 2 );


add_action( 'cmb_render_pl_contest_entries', function() {
	
	global $post;
	
	$entries = get_post_meta( $post->ID, '_contest_entry', false );
	
	if ( ! $entries )
		return;
	
	$sorted_entries = array();
	foreach ( $entries as $entry )
		$sorted_entries[$entry['answer']][] = $entry;
	?>
	
	<table class="widefat cmb-entry-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Date Entered</th>
				</tr>
			</thead>
	</table>
	
	<style>
		.cmb-entry-table th,
		.cmb-entry-table td { width: 30%; text-align: left !important; }
	</style>
	<?php foreach ( $sorted_entries as $answer => $entries ) : ?>
		<p><strong><?php echo $answer ?></strong></p>
		<table class="widefat cmb-entry-table">
			<tbody>
				<?php foreach ( $entries as $entry ) : ?>
					<tr>
						<td><?php echo $entry['name'] ?></td>
						<td><?php echo $entry['email'] ?></td>
						<td><?php echo date( DATE_RFC822, $entry['date'] ) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
	<?php

} );

add_filter( 'cmb_validate_cloneable_text', function( $data ) {
	$data = (array) $data;
	sort( $data );
	return array_filter( $data );
} );