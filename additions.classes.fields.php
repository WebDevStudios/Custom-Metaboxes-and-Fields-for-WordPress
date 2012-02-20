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
	
	public function __construct( $name, $title, array $values, $args = array() ) {
		
		$this->id 		= $name;
		$this->name		= $name . '[]';
		$this->title 	= $title;
		$this->args		= wp_parse_args( $args, array( 'repeatable' => false, 'std' => '', 'show_label' => false ) );

	
		$this->values 	= $values;
		$this->value 	= reset( $this->values );
					
		$this->description = $this->args['desc'];
	}
	
	public function get_value() {
		return $this->value;
	}
	
	public function parse_save_values() {
		
	}
	
	public function parse_save_value() {

	}
	
	public function save( $post_id ) {
		
		$this->parse_save_values();
		
		delete_post_meta( $post_id, $this->id );

		foreach( $this->values as $v ) {
			$this->value = $v;
			$this->parse_save_value();
			
			if ( $v )
				add_post_meta( $post_id, $this->id, $this->value );
		}
	
	}
	
	public function display() {
		
		// if there are no values and it's not repeateble, we want to do one with empty string
		if ( empty( $this->values ) && !  $this->args['repeatable'] )
			$this->values = array( '' );
		
		foreach ( $this->values as $value ) {
			
			$this->value = $value;
			
			echo '<div class="field-item">';
			$this->html();
			echo '</div>';
			
		}
		
		// insert a hidden one if it's repeatable
		if ( $this->args['repeatable'] ) {
			$this->value = '';
			
			echo '<div class="field-item hidden">';
			$this->html();
			echo '</div>';
			
			?>
			<p>
				<a href="#" class="button repeat-field">Add New</a>
			</p>
			<?php
		}
	
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
			<label><?php if ( $this->args['show_label'] ) : ?><?php echo $this->title ?><?php endif; ?>
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
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
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
		  
		if ( $this->args['show_label'] )
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
class CMB_URL_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
			<input class="cmb_text_url" type="text" name="<?php echo $this->name ?>" value="<?php echo esc_url( $this->value ) ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

/**
 * Date picker box.
 * 
 */
class CMB_Date_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

class CMB_Time_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
			<input class="cmb_text_small cmb_timepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ? date( 'm\/d\/Y', $this->value ) : '' ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

/**
 * Date picker for date only (not time) box.
 * 
 */
class CMB_Date_Timestamp_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ? date( 'm\/d\/Y', $this->value ) : '' ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
	
	public function parse_save_value() {
		return strtotime( $this->value );
	}
}

/**
 * Date picker for date and time (seperate fields) box.
 * 
 */
class CMB_Datetime_Timestamp_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->name ?>[date]" value="<?php echo $this->value ? date( 'm\/d\/Y', $this-value ) : '' ?>" />
			<input class="cmb_text_small cmb_timepicker" type="text" name="<?php echo $this->name ?>[time]" value="<?php echo $this->value ? date( 'm\/d\/Y', $this-value ) : '' ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
	
	public function parse_save_value() {
		return strtotime( $this->value['date'] . ' ' . $this->value['time'] );
	}
}


/**
 * Standard text meta box for a URL.
 * 
 */
class CMB_Oembed_Field extends CMB_Field {

	public function html() {
		
		?>
		<p>
			<?php if ( $this->args['show_label'] ) : ?><label style="display:inline-block; width: 70%"><?php echo $this->title ?></label><?php endif; ?>
			
			<?php if ( ! $this->value ) : ?>
				<?php echo '<input class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="" /><span class="cmb_metabox_description">', $this->description, '</span>'; ?>
			<?php else : ?>
				
				<?php echo '<div class="hidden"><input disabled class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="" /><span class="cmb_metabox_description">', $this->description, '</span></div>'; ?>
				
				<div style="position: relative">
					<span><?php echo $this->value ?></span>
					<input type="hidden" name="<?php echo $this->name ?>" value="<?php echo esc_attr( $this->value ) ?>" />
					<a href="#" class="cmb_remove_file_button" onclick="jQuery( this ).closest('div').prev().removeClass('hidden').find('input').first().removeAttr('disabled')">Remove</a>
				</div>	
				
			<?php endif; ?>
		</p>
		<?php
	}
	
	public function parse_save_value() {
		
		if ( strpos( $this->value, 'http' ) === 0 )
			$this->value = wp_oembed_get( $this->value, array( 'height' => 75 ) );
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
	
	public function display() {
		
		global $post;
		
		$meta = $this->values;
		
		if ( ! $meta && ! $this->args['repeatable'] )
			$meta = array( '' );
	
		$field = $this->args;
		
		foreach ( $meta as $value ) {
				
			$this->value = $value;
			echo '<div class="field-item">';
			$this->html();
			echo '</div>';
				
		}
		
		
		if ( $this->args['repeatable'] ) {
			$this->value = '';
			echo '<div class="field-item hidden">';
			$this->html();
			echo '</div>';
			?>
			<p>
				<a href="#" class="button repeat-field">Add New</a>
			</p>
			<?php
		}
		
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
	
	public function html() {
		
		$field = $this->args;
		$value = $this->value;
		
		?>
		<div style="background: #eee; border-radius: 5px; padding: 5px; margin-bottom: 10px;" class="group <?php echo !empty( $field['repeatable'] ) ? 'cloneable' : '' ?>">
		    
		    <a class="delete-group button" style="float: right">X</a>
		    <?php foreach ( $this->args['fields'] as $f ) {
		    	
		    	$f['uid'] = $field['id'] . '[' . $f['id'] . ']';
		    	
		    	// If it's cloneable , make it an array
		    	if ( $field['repeatable'] == true )
		    		$f['uid'] .= '[]';
		
		    	$class = _cmb_field_class_for_type( $f['type'] );
		    	$f['show_label'] = true;
		    	
		    	$field_obj = new $class( $f['uid'], $f['name'], isset( $value[$f['id']] ) ? $value[$f['id']] : array( '' ), $f );
		    	
		    	$field_obj->display();
		    
		    } ?>
		    
		</div>
			
		<?php
	}
	
	public function parse_save_values() {
				
		$values = $this->values;
		$this->values = array();
		
		$first = reset( $values );
		
		foreach ($first as $key => $field_val ) {
			
			$meta = array();
			
			foreach ( $this->args['fields'] as $construct_field ) {
				
				// create the fiel object so it can sanitize it's data etc
				$class = _cmb_field_class_for_type( $construct_field['type'] );
				$field = new $class( $construct_field['id'], $construct_field['name'], $values[$construct_field['id']][$key], $construct_field );

				$field->parse_save_value();
				
				
				$meta[$construct_field['id']] = array( $field->get_value() );
			}
			
			if( $this->isNotEmptyArray( $meta ) )
				$this->values[] = $meta;
			
		}
		
	}
	
	private function isNotEmptyArray( $array ) {
	
		foreach ($array as &$value) 
    	{ 
    	  if (is_array($value)) 
    	  { 
    	    $value = $this->isNotEmptyArray($value); 
    	  } 
    	} 
    	
    	return array_filter($array); 
	
	}
}