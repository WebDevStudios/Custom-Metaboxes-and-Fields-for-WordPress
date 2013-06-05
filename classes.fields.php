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

			foreach ( $this->args['options'] as $option ) {
				$re_format[$option['value']] = $option['name'];
			}

			// TODO this is incorrect
			_deprecated_argument( 'CMB_Field', "'std' is deprecated, use 'default instead'", '0.9' );

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
	 * Method responsible for enqueueing any extra scripts the field needs
	 *
	 * @uses wp_enqueue_script()
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'cmb-scripts', CMB_URL . '/js/cmb.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'media-upload', 'thickbox', 'farbtastic' ) );
	}

	/**
	 * Method responsible for enqueueing any extra styles the field needs
	 *
	 * @uses wp_enqueue_style()
	 */
	public function enqueue_styles() {}

	public function id_attr( $append = null ) {

		printf( 'id="%s"', esc_attr( $this->get_the_id_attr( $append ) ) );
		
	}
	
	public function get_the_id_attr( $append = null ) {

		$id = $this->id;

		if ( isset( $this->group_index ) )
			$id .= '-cmb-group-' . $this->group_index;

		$id .= '-cmb-field-' . $this->field_index;

		if ( ! is_null( $append ) )
			$id .= '-' . $append;

		$id = str_replace( array( '[', ']', '--' ), '-', $id );

		return $id;
		
	}

	public function for_attr( $append = null ) {

		$for = $this->id;

		if ( isset( $this->group_index ) )
			$for .= '-cmb-group-' . $this->group_index;

		$for .= '-cmb-field-' . $this->field_index;

		if ( ! is_null( $append ) )
			$for .= '-' . $append;

		$for = str_replace( array( '[', ']', '--' ), '-', $for );

		printf( 'for="%s"', esc_attr( $for ) );

	}

	public function name_attr( $append = null ) {
	
		printf( 'name="%s"', esc_attr( $this->get_the_name_attr( $append ) ) );
	
	}

	public function get_the_name_attr( $append = null ) {

		$name = str_replace( '[]', '', $this->name );

		if ( isset( $this->group_index ) )
			$name .= '[cmb-group-' . $this->group_index . ']';

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

	public function get_values() {
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

				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon">&times;</a>
					</span>
				</span>

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

			<div class="field-item hidden" data-class="<?php echo esc_attr( get_class($this) ) ?>" style="position: relative">

			<?php if ( $this->args['repeatable'] ) : ?>

				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon">&times;</a>
					</span>
				</span>

			<?php endif; ?>

			<?php $this->html(); ?>

			</div>

			<button class="button repeat-field">Add New</button>

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
		wp_enqueue_script( 'cmb-file-upload', CMB_URL . '/js/file-upload.js', array( 'jquery' ) );
		wp_enqueue_media();
	}

	public function html() { 

		$args = wp_parse_args( $this->args, array(
			'size' => array( 150, 150, 'crop' => true )
		) );

		?>

		<a class="button cmb-file-upload <?php echo esc_attr( $this->get_value() ) ? 'hidden' : '' ?>" href="#">Add Media</a>

		<div class="cmb-file <?php echo $this->get_value() ? '' : 'hidden'; ?>" style="text-align: center;">

			<div class="cmb-file-holder <?php if ( $this->value ) { echo wp_attachment_is_image( $this->value ) ? ' type-img' : ' type-file'; } ?>" style="text-align: center; vertical-align: middle;">

				<?php if ( $this->get_value() )
					echo wp_get_attachment_image( $this->get_value(), $args['size'], true ) ?>

				<?php if ( $this->get_value() && ! wp_attachment_is_image( $this->value ) ) : ?>
					<div class="cmb-file-name">
						<strong>
							<?php echo esc_html( end( explode( DIRECTORY_SEPARATOR, get_attached_file( $this->get_value() ) ) ) ); ?>
						</strong>
					</div>
				<?php endif; ?>

			</div>

			<a href="#" class="cmb-remove-file button">Remove</a>

		</div>

		<input type="hidden" class="cmb-file-upload-input" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>" />

	<?php }
}

class CMB_Image_Field extends CMB_Field {

	function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_script( 'plupload-all' );
		wp_enqueue_script( 'tf-well-plupload-image', CMB_URL . '/js/plupload-image.js', array( 'jquery-ui-sortable', 'wp-ajax-response', 'plupload-all' ), 1 );

		wp_localize_script( 'tf-well-plupload-image', 'tf_well_plupload_defaults', array(
			'runtimes'				=> 'html5,silverlight,flash,html4',
			'file_data_name'		=> 'async-upload',
			'multiple_queues'		=> true,
			'max_file_size'			=> wp_max_upload_size().'b',
			'url'					=> admin_url('admin-ajax.php'),
			'flash_swf_url'			=> includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url'	=> includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'				=> array( array( 'title' => __( 'Allowed Image Files' ), 'extensions' => '*' ) ),
			'multipart'				=> true,
			'urlstream_upload'		=> true,
			// additional post data to send to our ajax hook
			'multipart_params'		=> array(
				'_ajax_nonce'	=> wp_create_nonce( 'plupload_image' ),
				'action'    	=> 'plupload_image_upload'
			)

		) );

	}

	function enqueue_styles() {
		wp_enqueue_style( 'tf-well-plupload-image', CMB_URL . '/css/plupload-image.css', array() );
	}

	function html() {

		$args = wp_parse_args( $this->args, array(
			'allowed_extensions' => array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' ),
			'size' => array( 'width' => 150, 'height' => 150, 'crop' => true )
		) );

		$args['size'] = wp_parse_args( $args['size'], array( 'width' => 150, 'height' => 150, 'crop' => true ) );

		$attachment_id = $this->get_value();
		// Filter to change the drag & drop box background string
		$drop_text = 'Drag & Drop files';
		$extensions = implode( ',', $args['allowed_extensions'] );
		$img_prefix	= $this->id;
		$style = sprintf( 'width: %dpx; height: %dpx;', $args['size']['width'], $args['size']['height'] );

		$size_str = sprintf( 'width=%d&height=%d&crop=%s', $args['size']['width'], $args['size']['height'], $args['size']['crop'] ); ?>

		<div style="<?php echo esc_attr( $style ); ?>" class="hm-uploader <?php echo  $attachment_id ? 'with-image' : ''; ?>" id="<?php echo esc_attr( $img_prefix ); ?>-container">

			<input type="hidden" class="field-id rwmb-image-prefix" value="<?php echo esc_attr( $img_prefix ); ?>" />

			<input type="hidden" class="field-val" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $attachment_id ); ?>" />

			<div style="<?php echo esc_attr( $style ); ?><?php echo ( $attachment_id ) ? '' : 'display: none;' ?> line-height: <?php echo esc_attr( $args['size']['height'] ); ?>px;" class="current-image">

				<?php if ( $attachment_id && wp_get_attachment_image( $attachment_id, $args['size'], false, 'id=' . $this->id ) ) : ?>
					<?php echo wp_get_attachment_image( $attachment_id, $args['size'], false, 'id=' . $this->id ) ?>

				<?php else : ?>
					<img src="" />
				<?php endif; ?>

				<div class="image-options">
					<a href="#" class="delete-image button-secondary">Remove</a>
				</div>
			</div>

			<div style="<?php echo esc_attr( $style ); ?>" id="<?php echo esc_attr( $img_prefix ); ?>-dragdrop" data-extensions="<?php echo esc_attr( $extensions ); ?>" data-size="<?php echo esc_attr( $size_str ); ?>" class="rwmb-drag-drop upload-form">
				<div class="rwmb-drag-drop-inside">
					<p><?php echo esc_html( $drop_text ); ?></p>
					<p>or</p>
					<p><input id="<?php echo esc_html( $img_prefix ); ?>-browse-button" type="button" value="Select Files" class="button-secondary" /></p>
				</div>
			</div>

			<div style="<?php echo esc_attr( $style ) ?> border-radius: 0 !important; background: #DEDEDE; box-shadow: 0 0 10px rgba(0,0,0,0.5) inset;" class="loading-block hidden">
				<div style="height:100%; width: 100%; background: url('<?php echo esc_attr( esc_url( get_site_url() . '/wp-admin/images/wpspin_light-2x.gif' ) ); ?>') center center no-repeat; background-size: 16px 16px;"></div>
			</div>

		</div>

	<?php }


	/**
	 * Upload
	 * Ajax callback function
	 *
	 * @return error or (XML-)response
	 */
	static function handle_upload () {
		header( 'Content-Type: text/html; charset=UTF-8' );

		if ( ! defined('DOING_AJAX' ) )
			define( 'DOING_AJAX', true );

		check_ajax_referer('plupload_image');

		$post_id = 0;
		if ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) )
			$post_id = (int) $_REQUEST['post_id'];

		// you can use WP's wp_handle_upload() function:
		$file = $_FILES['async-upload'];
		$file_attr = wp_handle_upload( $file, array('test_form'=>true, 'action' => 'plupload_image_upload') );
		$attachment = array(
			'post_mime_type'	=> $file_attr['type'],
			'post_title'		=> preg_replace( '/\.[^.]+$/', '', basename( $file['name'] ) ),
			'post_content'		=> '',
			'post_status'		=> 'inherit',

		);

		// Adds file as attachment to WordPress
		$id = wp_insert_attachment( $attachment, $file_attr['file'], $post_id );
		if ( ! is_wp_error( $id ) )
		{
			$response = new WP_Ajax_Response();
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_attr['file'] ) );
			if ( isset( $_REQUEST['field_id'] ) )
			{
				// Save file ID in meta field
				add_post_meta( $post_id, $_REQUEST['field_id'], $id, false );
			}

			$src = wp_get_attachment_image_src( $id, $_REQUEST['size'] );

			$response->add( array(
				'what'			=>'tf_well_image_response',
				'data'			=> $id,
				'supplemental'	=> array(
					'thumbnail'	=>  $src[0],
					'edit_link'	=> get_edit_post_link($id)
				)
			) );
			$response->send();
		}

		exit;
	}

}
add_action( 'wp_ajax_plupload_image_upload', array( 'CMB_Image_Field', 'handle_upload' ) );

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

		wp_enqueue_script( 'cmb_datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery' ) );
	}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>" />

	<?php }
}

class CMB_Time_Field extends CMB_Field {

	public function enqueue_scripts() {

		parent::enqueue_scripts();

		wp_enqueue_script( 'cmb_datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery' ) );
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

		wp_enqueue_script( 'cmb_datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery' ) );
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

		wp_enqueue_script( 'cmb_datetime', trailingslashit( CMB_URL ) . 'js/field.datetime.js', array( 'jquery' ) );
	}

	public function html() { ?>		

		<input <?php $this->id_attr('date'); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" <?php $this->name_attr( '[date]' ); ?>  value="<?php echo $this->value ? esc_attr( date( 'm\/d\/Y', $this->value ) ) : '' ?>" />
		<input <?php $this->id_attr('time'); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_timepicker' ); ?> type="text" <?php $this->name_attr( '[time]' ); ?> value="<?php echo $this->value ? esc_attr( date( 'H:i A', $this->value ) ) : '' ?>" />

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
 * Standard text meta box for a URL.
 *
 */
class CMB_Oembed_Field extends CMB_Field {

	public function html() { ?>

		<style>

			.cmb_oembed img, .cmb_oembed object, .cmb_oembed video, .cmb_oembed embed, .cmb_oembed iframe { max-width: 100%; height: auto; }

		</style>

			<?php if ( ! $this->value ) : ?>

				<input class="cmb_oembed code" type="text" <?php $this->name_attr(); ?> id="<?php echo esc_attr( $this->name ); ?>" value="" />

			<?php else : ?>

				<div class="hidden"><input disabled class="cmb_oembed code" type="text" <?php $this->name_attr(); ?> id="<?php echo esc_attr( $this->name ); ?>" value="" /></div>

				<div style="position: relative">

				<?php if ( is_array( $this->value ) ) : ?>

					<span class="cmb_oembed"><?php echo $this->value['object']; ?></span>
					<input type="hidden" <?php $this->name_attr(); ?> value="<?php echo esc_attr( serialize( $this->value ) ); ?>" />

				<?php else : ?>

					<span class="cmb_oembed"><?php echo $this->value; ?></span>
					<input type="hidden" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->value ); ?>" />

				<?php endif; ?>

					<a href="#" class="cmb_remove_file_button" onclick="jQuery( this ).closest('div').prev().removeClass('hidden').find('input').first().removeAttr('disabled')">Remove</a>

				</div>

			<?php endif; ?>

	<?php }

	public function parse_save_value() {

		$args['cmb_oembed'] = true;

		if ( ! empty( $this->args['height'] ) )
			$args['height'] = $this->args['height'];

		if ( strpos( $this->value, 'http' ) === 0 )
			$this->value = wp_oembed_get( $this->value, $args );

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

		wp_enqueue_script( 'cmb_colorpicker', trailingslashit( CMB_URL ) . 'js/field.colorpicker.js', array( 'jquery' ) );
	
	}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_colorpicker cmb_text_small' ); ?> type="text" <?php $this->name_attr(); ?> value="<?php echo esc_attr( $this->get_value() ); ?>" />

	<?php }

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

		$this->args = wp_parse_args( $this->args, array( 'multiple' => false, 'ajax_url' => '' ) );

	}

	public function parse_save_values(){

		if ( isset( $this->group_index ) && isset( $this->args['multiple'] ) && $this->args['multiple'] )
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
	}

	public function enqueue_styles() {

		parent::enqueue_styles();

		wp_enqueue_style( 'select2', trailingslashit( CMB_URL ) . 'js/select2/select2.css' );
	}

	public function html() {

		if ( $this->has_data_delegate() )
			$this->args['options'] = $this->get_delegate_data();

		$id = $this->get_the_id_attr();
		
		$name = $this->get_the_name_attr();
		$name .= ! empty( $this->args['multiple'] ) ? '[]' : null;

		$val = (array) $this->get_value();

		?>

			<?php if ( $this->args['ajax_url'] ) : ?>

				<input <?php $this->id_attr(); ?> value="<?php echo esc_attr( implode( ',' , (array) $this->value ) ); ?>" <?php $this->boolean_attr(); ?> <?php printf( 'name="%s"', esc_attr( $name ) ); ?> <?php echo ! empty( $this->args['multiple'] ) ? 'multiple' : '' ?> class="<?php echo esc_attr( $id ); ?>" style="width: 100%" />

			<?php else : ?>
			
				<select <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php printf( 'name="%s"', esc_attr( $name ) ); ?> <?php echo ! empty( $this->args['multiple'] ) ? 'multiple' : '' ?> class="<?php echo esc_attr( $id ); ?>" style="width: 100%" >

					<?php if ( ! empty( $this->args['allow_none'] ) ) : ?>

						<option value="">None</option>

					<?php endif; ?>

					<?php foreach ( $this->args['options'] as $value => $name ): ?>

					   <option <?php selected( in_array( $value, $val ) ) ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_attr( $name ); ?></option>

					<?php endforeach; ?>

				</select>
			<?php endif; ?>

		<script>

			jQuery( document ).ready( function() {

				var options = { 
					placeholder: "Type to search" ,
					allowClear: true
				};

				<?php if ( $this->args['ajax_url'] ) : ?>

					var query = JSON.parse( '<?php echo json_encode( $this->args['ajax_args'] ? wp_parse_args( $this->args['ajax_args'] ) : (object) array() ); ?>' );

					<?php if ( $this->args['multiple'] ) : ?>

						options.multiple = true;

					<?php endif; ?>

					<?php if ( ! empty( $this->value ) ) : ?>

						options.initSelection = function( element, callback ) {
								
							<?php if ( $this->args['multiple'] ) : ?>
								
								var data = [];
								
								<?php foreach ( (array) $this->value as $post_id ) : ?>
									data.push = <?php echo sprintf( '{ id: %d, text: "%s" }', $this->value, get_the_title( $this->value ) ); ?>;
								<?php endforeach; ?>
							
							<?php else : ?>
							
								var data = <?php echo sprintf( '{ id: %d, text: "%s" }', $this->value, get_the_title( $this->value ) ); ?>;
							
							<?php endif; ?>

							callback( data );
							
						};

					<?php endif; ?>

					options.ajax = {
						url: '<?php echo esc_js( $this->args['ajax_url'] ); ?>',
						dataType: 'json',
						data: function( term, page ) {
							query.s = term;
							query.paged = page;
							return query;
						},
						results : function( data, page ) {
							return { results: data }
						}
					}
					
				<?php endif; ?>	
				
				setInterval( function() {
					jQuery( '#<?php echo esc_js( $id ); ?>' ).each( function( index, el ) {
						if ( jQuery( el ).is( ':visible' ) && ! jQuery( el ).hasClass( 'select2-added' ) )
							jQuery(el).addClass( 'select2-added' ).select2( options );
					} );

				}, 300 );

			} );

		</script>

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
		wp_enqueue_script( 'cmb-wysiwyg', CMB_URL . '/js/field-wysiwyg.js', array( 'jquery' ) );
	}

	public function html() { 

		$id   = $this->get_the_id_attr();
		$name = $this->get_the_name_attr();		

		$field_id = str_replace( array( '-', '[', ']', '--' ),'_', $this->id );

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

		if ( isset( $this->group_index ) && ! is_int( $this->group_index ) )
			return true;

		else return ! is_int( $this->field_index );

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
 * Standard select field.
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

		$this->args = wp_parse_args( $this->args, array( 'use_ajax' => false ) );

		$this->args['query'] = isset( $this->args['query'] ) ? $this->args['query'] : array();

		if ( ! $this->args['use_ajax'] ) {
			
			$this->args['data_delegate'] = array( $this, 'get_delegate_data' );

		} else {
			
			$this->args['ajax_url'] = add_query_arg( 'action', 'cmb_post_select', admin_url( 'admin-ajax.php' ) );
			$this->args['ajax_args'] = $this->args['query'];

		}

	}

	public function get_delegate_data() {

		$posts = $this->get_posts();
		$post_options = array();

		foreach ( $posts as $post )
			$post_options[$post->ID] = get_the_title( $post->ID );

		return $post_options;

	}

	private function get_posts() {

		return get_posts( $this->args['query'] );

	}

	public function parse_save_value() {

		if ( $this->args['ajax_url'] && $this->args['multiple'] )
			$this->value = explode( ',', $this->value );

	}
}

// TODO this should be in inside the class
function cmb_ajax_post_select() {

	$query = new WP_Query( $_GET );
	
	$posts = $query->posts;

	$json = array();

	foreach ( $posts as $post )
		$json[] = array( 'id' => $post->ID, 'text' => get_the_title( $post->ID ) );

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
		foreach ( $this->args['fields'] as $f ) {

			$class = _cmb_field_class_for_type( $f['type'] );
			$field = new $class( '', '', array(), $f );
			$field->enqueue_styles();
		}
	}

	public function display() {

		global $post;

		$meta = $this->values;

		if ( ! $meta && ! $this->args['repeatable'] )
			$meta = array( '' );

		$field = $this->args;

		if ( ! empty( $this->args['name'] ) ) : ?>

			<h2 class="group-name"><?php echo esc_attr( $this->args['name'] ); ?></h2>

		<?php endif;

		$i = 0;
		foreach ( $meta as $value ) {

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

				<button class="button repeat-field">Add New</button>

		<?php }

	}

	public function add_field( CMB_Field $field ) {

		$key                = $field->id;
		$field->original_id = $key;
		$field->id          = $this->id . '[' . $field->id . ']';
		$field->name        = $field->id . '[]';
		$field->group_index = $this->field_index;
		$this->fields[$key] = $field;

	}

	public function html() {

		// Set the group index for each field.
		foreach ( $this->fields as $field => $field_value )
			$this->fields[$field]->group_index = $this->field_index;

		$value = $this->value;

		if ( ! empty( $value ) ) {
			foreach ( $value as $field => $field_value )
				if ( ! empty( $field ) && ! empty( $this->fields[$field] ) )
					$this->fields[$field]->set_values( (array) $field_value );
				else if ( ! empty( $this->fields[$field] ) )
					$this->fields[$field]->set_values( array() );
		} else {
			foreach ( $this->fields as $field ) {
				$field->set_values( array() );
			}
		}

		$field = $this->args; ?>

		<div class="group <?php echo ! empty( $field['repeatable'] ) ? 'cloneable' : '' ?>" style="position: relative">

			<?php if ( $this->args['repeatable'] ) : ?>
				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon">&times;</a>
					</span>
				</span>
			<?php endif; ?>

			<?php CMB_Meta_Box::layout_fields( $this->fields ); ?>

		</div>

	<?php }

	public function parse_save_values() {

		$values = $this->values;

		$this->values = array();		

		$first = reset( $values );

		foreach ( $first as $key => $field_val ) {

			$meta = array();

			foreach ( $this->fields as $field ) {

				$field->values = isset( $values[$field->original_id][$key] ) ? $values[$field->original_id][$key] : array();
				
				$field->parse_save_values();

				// if the field is a repeatable field, store the whole array of them, if it's not repeatble,
				// just store the first (and only) one directly
				if ( $field->args['repeatable'] )
					$meta[$field->original_id] = $field->values;

				else
					$meta[$field->original_id] = reset( $field->values );

			}

			if ( $this->isNotEmptyArray( $meta ) )
				$this->values[] = $meta;

		}
	}

	private function isNotEmptyArray( $array ) {

		foreach ( $array as &$value )
			if ( is_array( $value ) )
				$value = $this->isNotEmptyArray( $value );

		return array_filter( $array );

	}

	public function set_values( array $values ) {
		
		$this->values = $values;

		foreach ( $values as $value ) {

			foreach ( $value as $field => $field_value ) {
				$this->fields[$field]->set_values( (array) $field_value );
			}

		}

	}

}