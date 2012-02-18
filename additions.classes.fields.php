<?php

/**
 * Abstract class for all fields.
 * Subclasses need only override html()
 * 
 * @abstract
 */
abstract class CMB_Field {
	
	public $value;
	
	/**
	 * used for repeatable
	 * 
	 */
	static $did_saves;
	
	
	/**
	 * used for repeatable
	 * 
	 */
	static $next_values;
	
	public function __construct( $name, $title, $value, $args = array() ) {
		
		$this->name 	= $name;
		$this->title 	= $title;
		$this->args		= $args;
		
		if ( ! empty( $this->args['repeatable'] ) ) {
			
			if ( !isset( self::$next_values[$name] ) )
				self::$next_values[$name] = 0;
				
			if ( !isset( self::$did_saves[$name] ) )
				self::$did_saves[$name] = false;
				
			$value = (array) $value;
			
			$this->value = $value[(int)self::$next_values[$name]];
			self::$next_values[$name]++;
		
		} else {
			$this->value = $value;
		}
					
		$this->description = '';
		
	}
	
	public function save( $post_id ) {
		
		if ( $this->args['repeatable'] ) {
			
			if ( ! self::$did_saves[$this->name] )
				$this->save_multiple( $post_id );
		
		} else {
			update_post_meta( $post_id, $this->name, $this->value );
		}
	
	}
	
	public function save_multiple( $post_id ) {
			
		delete_post_meta( $post_id, $this->name );
		
		foreach( (array) $this->value as $v ) {
			add_post_meta( $post_id, $this->name, $v );
		}
		
		self::$did_saves[$this->name] = true;
		
	}
}

/**
 * Standard text field.
 * 
 */
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

/**
 * Field for image upload / file updoad.
 * 
 * @todo work with files as well as images
 * @todo ability to set image size (preview image) from caller
 */
class CMB_File_Field extends CMB_Field {

	public function html() {
		
		$field = array(
			'id' => $this->name,
			'allow' => '',
			'desc' => $this->description,
			'html_id' => str_replace( array( '[', ']' ),  '_', $this->name . rand(1,100) )
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

/**
 * Standard text meta box for a URL.
 * 
 */
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
 * Standard text meta box for a URL.
 * 
 */
class CMB_Oembed_Field extends CMB_Field {

	public function html() {
	
		echo '<input class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="', '' !== $this->value ? esc_url( $this->value ) : $this->args['std'], '" /><span class="cmb_metabox_description">', $this->args['desc'], '</span>';
	}
}


/**
 * Field to group child fieids
 * pass $args[fields] array for child fields
 * pass $args['repeatable'] for cloing all child fields (set) 
 *
 * @todo remove global $post reference, somehow
 */
class CMB_Group_Field extends CMB_Field {
	
	static $added_js;
	
	public function __construct( $name, $title, $value, $args = array() ) {
		
		$this->name 	= $name;
		$this->title 	= $title;
		$this->args		= $args;
		$this->value = $value;
			
		$this->description = '';
		
	}
	
	public function html() {
		
		global $post;
		
		global $post;
		// mutltiple so is differernt
		$meta = get_post_meta( $post->ID, $this->name, false );
		
		$meta[] = '';
		
		$field = $this->args;
		
		?>
		<div class="cloneable-group">
			
			<?php foreach ( $meta as $value ) : ?>
				<div style="background: #eee; border-radius: 5px; padding: 5px; margin-bottom: 10px;" class="group <?php echo $field['repeatable'] == true ? 'cloneable' : '' ?> <?php echo $value == '' ? 'hidden' : '' ?>">
					
					<a class="delete-group button" style="float: right">X</a>
					<?php foreach ( $this->args['fields'] as $f ) {
						
						$f['uid'] = $field['id'] . '[' . $f['id'] . ']';
						
						// If it's cloneable , make it an array
						if ( $field['repeatable'] == true )
							$f['uid'] .= '[]';
				
						$class = _cmb_field_class_for_type( $f['type'] );

						$field_obj = new $class( $f['uid'], $f['name'], isset( $value[$f['id']] ) ? $value[$f['id']] : '' );
						
						$field_obj->html();
					
					} ?>
					
				</div>
			<?php endforeach; ?>
			
			<?php if ( $field['repeatable'] == true ) : ?>
				
				<p>
				    <a href="#" class="clone-group button">Add New</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
		
		
		if ( ! self::$added_js ) : ?>
		
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
		
		<?php self::$added_js = true; endif; 
		
	}
	
	public function save_multiple( $post_id ) {

		delete_post_meta( $post_id, $this->name );
	
		$first = reset( $this->value );
		
		foreach ($first as $key => $field_val ) {
			
			$meta = array();
			
			foreach ( $this->args['fields'] as $construct_field )
				$meta[$construct_field['id']] = $this->value[$construct_field['id']][$key];

			if( array_filter( $meta ) )
				add_post_meta( $post_id, $this->name, $meta );
			
		}
	
	}

}