<?php

class CMB_Field {
	
	public $id;
	public $value;
	
	public function __construct( $id, $name, $title, $value ) {
	
		$this->id 		= $id;
		$this->name 	= $name;
		$this->title 	= $title;	
		$this->value 	= $value;
		$this->description = '';
	}
	
	public function html() {
	
	}
}

class CMB_Text_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<label><?php echo $this->title ?>
				<input type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" />
			</label>
		</p>
		<?php
	}
}

class CMB_Text_Small_Field extends CMB_Field {

	public function html() {
	
		?>

		<p>
			<label style="display:inline-block; width: 200px;"><?php echo $this->title ?></label>
			<input class="cmb_text_small" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

class CMB_File_Field extends CMB_Field {

	public function html() {
		
		$field = array(
			'id' => $this->id,
			'allow' => '',
			'desc' => $this->description,
			'html_id' => str_replace( array( '[', ']' ),  '_', $this->id . rand(1,100) )
		);
		
		if ( wp_get_attachment_image_src( $this->value, 'width=100&height=100' ) )
			$meta = reset( wp_get_attachment_image_src( $this->value, 'width=100&height=100' ) );
		else
			$meta = '';
		
		$input_type_url = "hidden";
		if ( 'url' == $field['allow'] || ( is_array( $field['allow'] ) && in_array( 'url', $field['allow'] ) ) )
		    $input_type_url="text";
		   
		echo '<label>', $this->title, '<br /></label>';
		echo '<input class="cmb_upload_file" type="' . $input_type_url . '" size="45" id="', $field['html_id'], '" value="', $meta, '" />';
		echo '<input class="cmb_upload_button button" type="button" value="Upload File" />';
		echo '<input class="cmb_upload_file_id" type="hidden" id="', $field['html_id'], '_id" name="', $field['id'], '" value="', $this->value, '" />';					
		echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
		echo '<div id="', $field['html_id'], '_status" class="cmb_upload_status">';	
		    if ( $meta != '' ) { 
		    	$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
		    	if ( $check_image ) {
		    		echo '<div class="img_status">';
		    		echo '<img src="', $meta, '" alt="" />';
		    		echo '<a href="#" class="cmb_remove_file_button" rel="', $field['html_id'], '">Remove Image</a>';
		    		echo '</div>';
		    	}	
		    }
		echo '</div>'; 
		
	}
}			

class CMB_Text_URL_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<label style="display:inline-block; width: 200px;"><?php echo $this->title ?></label>
			<input class="cmb_text_url" type="text" name="<?php echo $this->name ?>" value="<?php echo esc_url( $this->value ) ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

/**
 * Render a cloneable text field
 *
 * @param string $field
 * @return null
 */
function cmb_render_group( $field, $meta ) { 
	
	global $post;
	$meta = get_post_meta( $post->ID, $field['id'], false );
	
	$meta[] = '';
	
	?>
	
	<div class="cloneable-group">
		
		<?php foreach ( $meta as $value ) : ?>
			<div style="background: #eee; border-radius: 5px; padding: 5px; margin-bottom: 10px;" class="group <?php echo $field['cloneable'] == true ? 'cloneable' : '' ?> <?php echo $value == '' ? 'hidden' : '' ?>">
				
				<a class="delete-group button" style="float: right">X</a>
				<?php foreach ( $field['fields'] as $f ) {
					
					$f['uid'] = $field['id'] . '[' . $f['id'] . ']';
					
					// If it's cloneable , make it an array
					if ( $field['cloneable'] == true )
						$f['uid'] .= '[]';
			
					$class = _cmb_field_class_for_type( $f['type'] );
					
					$field_obj = new $class( $f['uid'], $f['uid'], $f['name'], isset( $value[$f['id']] ) ? $value[$f['id']] : '' );
					
					$field_obj->html();
				
				} ?>
				
			</div>
		<?php endforeach; ?>
		
		<?php if ( $field['cloneable'] == true ) : ?>
			
			<p>
			    <a href="#" class="clone-group button">Add New</a>
			</p>
		<?php endif; ?>
	</div>
	<?php
	
	static $added_js;
	
	if ( ! $added_js ) : ?>
	
		<script type="text/javascript">
			
			jQuery( document ).on( 'click', 'a.clone-group', function( e ) {
			
				e.preventDefault();
				var a = jQuery( this );
				
				var newT = a.parent().prev().clone().removeClass('hidden');
				newT.find('input[type!="button"]').val('');
				newT.find( '.cmb_upload_status' ).html('');
				newT.insertBefore( a.parent().prev() );
				
			} );
			
			jQuery( document ).on( 'click', 'a.delete-group', function( e ) {
			
				e.preventDefault();
				var a = jQuery( this );
				
				a.closest( '.group' ).remove();
				
			} );
			
		</script>
	
	<?php $added_js = true; endif; 
}
add_action( 'cmb_render_group', 'cmb_render_group', 10, 2 );

function _cmb_field_class_for_type( $type ) {

	$map = array(
	
		'text'			=> 'CMB_Text_Field',
		'text_small' 	=> 'CMB_Text_Small_Field',
		'text_url'		=> 'CMB_Text_URL_Field',
		'file'			=> 'CMB_File_Field'
	);
	
	return $map[$type];

}

function _cmb_group_validate_save( $value, $post_id, $fields ) {

	delete_post_meta( $post_id, $fields['id'] );
	
	$first = reset( $value );

	foreach ($first as $key => $field_val ) {
		
		$meta = array();
		
		foreach ( $fields['fields'] as $construct_field )
			$meta[$construct_field['id']] = $value[$construct_field['id']][$key];
			
		if( array_filter( $meta ) )
			add_post_meta( $post_id, $fields['id'], $meta );
		
	}
	
	
	// we have to return false to stop paretn from overwtiing
	return false;
			
}
add_filter( 'cmb_validate_group', '_cmb_group_validate_save', 10, 3 );
