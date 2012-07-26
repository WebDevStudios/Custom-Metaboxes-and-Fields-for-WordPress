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
		$this->args		= wp_parse_args( $args, array(
			'repeatable' => false,
			'std'        => '',
			'default'    => '',
			'show_label' => false,
			'taxonomy'   => '',
			'hide_empty' => false,
			'data_delegate' => null,
			'options'	=> array(),
			'cols' 	=> '12'
			)
		);

		if ( ! empty( $this->args['std'] ) && empty( $this->args['default'] ) ) {
			$this->args['default'] = $this->args['std'];
			_deprecated_argument( 'CMB_Field', "'std' is deprecated, use 'default instead'", '0.9' );
		}

		if ( ! empty( $this->args['options'] ) && is_array( reset( $this->args['options'] ) ) ) {

			$re_format = array();

			foreach ( $this->args['options'] as $option ) {
				$re_format[$option['value']] = $option['name'];
			}
			_deprecated_argument( 'CMB_Field', "'std' is deprecated, use 'default instead'", '0.9' );

			$this->args['options'] = $re_format;
		}

		$this->values 	= $values;
		$this->value 	= reset( $this->values );

		$this->description = $this->args['desc'];

	}

	/**
	 * Method responsible for enqueueing any extra scripts the field needs
	 * 
	 * @uses wp_enqueue_script()
	 */
	public function enqueue_scripts() {}

	/**
	 * Method responsible for enqueueing any extra styles the field needs
	 * 
	 * @uses wp_enqueue_style()
	 */
	public function enqueue_styles() {}

	/**
	 * Check if this field has a data delegate set
	 * 
	 * @return boolean
	 */
	public function has_data_delegate() {
		return (bool) $this->args['data_delegate'];
	}

	/**
	 * Get the array of data from the data delegate
	 * 
	 * @return array mixed
	 */
	protected function get_delegate_data() {
		if ( $this->args['data_delegate'] )
			return call_user_func_array( $this->args['data_delegate'], array( $this ) );

		return array();
	}

	public function get_value() {

	   return ( $this->value ) ? $this->value : $this->args['default'];
	}

	public function parse_save_values() {

		// if it's repeatable take off the last one
		if ( $this->args['repeatable'] ) {
			end( $this->values );
			unset( $this->values[key( $this->values )] );
			reset( $this->values );
		}
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

		echo '<strong>' . $this->args['name'] . '</strong>';

		foreach ( $this->values as $value ) {

			$this->value = $value;

			echo '<div class="field-item" style="position: relative">';

			if ( $this->args['repeatable'] ) : ?>
				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon" style="position: absolute; top: 5px; right: -10px">X</a>
					</span>
				</span>
			<?php endif;

			$this->html();
			echo '</div>';

		}

		// insert a hidden one if it's repeatable
		if ( $this->args['repeatable'] ) {
			$this->value = '';

			echo '<div class="field-item hidden" style="position: relative">';
			
			if ( $this->args['repeatable'] ) : ?>
				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon" style="position: absolute; top: 5px; right: -10px">X</a>
					</span>
				</span>
			<?php endif;

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
				<input type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" />
		</p>
		<?php
	}
}

class CMB_Text_Small_Field extends CMB_Field {

	public function html() {

		?>
		<p>
			<input class="cmb_text_small" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

/**
 * Field for image upload / file updoad.
 *
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
		
		// value can be URL of file or id of image attachment
		
		if ( is_numeric( $this->value ) ) {
			if ( wp_get_attachment_image_src( $this->value, 'width=100&height=100' ) )
				$meta = reset( wp_get_attachment_image_src( $this->value, 'width=100&height=100' ) );
			else
				$meta = '';
		} else {
			$meta = $this->value;
		}

		$input_type_url = "hidden";
		if ( 'url' == $field['allow'] || ( is_array( $field['allow'] ) && in_array( 'url', $field['allow'] ) ) )
			$input_type_url="text";

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
				} else {
					?>
					<div class="img_status">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo $meta ?>" target="_blank">View File</a>
					<a href="#" class="cmb_remove_file_button" rel="<?php echo $field['html_id'] ?>">Remove</a>
					</div>
					<?php
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
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" /> <span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

class CMB_Time_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
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

	public function html() { ?>
	
		<style>
		
			.cmb_oembed img, .cmb_oembed object, .cmb_oembed video, .cmb_oembed embed, .cmb_oembed iframe { max-width: 100%; height: auto; }
		
		</style>

		<p>

			<?php if ( ! $this->value ) : ?>
				<?php echo '<input class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="" /><span class="cmb_metabox_description">', $this->description, '</span>'; ?>
			
			<?php else : ?>

				<?php echo '<div class="hidden"><input disabled class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="" /><span class="cmb_metabox_description">', $this->description, '</span></div>'; ?>

				<div style="position: relative">
				
				<?php if ( is_array( $this->value ) ) :?>
				
					<span class="cmb_oembed"><?php echo $this->value['object'] ?></span>
					<input type="hidden" name="<?php echo $this->name ?>" value="<?php echo esc_attr( serialize( $this->value ) ); ?>" />
				
				<?php else : ?>

					<span class="cmb_oembed"><?php echo $this->value ?></span>
					<input type="hidden" name="<?php echo $this->name ?>" value="<?php echo esc_attr( $this->value ); ?>" />
					
				<?php endif; ?>
					
					<a href="#" class="cmb_remove_file_button" onclick="jQuery( this ).closest('div').prev().removeClass('hidden').find('input').first().removeAttr('disabled')">Remove</a>
				</div>

			<?php endif; ?>
		</p>
	
	<?php }

	public function parse_save_value() {
	
		$args['cmb_oembed'] = true;
	
		if ( ! empty( $this->args['height'] ) )
			$args['height'] = $this->args['height'];
		
		if ( strpos( $this->value, 'http' ) === 0 )
			$this->value = wp_oembed_get( $this->value, $args );

	}

}

function cmb_oembed_thumbnail( $return, $data, $url ) {

	$backtrace = debug_backtrace();

	if ( $data->type == 'video' && ! empty( $backtrace[5]['args'][1]['cmb_oembed'] ) )
		return '<a href=""><img src="' . $data->thumbnail_url . '" /><span class="video_embed">' . $return . '</span></a>';

	return $return;

}

add_filter( 'oembed_dataparse', 'cmb_oembed_thumbnail', 10, 3 );

/**
 * Standard text field.
 *
 * Args:
 *  - int "rows" - number of rows in the <textarea>
 */
class CMB_Textarea_Field extends CMB_Field {

	public function html() {

		?>
		<p>
				<textarea rows="<?php echo !empty( $this->args['rows'] ) ? $this->args['rows'] : 4 ?>" name="<?php echo $this->name ?>"><?php echo $this->value ?></textarea>
		</p>
		<?php
	}
}

/**
 * Code style text field.
 *
 * Args:
 *  - int "rows" - number of rows in the <textarea>
 */
class CMB_Textarea_Field_Code extends CMB_Field {

	public function html() {

		?>
		<p>
			<textarea class="cmb_textarea_code" rows="<?php echo !empty( $this->args['rows'] ) ? $this->args['rows'] : 4 ?>" name="<?php echo $this->name ?>"><?php echo $this->value ?></textarea>
		</p>
		<?php
	}
}

/**
 *  Colour picker
 *
 */
class CMB_Color_Picker extends CMB_Field {

	public function html() {

		?>
		<p>
			<input class="cmb_colorpicker cmb_text_small" type="text" name="<?php echo $this->name; ?>" value="<?php echo $this->get_value() ?>" /><span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}

}

/**
 * Standard select field.
 *
 */
class CMB_Select extends CMB_Field {

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_script( 'select2', CMB_URL . 'js/select2/select2.js', array( 'jquery' ) );
	}

	public function enqueue_styles() {

		parent::enqueue_styles();

		wp_enqueue_style( 'select2', CMB_URL . 'js/select2/select2.css' );
	}

	public function html() {
		if ( $this->has_data_delegate() )
			$this->args['options'] = $this->get_delegate_data();

		$id = 'select-' . rand( 0, 1000 );
		?>
		<p>
			<select id="<?php echo $id ?>" name="<?php echo $this->name ?>"> >
				<?php foreach ( $this->args['options'] as $value => $name ): ?>
				   <option <?php selected( $this->value, $value ) ?> value="<?php echo $value; ?>"><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<script>
			jQuery( document ).ready( function() {
				jQuery( '#<?php echo $id ?>' ).select2();
			} );
		</script>
		<?php
	}
}

/**
 * Standard radio field.
 *
 * Args:
 *  - bool "inline" - display the radio buttons inline
 */
class CMB_Radio_Field extends CMB_Field {

	public function html() {
		?>
		<p>
			<?php foreach ( $this->values as $key => $value ): ?>
				<input type="radio" name="<?php echo $this->name ?>" value="<?php echo $value; ?>" <?php checked( $value, $this->get_value() ); ?> />
			<?php endforeach; ?>
		</p>
		<?php
	}
}

/**
 * Standard checkbox field.
 *
 */
class CMB_Checkbox extends CMB_Field {

	public function parse_save_values() {

		$name = str_replace( '[]', '', $this->name );
		foreach ( $this->values as $key => $value )
			$this->values[$key] = isset( $_POST['checkbox_' . $name][$key] ) ? $_POST['checkbox_' . $name][$key] : null;
	}

	public function html() {
		?>
		<p>
			<input type="checkbox" name="checkbox_<?php echo $this->name ?>" value="1" <?php checked( $this->get_value() ); ?> /><span class="cmb_metabox_description"><?php echo $this->description ?></span>
			<input type="hidden" name="<?php echo $this->name ?>" value="1" />
		</p>
		<?php
	}
}


/**
 * Standard title used as a splitter.
 *
 */
class CMB_Title extends CMB_Field {

	public function html() {
		?>
		<p>
			<h5 class="cmb_metabox_title"><?php echo $this->title; ?></h5>
			<span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

/**
 * wysiwyg field.
 *
 */
class CMB_wysiwyg extends CMB_Field {

	public function html() {

		?>
		<p>
			<?php wp_editor( $this->get_value(), $this->name, $this->args['options'] );?><span class="cmb_metabox_description"><?php echo $this->description ?></span>
		</p>
		<?php
	}
}

class CMB_Taxonomy extends CMB_Select {

	public function __construct() {
		$args = func_get_args();
		call_user_func_array( array( 'parent', '__construct' ), $args );

		$this->args['data_delegate'] = array( $this, 'get_delegate_data' );
	}

	public function get_delegate_data() {
		$terms = $this->get_terms();
		$term_options = array();

		foreach ( $terms as $term )
			$term_options[$term->term_id] = $term->name;

		return $term_options;
	}

	private function get_terms() {

		return get_terms( $this->args['taxonomy'], array( 'hide_empty' => $this->args['hide_empty'] ) );
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

		echo '<strong>' . $this->args['name'] . '</strong>';

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

				

			</script>

		<?php self::$added_js = true; endif;
	}

	public function html() {

		$field = $this->args;
		$value = $this->value;
		$fields = array();
				
		foreach ( $this->args['fields'] as $f ) {
				$field_value = isset( $this->value[$f['id']] ) ? $this->value[$f['id']] : '';
				$f['uid'] = $field['id'] . '[' . $f['id'] . ']';

				// If it's cloneable , make it an array
				if ( $field['repeatable'] == true )
					$f['uid'] .= '[]';

				$class = _cmb_field_class_for_type( $f['type'] );
				$f['show_label'] = true;
				
				// Todo support for repeatble fields in groups
			$fields[] = new $class( $f['uid'], $f['name'], array( $field_value ), $f );
		}
		?>
		<div class="group <?php echo !empty( $field['repeatable'] ) ? 'cloneable' : '' ?>">

			<?php if ( ! empty( $this->args['name'] ) ) : ?>			
				<h2 class="group-name"><?php echo $this->args['name'] ?></h2>
			<?php endif; ?>

			<?php if ( $this->args['repeatable'] ) : ?>
				<a class="delete-field button" style="position: absolute; top: -3px; right: -3px">X</a>
			<?php endif; ?>

			<?php CMB_Meta_Box::layout_fields( $fields ); ?>

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
				$field = new $class( $construct_field['id'], $construct_field['name'], (array) $values[$construct_field['id']][$key], $construct_field );

				$field->parse_save_value();


				$meta[$construct_field['id']] = $field->get_value();
			}

			if( $this->isNotEmptyArray( $meta ) )
				$this->values[] = $meta;

		}

		parent::parse_save_values();

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