<?php

/**
 * Abstract class for all fields.
 * Subclasses need only override html()
 *
 * @abstract
 */
abstract class CMB_Field {

	public $value;
	public $field_index = 0;

	public function __construct( $name, $title, array $values, $args = array() ) {

		$this->id 		= $name;
		$this->name		= $name . '[]';
		$this->title 	= $title;
		
		$this->args		= wp_parse_args( $args, array(
				'repeatable' 			=> false,
				'std'        			=> '',
				'default'    			=> '',
				'show_label' 			=> false,
				'taxonomy'   			=> '',
				'hide_empty' 			=> false,
				'data_delegate' 		=> null,
				'options'				=> array(),
				'cols' 					=> '12',
				'style' 				=> '',
				'class'					=> '',
				'readonly'				=> false,
				'disabled'				=> false
			)
		);

		if ( ! empty( $this->args['std'] ) && empty( $this->args['default'] ) ) {
			$this->args['default'] = $this->args['std'];
			_deprecated_argument( 'CMB_Field', "'std' is deprecated, use 'default instead'", '0.9' );
		}

		if ( ! empty( $this->args['options'] ) && is_array( reset( $this->args['options'] ) ) ) {

			$re_format = array();

			foreach ( $this->args['options'] as $option )
				$re_format[$option['value']] = $option['name'];

			$this->args['options'] = $re_format;
		}

		// If the field has a custom value populator callback
		if ( ! empty( $args['values_callback'] ) )
			$this->values = call_user_func( $args['values_callback'], get_the_id() );
		else
			$this->values = $values;

		$this->value = reset( $this->values );

		$this->description = ! empty( $this->args['desc'] ) ? $this->args['desc'] : '';

	}

	/**
	 * Enqueue all scripts required by the field.
	 *
	 * @uses wp_enqueue_script()
	 */
	public function enqueue_scripts() {
	}

	/**
	 * Enqueue all styles required by the field.
	 *
	 * @uses wp_enqueue_style()
	 */
	public function enqueue_styles() {
	}

	/**
	 * Output the field input ID attribute.
	 * 
	 * If multiple inputs are required for a single field, 
	 * use the append parameter to add unique identifier.
	 * 
	 * @param  string $append
	 * @return null
	 */
	public function id_attr( $append = null ) {

		printf( 'id="%s"', esc_attr( $this->get_the_id_attr( $append ) ) );
		
	}
	
	/**
	 * Output the for attribute for the field.
	 *
	 * 
	 * 
	 * If multiple inputs are required for a single field, 
	 * use the append parameter to add unique identifier.
	 * 
	 * @param  string $append
	 * @return null
	 */
	public function get_the_id_attr( $append = null ) {

		$id = $this->id;

		if ( isset( $this->parent ) ) {
			$parent_id = preg_replace( '/cmb\-field\-(\d|x)+/', 'cmb-group-$1', $this->parent->get_the_id_attr() );
			$id = $parent_id . '[' . $id . ']';
		}

		$id .= '-cmb-field-' . $this->field_index;

		if ( ! is_null( $append ) )
			$id .= '-' . $append;

		$id = str_replace( array( '[', ']', '--' ), '-', $id );

		return $id;
		
	}

	/**
	 * Return the field input ID attribute value.
	 *
	 * If multiple inputs are required for a single field, 
	 * use the append parameter to add unique identifier.
	 * 
	 * @param  string $append 
	 * @return string id attribute value.
	 */
	public function for_attr( $append = null ) {

		printf( 'for="%s"', esc_attr( $this->get_the_id_attr( $append ) ) );

	}

	public function name_attr( $append = null ) {
	
		printf( 'name="%s"', esc_attr( $this->get_the_name_attr( $append ) ) );
	
	}

	public function get_the_name_attr( $append = null ) {

		$name = str_replace( '[]', '', $this->name );

		if ( isset( $this->parent ) ) {
			$parent_name = preg_replace( '/cmb\-field\-(\d|x)+/', 'cmb-group-$1', $this->parent->get_the_name_attr() );
			$name = $parent_name . '[' . $name . ']';
		}

		$name .= "[cmb-field-$this->field_index]";

		if ( ! is_null( $append ) )
			$name .= $append;

		return $name;

	}

	public function class_attr( $classes = '' ) {

		if ( $classes = implode( ' ', array_map( 'sanitize_html_class', array_filter( array_unique( explode( ' ', $classes . ' ' . $this->args['class'] ) ) ) ) ) ) { ?>

			class="<?php echo esc_attr( $classes ); ?>"

		<?php }

	}

	/**
	 * Get JS Safe ID.
	 *
	 * For use as a unique field identifier in javascript.
	 */
	public function get_js_id() {

		return str_replace( array( '-', '[', ']', '--' ),'_', $this->get_the_id_attr() ); // JS friendly ID
	
	}

	public function boolean_attr( $attrs = array() ) {

		if ( $this->args['readonly'] )
			$attrs[] = 'readonly';

		if ( $this->args['disabled'] )
			$attrs[] = 'disabled';

		$attrs = array_filter( array_unique( $attrs ) );

		foreach ( $attrs as $attr )
			echo esc_html( $attr ) . '="' . esc_attr( $attr ) . '"';

	}

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
	   return ( $this->value || $this->value === '0' ) ? $this->value : $this->args['default'];
	}

	public function &get_values() {
		return $this->values;
	}

	public function set_values( array $values ) {

		$this->values = $values;

		unset( $this->value );

	}

	public function parse_save_values() {}

	public function parse_save_value() {}

	/**
	 * @todo this surely only works for posts
	 * @todo why do values need to be passed in, they can already be passed in on construct
	 */
	public function save( $post_id, $values ) {

		// Don't save readonly values.
		if ( $this->args['readonly'] )
			return;

		$this->values = $values;
		$this->parse_save_values();

		// Allow override from args
		if ( ! empty( $this->args['save_callback'] ) ) {

			call_user_func( $this->args['save_callback'], $this->values, $post_id );

			return;

		}

		// If we are not on a post edit screen
		if ( ! $post_id )
			return;

		delete_post_meta( $post_id, $this->id );

		foreach( $this->values as $v ) {

			$this->value = $v;
			$this->parse_save_value();

			if ( $this->value || $this->value === '0' )
				add_post_meta( $post_id, $this->id, $this->value );

		}
	}

	public function title() {

		if ( $this->title ) { ?>

			<div class="field-title">
				<label <?php $this->for_attr(); ?>>
					<?php echo esc_html( $this->title ); ?>
				</label>
			</div>

		<?php }

	}

	public function description() {

		if ( $this->description ) { ?>

			<div class="cmb_metabox_description"><?php echo wp_kses_post( $this->description ); ?></div>

		<?php }

	}

	public function display() {

		// if there are no values and it's not repeateble, we want to do one with empty string
		if ( ! $this->get_values() && ! $this->args['repeatable'] )
			$values = array( '' );
		else
			$values = $this->get_values();

		$this->title();

		$this->description();

		$i = 0;
		foreach ( $values as $key => $value ) {

			$this->field_index = $i;
			$this->value = $value; ?>

			<div class="field-item" data-class="<?php echo esc_attr( get_class($this) ) ?>" style="position: relative; <?php echo esc_attr( $this->args['style'] ); ?>">

			<?php if ( $this->args['repeatable'] ) : ?>
				<button class="cmb-delete-field" title="Remove field"><span class="cmb-delete-field-icon">&times;</span></button>
			<?php endif; ?>

			<?php $this->html(); ?>

			</div>

		<?php 

			$i++;

		}

		// Insert a hidden one if it's repeatable
		if ( $this->args['repeatable'] ) {

			$this->field_index = 'x'; // x used to distinguish hidden fields.
			$this->value = ''; ?>

			<div class="field-item hidden" data-class="<?php echo esc_attr( get_class($this) ) ?>" style="position: relative; <?php echo esc_attr( $this->args['style'] ); ?>">

			<?php if ( $this->args['repeatable'] ) : ?>
				<button class="cmb-delete-field" title="Remove field"><span class="cmb-delete-field-icon">&times;</span> Remove Group</button>
			<?php endif; ?>

			<?php $this->html(); ?>

			</div>

			<button class="button repeat-field"><?php esc_html_e( 'Add New', 'cmb' ); ?></button>

		<?php }

	}

}

/**
 * Standard text field.
 *
 * @extends CMB_Field
 */
class CMB_Text_Field extends CMB_Field {

	public function html() { ?>

		<input type="text" <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->get_value() ); ?>" />

	<?php }
}

class CMB_Text_Small_Field extends CMB_Text_Field {

	public function html() { 

		$this->args['class'] .= ' cmb_text_small';	

		parent::html();

	}
}

/**
 * Field for image upload / file updoad.
 *
 * @todo ability to set image size (preview image) from caller
 */
class CMB_File_Field extends CMB_Field {

	function enqueue_scripts() {

		parent::enqueue_scripts();
		wp_enqueue_media();
		wp_enqueue_script( 'cmb-file-upload', trailingslashit( CMB_URL ) . 'js/file-upload.js', array( 'jquery', 'cmb-scripts' ) );
		
	}

	public function html() { 

		$args = wp_parse_args( $this->args, array(
			'library-type' => array( 'video', 'audio', 'text', 'application' )
		) );

		if ( $this->get_value() ) {
			$src = wp_mime_type_icon( $this->get_value() );
			$size = getimagesize($src);
			$icon_img = '<img src="' . $src . '" ' . $size[3] . ' />';
		}

		$data_type = ( ! empty( $args['library-type'] ) ? implode( ',', $args['library-type'] ) : null );

		?>

		<div class="cmb-file-wrap" <?php echo 'data-type="' . esc_attr( $data_type ) . '"'; ?>>

			<div class="cmb-file-wrap-placeholder"></div>

			<button class="button cmb-file-upload <?php echo esc_attr( $this->get_value() ) ? 'hidden' : '' ?>">
				<?php esc_html_e( 'Add File', 'cmb' ); ?>
			</button>

			<div class="cmb-file-holder type-file <?php echo $this->get_value() ? '' : 'hidden'; ?>">

				<?php if ( $this->get_value() ) : ?>

					<?php if ( isset( $icon_img ) ) echo $icon_img; ?>

					<div class="cmb-file-name">
						<strong><?php echo esc_html( basename( get_attached_file( $this->get_value() ) ) ); ?></strong>
					</div>

				<?php endif; ?>

			</div>

			<button class="cmb-remove-file button <?php echo $this->get_value() ? '' : 'hidden'; ?>">
				<?php esc_html_e( 'Remove', 'cmb' ); ?>
			</button>

			<input type="hidden" class="cmb-file-upload-input" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>" />

		</div>

	<?php }

}

class CMB_Image_Field extends CMB_File_Field {

	public function html() {

		$args = $this->args = wp_parse_args( $this->args, array(
			'size' => 'thumbnail',
			'library-type' => array( 'image' ),
			'show_size' => false
		) );

		if ( $this->get_value() )
			$image = wp_get_attachment_image_src( $this->get_value(), $args['size'], true );

		// Convert size arg to array of width, height, crop. 
		$size = $this->parse_image_size( $args['size'] );

		// Inline styles.
		$styles              = sprintf( 'width: %1$dpx; height: %2$dpx; line-height: %2$dpx', intval( $size['width'] ), intval( $size['height'] ) );
		$placeholder_styles  = sprintf( 'width: %dpx; height: %dpx;', intval( $size['width'] ) - 8, intval( $size['height'] ) - 8 );
		
		$data_type = ( ! empty( $args['library-type'] ) ? implode( ',', $args['library-type'] ) : null );

		?>

		<div class="cmb-file-wrap" style="<?php echo esc_attr( $styles ); ?>" data-type="<?php echo esc_attr( $data_type ); ?>">

			<div class="cmb-file-wrap-placeholder" style="<?php echo esc_attr( $placeholder_styles ); ?>">

				<?php if ( $args['show_size'] ) : ?>
					<span class="dimensions">
						<?php printf( '%dpx &times; %dpx', intval( $size['width'] ), intval( $size['height'] ) ); ?>
					</span>
				<?php endif; ?>

			</div>

			<button class="button cmb-file-upload <?php echo esc_attr( $this->get_value() ) ? 'hidden' : '' ?>" data-nonce="<?php echo wp_create_nonce( 'cmb-file-upload-nonce' ); ?>">
				<?php esc_html_e( 'Add Image', 'cmb' ); ?>
			</button>

			<div class="cmb-file-holder type-img <?php echo $this->get_value() ? '' : 'hidden'; ?>" data-crop="<?php echo (bool) $size['crop']; ?>">

				<?php if ( ! empty( $image ) ) : ?>
					<img src="<?php echo esc_url( $image[0] ); ?>" width="<?php echo intval( $image[1] ); ?>" height="<?php echo intval( $image[2] ); ?>" />
				<?php endif; ?>

			</div>

			<button class="cmb-remove-file button <?php echo $this->get_value() ? '' : 'hidden'; ?>">
				<?php esc_html_e( 'Remove', 'cmb' ); ?>
			</button>

			<input type="hidden" class="cmb-file-upload-input" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>" />

		</div>

	<?php }

	/**
	 * Parse the size argument to get pixel width, pixel height and crop information.
	 * 
	 * @param  string $size
	 * @return array width, height, crop
	 */
	private function parse_image_size( $size ) {
		
		// Handle string for built-in image sizes
		if ( is_string( $size ) && in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
			return array(
				'width'  => get_option( $size . '_size_w' ),
				'height' => get_option( $size . '_size_h' ),
				'crop'   => get_option( $size . '_crop' )
			);
		}

		// Handle string for additional image sizes
		global $_wp_additional_image_sizes;
		if ( is_string( $size ) && isset( $_wp_additional_image_sizes[$size] ) ) {
			return array(
				'width'  => $_wp_additional_image_sizes[$size]['width'],
				'height' => $_wp_additional_image_sizes[$size]['height'],
				'crop'   => $_wp_additional_image_sizes[$size]['crop']
			);
		}

		// Handle default WP size format. 
		if ( is_array( $size ) && isset( $size[0] ) && isset( $size[1] ) )
			$size = array( 'width' => $size[0], 'height' => $size[0] );

		return wp_parse_args( $size, array(
			'width'  => get_option( 'thumbnail_size_w' ),
			'height' => get_option( 'thumbnail_size_h' ),
			'crop'   => get_option( 'thumbnail_crop' )
		) );

	}

	/**
	 * Ajax callback for outputing an image src based on post data.
	 *
	 * @return null
	 */
	static function request_image_ajax_callback() {
		
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'cmb-file-upload-nonce' ) ) )
			return;

		$id = intval( $_POST['id'] );

		$size = array(
			intval( $_POST['width'] ),
			intval( $_POST['height'] ),
			'crop' => (bool) $_POST['crop']
		);

		$image = wp_get_attachment_image_src( $id, $size );
		echo reset( $image );

		die(); // this is required to return a proper result
	}

}
add_action( 'wp_ajax_cmb_request_image', array( 'CMB_Image_Field', 'request_image_ajax_callback' ) );

/**
 * Standard text meta box for a URL.
 *
 */
class CMB_URL_Field extends CMB_Field {

	public function html() { ?>

		<input type="text" <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_url code' ); ?> <?php $this->name_attr(); ?> value="<?php echo esc_attr( esc_url( $this->value ) ); ?>" />

	<?php }
}

/**
 * Date picker box.
 *
 */
class CMB_Date_Field extends CMB_Field {

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_style( 'cmb-jquery-ui', trailingslashit( CMB_URL ) . 'css/vendor/jquery-ui/jquery-ui.css', '1.10.3' );

		wp_enqueue_script( 'cmb-datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'cmb-scripts' ) );
	}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>" />

	<?php }
}

class CMB_Time_Field extends CMB_Field {

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_style( 'cmb-jquery-ui', trailingslashit( CMB_URL ) . 'css/vendor/jquery-ui/jquery-ui.css', '1.10.3' );

		wp_enqueue_script( 'cmb-timepicker', trailingslashit( CMB_URL ) . 'js/jquery.timePicker.min.js', array( 'jquery', 'cmb-scripts' ) );
		wp_enqueue_script( 'cmb-datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'cmb-scripts' ) );
	}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_timepicker' ); ?> type="text" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>"/>

	<?php }

}

/**
 * Date picker for date only (not time) box.
 *
 */
class CMB_Date_Timestamp_Field extends CMB_Field {

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_style( 'cmb-jquery-ui', trailingslashit( CMB_URL ) . 'css/jquery-ui.css', '1.10.3' );

		wp_enqueue_script( 'cmb-timepicker', trailingslashit( CMB_URL ) . 'js/jquery.timePicker.min.js', array( 'jquery', 'cmb-scripts' ) );		
		wp_enqueue_script( 'cmb-datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'cmb-scripts' ) );

	}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" <?php $this->name_attr(); ?>  value="<?php echo $this->value ? esc_attr( date( 'm\/d\/Y', $this->value ) ) : '' ?>" />

	<?php }

	public function parse_save_values() {
		
		foreach( $this->values as &$value )
			$value = strtotime( $value );

		sort( $this->values );
	
	}

}

/**
 * Date picker for date and time (seperate fields) box.
 *
 */
class CMB_Datetime_Timestamp_Field extends CMB_Field {
	
	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_style( 'cmb-jquery-ui', trailingslashit( CMB_URL ) . 'css/jquery-ui.css', '1.10.3' );
		
		wp_enqueue_script( 'cmb-timepicker', trailingslashit( CMB_URL ) . 'js/jquery.timePicker.min.js', array( 'jquery', 'cmb-scripts' ) );
		wp_enqueue_script( 'cmb-datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'cmb-scripts' ) );
	}

	public function html() { ?>		

		<input <?php $this->id_attr('date'); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" <?php $this->name_attr( '[date]' ); ?>  value="<?php echo $this->value ? esc_attr( date( 'm\/d\/Y', $this->value ) ) : '' ?>" />
		<input <?php $this->id_attr('time'); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_timepicker' ); ?> type="text" <?php $this->name_attr( '[time]' ); ?> value="<?php echo $this->value ? esc_attr( date( 'h:i A', $this->value ) ) : '' ?>" />

	<?php }

	public function parse_save_values() {

		// Convert all [date] and [time] values to a unix timestamp.
		// If date is empty, assume delete. If time is empty, assume 00:00. 
		foreach( $this->values as $key => &$value ) {
			if ( empty( $value['date'] ) )
				unset( $this->values[$key] );
			else
				$value = strtotime( $value['date'] . ' ' . $value['time'] );
		}

		$this->values = array_filter( $this->values );
		sort( $this->values );

		parent::parse_save_values();

	}

}

/**
 * Standard text field.
 *
 * Args:
 *  - int "rows" - number of rows in the <textarea>
 */
class CMB_Textarea_Field extends CMB_Field {

	public function html() { ?>

		<textarea <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> rows="<?php echo ! empty( $this->args['rows'] ) ? esc_attr( $this->args['rows'] ) : 4; ?>" <?php $this->name_attr(); ?>><?php echo esc_html( $this->value ); ?></textarea>

	<?php }

}

/**
 * Code style text field.
 *
 * Args:
 *  - int "rows" - number of rows in the <textarea>
 */
class CMB_Textarea_Field_Code extends CMB_Textarea_Field {

	public function html() {

		$this->args['class'] .= ' code';	

		parent::html();

	}

}

/**
 *  Colour picker
 *
 */
class CMB_Color_Picker extends CMB_Field {

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_script( 'cmb-colorpicker', trailingslashit( CMB_URL ) . 'js/field.colorpicker.js', array( 'jquery', 'wp-color-picker', 'cmb-scripts' ) );
		wp_enqueue_style( 'wp-color-picker' );
	}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_colorpicker cmb_text_small' ); ?> type="text" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->get_value() ); ?>" />

	<?php }

}

/**
 * Standard radio field.
 *
 * Args:
 *  - bool "inline" - display the radio buttons inline
 */
class CMB_Radio_Field extends CMB_Field {

	public function html() {

		if ( $this->has_data_delegate() )
			$this->args['options'] = $this->get_delegate_data(); ?>

			<?php foreach ( $this->args['options'] as $key => $value ): ?>

			<input <?php $this->id_attr( 'item-' . $key ); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> type="radio" <?php $this->name_attr(); ?>  value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, $this->get_value() ); ?> />
			<label <?php $this->for_attr( 'item-' . $key ); ?> style="margin-right: 20px;">
				<?php echo esc_html( $value ); ?>
			</label>

			<?php endforeach; ?>

	<?php }

}

/**
 * Standard checkbox field.
 *
 */
class CMB_Checkbox extends CMB_Field {

	public function title() {}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> type="checkbox" <?php $this->name_attr(); ?>  value="1" <?php checked( $this->get_value() ); ?> />
		<label <?php $this->for_attr(); ?>><?php echo esc_html( $this->args['name'] ); ?></label>

	<?php }

}


/**
 * Standard title used as a splitter.
 *
 */
class CMB_Title extends CMB_Field {

	public function title() {
		?>

		<div class="field-title">
			<h2>
				<?php echo esc_html( $this->title ); ?>
			</h2>
		</div>

		<?php

	}

	public function html() {}

}

/**
 * wysiwyg field.
 *
 */
class CMB_wysiwyg extends CMB_Field {

	function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_script( 'cmb-wysiwyg', trailingslashit( CMB_URL ) . 'js/field-wysiwyg.js', array( 'jquery', 'cmb-scripts' ) );
	}

	public function html() { 

		$id   = $this->get_the_id_attr();
		$name = $this->get_the_name_attr();		

		$field_id = $this->get_js_id();

		printf( '<div class="cmb-wysiwyg" data-id="%s" data-name="%s" data-field-id="%s">', $id, $name, $field_id );
	
		if ( $this->is_placeholder() ) 	{

			// For placeholder, output the markup for the editor in a JS var.
			ob_start();
			$this->args['options']['textarea_name'] = 'cmb-placeholder-name-' . $field_id;
			wp_editor( '', 'cmb-placeholder-id-' . $field_id, $this->args['options'] );
			$editor = ob_get_clean();
			$editor = str_replace( array( "\n", "\r" ), "", $editor );
			$editor = str_replace( array( "'" ), '"', $editor );

			?>
			
			<script>
				if ( 'undefined' === typeof( cmb_wysiwyg_editors ) ) 
					var cmb_wysiwyg_editors = {};
				cmb_wysiwyg_editors.<?php echo $field_id; ?> = '<?php echo $editor; ?>';
			</script>

			<?php
		
		} else {

			$this->args['options']['textarea_name'] = $name;
			echo wp_editor( $this->get_value(), $id, $this->args['options'] );
		
		}

		echo '</div>';

	}

	/**
	 * Check if this is a placeholder field.
	 * Either the field itself, or because it is part of a repeatable group.
	 * 
	 * @return bool
	 */
	public function is_placeholder() {

		if ( isset( $this->parent ) && ! is_int( $this->parent->field_index ) )
			return true;

		else return ! is_int( $this->field_index );

	}

}

/**
 * Standard select field.
 *
 * @supports "data_delegate"
 * @args
 *     'options'     => array Array of options to show in the select, optionally use data_delegate instead
 *     'allow_none'   => bool Allow no option to be selected (will palce a "None" at the top of the select)
 *     'multiple'     => bool whether multiple can be selected
 */
class CMB_Select extends CMB_Field {

	public function __construct() {
		
		$args = func_get_args();

		call_user_func_array( array( 'parent', '__construct' ), $args );

		$this->args = wp_parse_args( $this->args, array( 'multiple' => false ) );

	}

	public function parse_save_values(){

		if ( isset( $this->parent ) && isset( $this->args['multiple'] ) && $this->args['multiple'] )
			$this->values = array( $this->values );

	}

	public function get_options() {

		if ( $this->has_data_delegate() )
			$this->args['options'] = $this->get_delegate_data();

		return $this->args['options'];
	}

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_script( 'select2', trailingslashit( CMB_URL ) . 'js/select2/select2.js', array( 'jquery' ) );
		wp_enqueue_script( 'field-select', trailingslashit( CMB_URL ) . 'js/field.select.js', array( 'jquery', 'select2', 'cmb-scripts' ) );
	}

	public function enqueue_styles() {

		parent::enqueue_styles();

		wp_enqueue_style( 'select2', trailingslashit( CMB_URL ) . 'js/select2/select2.css' );
	}

	public function html() {

		if ( $this->has_data_delegate() )
			$this->args['options'] = $this->get_delegate_data();

		$this->output_field();
		
		$this->output_script();

	}

	public function output_field() {

		$val = (array) $this->get_value();

		$name = $this->get_the_name_attr();
		$name .= ! empty( $this->args['multiple'] ) ? '[]' : null;
		
		?>

		<select 
			<?php $this->id_attr(); ?> 
			<?php $this->boolean_attr(); ?> 
			<?php printf( 'name="%s"', esc_attr( $name ) ); ?> 
			<?php printf( 'data-field-id="%s" ', esc_attr( $this->get_js_id() ) ); ?> 
			<?php echo ! empty( $this->args['multiple'] ) ? 'multiple' : '' ?> 
			class="cmb_select" 
			style="width: 100%" 
		>

			<?php if ( ! empty( $this->args['allow_none'] ) ) : ?>
				<option value=""><?php echo esc_html_x( 'None', 'select field', 'cmb' ) ?></option>
			<?php endif; ?>

			<?php foreach ( $this->args['options'] as $value => $name ): ?>
			   <option <?php selected( in_array( $value, $val ) ) ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>

		</select>

		<?php 
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			(function($) {
				
				var options = {};
				
				options.placeholder = <?php echo json_encode( __( 'Type to search', 'cmb' ) ) ?>;
				options.allowClear  = true;

				if ( 'undefined' === typeof( window.cmb_select_fields ) )
					window.cmb_select_fields = {};
				
				var id = <?php echo json_encode( $this->get_js_id() ); ?>;
				window.cmb_select_fields[id] = options;

			})( jQuery );

		</script>

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
 * Post Select field.
 *
 * @supports "data_delegate"
 * @args
 *     'options'     => array Array of options to show in the select, optionally use data_delegate instead
 *     'allow_none'   => bool Allow no option to be selected (will palce a "None" at the top of the select)
 *     'multiple'     => bool whether multiple can be selected
 */
class CMB_Post_Select extends CMB_Select {

	public function __construct() {

		$args = func_get_args();

		call_user_func_array( array( 'parent', '__construct' ), $args );

		$this->args = wp_parse_args( $this->args, array( 'use_ajax' => false, 'ajax_url' => '' ) );

		$this->args['query'] = isset( $this->args['query'] ) ? $this->args['query'] : array();

		if ( ! $this->args['use_ajax'] ) {
			
			$this->args['data_delegate'] = array( $this, 'get_delegate_data' );

		} else {

			$this->args['ajax_url'] = admin_url( 'admin-ajax.php' );
			$this->args['ajax_args'] = wp_parse_args( $this->args['query'] );

		}

	}

	public function get_delegate_data() {
		
		$data = array();

		foreach ( $this->get_posts() as $post_id )
			$data[$post_id] = get_the_title( $post_id );

		return $data;

	}

	private function get_posts() {

		$this->args['query']['fields'] = 'ids';
		$query = new WP_Query( $this->args['query'] );

		return isset( $query->posts ) ? $query->posts : array();

	}

	public function parse_save_value() {

		// AJAX multi select2 data is submitted as a string of comma separated post IDs.
		// If empty, set to false instead of empty array to ensure the meta entry is deleted.
		if ( $this->args['ajax_url'] && $this->args['multiple'] ) {
			$this->value = ( ! empty( $this->value ) ) ? explode( ',', $this->value ) : false;
		}

	}

	public function output_field() {
			
		// If AJAX, must use input type not standard select. 
		if ( $this->args['ajax_url'] ) :

			?>

			<input 
				<?php $this->id_attr(); ?> 
				<?php printf( 'value="%s" ', esc_attr( implode( ',' , (array) $this->value ) ) ); ?>
				<?php printf( 'name="%s"', esc_attr( $this->get_the_name_attr() ) ); ?> 
				<?php printf( 'data-field-id="%s" ', esc_attr( $this->get_js_id() ) ); ?> 
				<?php $this->boolean_attr(); ?> 
				class="cmb_select" 
				style="width: 100%" 
			/>

			<?php 

		else :

			parent::output_field();

		endif;

	}

	public function output_script() {
		
		parent::output_script();

		?>

		<script type="text/javascript">

			(function($) {

				if ( 'undefined' === typeof( window.cmb_select_fields ) )
					return false; 
				
				// Get options for this field so we can modify it.
				var id = <?php echo json_encode( $this->get_js_id() ); ?>;
				var options = window.cmb_select_fields[id];

				<?php if ( $this->args['ajax_url'] && $this->args['multiple'] ) : ?>
					// The multiple setting is required when using ajax (because an input field is used instead of select)
					options.multiple = true;
				<?php endif; ?>

				<?php if ( $this->args['ajax_url'] && ! empty( $this->value ) ) : ?>
				
					options.initSelection = function( element, callback ) {
						
						var data = [];

						<?php if ( $this->args['multiple'] ) : ?>
						
							<?php foreach ( (array) $this->value as $post_id ) : ?>
								data.push( <?php echo sprintf( '{ id: %d, text: "%s" }', $post_id, get_the_title( $post_id ) ); ?> );
							<?php endforeach; ?>
						
						<?php else : ?>
						
							data = <?php echo sprintf( '{ id: %d, text: "%s" }', $this->value, get_the_title( $this->value ) ); ?>;
						
						<?php endif; ?>

						callback( data );
						
					};

				<?php endif; ?>

				<?php if ( $this->args['ajax_url'] ) : ?>
					
					var ajaxData = {
						action  : 'cmb_post_select',
						post_id : '<?php echo intval( get_the_id() ); ?>', // Used for user capabilty check.
						nonce   : <?php echo json_encode( wp_create_nonce( 'cmb_select_field' ) ); ?>,
						query   : <?php echo json_encode( $this->args['ajax_args'] ); ?>
					};
					
					options.ajax = {
						url: <?php echo json_encode( esc_url( $this->args['ajax_url'] ) ); ?>,
						type: 'POST',
						dataType: 'json',
						data: function( term, page ) {
							ajaxData.query.s = term;
							ajaxData.query.paged = page;
							return ajaxData;
						},
						results : function( results, page ) {
							var postsPerPage = ajaxData.query.posts_per_page = ( 'posts_per_page' in ajaxData.query ) ? ajaxData.query.posts_per_page : ( 'showposts' in ajaxData.query ) ? ajaxData.query.showposts : 10;
							var isMore = ( page * postsPerPage ) < results.total; 
		            		return { results: results.posts, more: isMore };
						}
					}

				<?php endif; ?>			

			})( jQuery );

		</script>

		<?php
	}

}

// TODO this should be in inside the class
function cmb_ajax_post_select() {
	
	$post_id = ! empty( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : false;
	$nonce   = ! empty( $_POST['nonce'] ) ? $_POST['nonce'] : false;
	$args    = ! empty( $_POST['query'] ) ? $_POST['query'] : array();

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cmb_select_field' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		echo json_encode( array( 'total' => 0, 'posts' => array() ) );
		exit;
	}

	$args['fields'] = 'ids'; // Only need to retrieve post IDs.

	$query = new WP_Query( $args );
	
	$json = array( 'total' => $query->found_posts, 'posts' => array() );

	foreach ( $query->posts as $post_id )
		array_push( $json['posts'], array( 'id' => $post_id, 'text' => get_the_title( $post_id ) ) );

	echo json_encode( $json );

	exit;

}
add_action( 'wp_ajax_cmb_post_select', 'cmb_ajax_post_select' );

/**
 * Field to group child fieids
 * pass $args[fields] array for child fields
 * pass $args['repeatable'] for cloing all child fields (set)
 *
 * @todo remove global $post reference, somehow
 */
class CMB_Group_Field extends CMB_Field {

	static $added_js;
	private $fields = array();

	function __construct() {

		$args = func_get_args(); // you can't just put func_get_args() into a function as a parameter
		call_user_func_array( array( 'parent', '__construct' ), $args );

		if ( ! empty( $this->args['fields'] ) ) {
			foreach ( $this->args['fields'] as $f ) {

				$field_value = isset( $this->value[$f['id']] ) ? $this->value[$f['id']] : '';
				$f['uid'] = $f['id'];

				$class = _cmb_field_class_for_type( $f['type'] );
				$f['show_label'] = true;

				// Todo support for repeatable fields in groups
				$this->add_field( new $class( $f['uid'], $f['name'], (array) $field_value, $f ) );

			}
		}

	}

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		foreach ( $this->args['fields'] as $f ) {

			$class = _cmb_field_class_for_type( $f['type'] );
			$field = new $class( '', '', array(), $f );
			$field->enqueue_scripts();
		}
	}

	public function enqueue_styles() {

		parent::enqueue_styles();

		foreach ( $this->args['fields'] as $f ) {
			$class = _cmb_field_class_for_type( $f['type'] );
			$field = new $class( '', '', array(), $f );
			$field->enqueue_styles();
		}

	}

	public function display() {

		global $post;

		$field = $this->args;

		$this->title();
		$this->description();

		$i = 0;
		foreach ( $this->get_values() as $value ) {

			$this->field_index = $i;
			$this->value = $value; 	

			?>

			<div class="field-item" data-class="<?php echo esc_attr( get_class($this) ) ?>" style="<?php echo esc_attr( $this->args['style'] ); ?>">
				<?php $this->html(); ?>
			</div>

			<?php

			$i++;
		
		}	

		if ( $this->args['repeatable'] ) {

			$this->field_index = 'x'; // x used to distinguish hidden fields.
			$this->value = ''; ?>

				<div class="field-item hidden" data-class="<?php echo esc_attr( get_class($this) ) ?>" style="<?php echo esc_attr( $this->args['style'] ); ?>">

					<?php $this->html(); ?>

				</div>

				<button class="button repeat-field"><?php esc_html_e( 'Add New', 'cmb' ); ?></button>

		<?php }

	}

	public function html() {

		$fields = &$this->get_fields();
		$value = $this->value;

		if ( ! empty( $value ) ) {
			foreach ( $value as $field_id => $field_value ) {
				if ( ! empty( $field_value ) && ! empty( $fields[$field_id] ) )
					$fields[$field_id]->set_values( (array) $field_value );
				else if ( ! empty( $fields[$field_id] ) )
					$fields[$field_id]->set_values( array() );
			}
		} else {
			foreach ( $fields as &$field ) {
				$field->set_values( array() );
			}
		}

		?>

		<?php if ( $this->args['repeatable'] ) : ?>
			<button class="cmb-delete-field" title="Remove field"><span class="cmb-delete-field-icon">&times;</span> Remove Group</button>
		<?php endif; ?>

		<?php CMB_Meta_Box::layout_fields( $fields ); ?>

	<?php }

	public function parse_save_values() {

		$fields = &$this->get_fields();
		$values = &$this->get_values();

		foreach ( $values as &$group_value ) {
			foreach ( $group_value as $field_id => &$field_value ) {

				if ( ! isset( $fields[$field_id] ) ) {
					$field_value = array();
					continue;
				}
				
				$field = $fields[$field_id];
				$field->values = $field_value;
				$field->parse_save_values();

				$field_value = $field->get_values();

				// if the field is a repeatable field, store the whole array of them, if it's not repeatble,
				// just store the first (and only) one directly
				if ( ! $field->args['repeatable'] )
					$field_value = reset( $field_value );
			}
		}

	}

	public function add_field( CMB_Field $field ) {
		$field->parent = $this;
		$this->fields[$field->id] = $field;
	}

	public function &get_fields() {
		return $this->fields;
	}

	public function set_values( array $values ) {
		
		$this->values = $values;
		$fields = &$this->get_fields();

		foreach ( $values as $value ) {
			foreach ( $value as $field_id => $field_value ) {
				$fields[$field_id]->set_values( (array) $field_value );
			}
		}

	}

}