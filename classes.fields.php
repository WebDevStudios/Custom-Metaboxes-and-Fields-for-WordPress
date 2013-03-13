<?php

/**
 * Abstract class for all fields.
 * Subclasses need only override html()
 *
 * @abstract
 */
abstract class CMB_Field {

	public $value;
	public $current_item = 0;


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

		$id = $this->id . '-' . $this->current_item;

		if ( $append )
			$id .= '-' . $append;

		?>

		id="<?php esc_attr_e( $id ); ?>"

		<?php
	}

	public function for_attr( $append = null ) {

		$for = $this->id . '-' . $this->current_item;

		if ( $append )
			$for .= '-' . $append;

		?>

		for="<?php esc_attr_e( $for ); ?>"

		<?php
	}

	public function class_attr( $classes = '' ) {

		if ( $classes = implode( ' ', array_map( 'sanitize_html_class', array_filter( array_unique( explode( ' ', $classes . ' ' . $this->args['class'] ) ) ) ) ) ) { ?>

			class="<?php esc_attr_e( $classes ); ?>"

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
				<label for="<?php esc_attr_e( $this->id . '-' . $this->current_item ); ?>">
					<?php esc_html_e( $this->title ); ?>
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

		foreach ( $values as $key => $value ) {

			$this->current_item = $key;
			$this->value = $value; ?>

			<div class="field-item" style="position: relative; <?php esc_attr_e( $this->args['style'] ); ?>">

			<?php if ( $this->args['repeatable'] ) : ?>

				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon">&times;</a>
					</span>
				</span>

			<?php endif; ?>

			<?php $this->html(); ?>

			</div>

		<?php }

		// Insert a hidden one if it's repeatable
		if ( $this->args['repeatable'] ) {

			$this->value = ''; ?>

			<div class="field-item hidden" style="position: relative">

			<?php if ( $this->args['repeatable'] ) : ?>

				<span class="cmb_element">
					<span class="ui-state-default">
						<a class="delete-field ui-icon-circle-close ui-icon">&times;</a>
					</span>
				</span>

			<?php endif; ?>

			<?php $this->html(); ?>

			</div>

			<button href="#" class="button repeat-field">Add New</button>

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

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->get_value() ); ?>" />

	<?php }
}

class CMB_Text_Small_Field extends CMB_Field {

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small' ); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->get_value() ); ?>" />

	<?php }
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

	public function html() { ?>

		<a class="button cmb-file-upload <?php echo esc_attr( $this->get_value() ) ? 'hidden' : '' ?>" href="#">Add Media</a>

		<div class="cmb-file <?php esc_attr_e( $this->get_value() ? '' : 'hidden' ); ?>" style="text-align: center;">

			<div class="cmb-file-holder <?php if ( $this->value ) { esc_attr_e( wp_attachment_is_image( $this->value ) ? ' type-img' : ' type-file' ); } ?>" style="text-align: center; vertical-align: middle;">

				<?php if ( $this->get_value() )
					echo wp_get_attachment_image( $this->get_value(),'thumbnail', true ) ?>

				<?php if ( $this->get_value() && ! wp_attachment_is_image( $this->value ) ) : ?>
					<div class="cmb-file-name">
						<strong>
							<?php esc_html_e( end( explode( DIRECTORY_SEPARATOR, get_attached_file( $this->get_value() ) ) ) ); ?>
						</strong>
					</div>
				<?php endif; ?>

			</div>

			<a href="#" class="cmb-remove-file button">Remove</a>

		</div>

		<input type="hidden" class="cmb-file-upload-input" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->value ); ?>" />

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

		<div style="<?php esc_attr_e( $style ); ?>" class="hm-uploader <?php echo  $attachment_id ? 'with-image' : ''; ?>" id="<?php esc_attr_e( $img_prefix ); ?>-container">

			<input type="hidden" class="field-id rwmb-image-prefix" value="<?php esc_attr_e( $img_prefix ); ?>" />

			<input type="hidden" class="field-val" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $attachment_id ); ?>" />

			<div style="<?php esc_attr_e( $style ); ?><?php echo ( $attachment_id ) ? '' : 'display: none;' ?> line-height: <?php esc_attr_e( $args['size']['height'] ); ?>px;" class="current-image">

				<?php if ( $attachment_id && wp_get_attachment_image( $attachment_id, $args['size'], false, 'id=' . $this->id ) ) : ?>
					<?php echo wp_get_attachment_image( $attachment_id, $args['size'], false, 'id=' . $this->id ) ?>

				<?php else : ?>
					<img src="" />
				<?php endif; ?>

				<div class="image-options">
					<a href="#" class="delete-image button-secondary">Remove</a>
				</div>
			</div>

			<div style="<?php esc_attr_e( $style ); ?>" id="<?php esc_attr_e( $img_prefix ); ?>-dragdrop" data-extensions="<?php esc_attr_e( $extensions ); ?>" data-size="<?php esc_attr_e( $size_str ); ?>" class="rwmb-drag-drop upload-form">
				<div class="rwmb-drag-drop-inside">
					<p><?php esc_html_e( $drop_text ); ?></p>
					<p>or</p>
					<p><input id="<?php esc_html_e( $img_prefix ); ?>-browse-button" type="button" value="Select Files" class="button-secondary" /></p>
				</div>
			</div>

			<div style="<?php esc_attr_e( $style ) ?>" class="loading-block hidden">
				<img src="<?php esc_attr_e( esc_url( get_bloginfo( 'template_url' ) . '/framework/assets/images/spinner.gif' ) ); ?>" />
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
		if ( is_numeric( $_REQUEST['post_id'] ) )
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

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_url code' ); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( esc_url( $this->value ) ); ?>" />

	<?php }
}

/**
 * Date picker box.
 *
 */
class CMB_Date_Field extends CMB_Field {

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->value ); ?>" />

	<?php }
}

class CMB_Time_Field extends CMB_Field {

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_timepicker' ); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->value ); ?>"/>

	<?php }

}

/**
 * Date picker for date only (not time) box.
 *
 */
class CMB_Date_Timestamp_Field extends CMB_Field {

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php echo $this->value ? esc_attr( date( 'm\/d\/Y', $this->value ) ) : '' ?>" />

	<?php }

	public function parse_save_value() {
		$this->value = strtotime( $this->value );
	}

}

/**
 * Date picker for date and time (seperate fields) box.
 *
 */
class CMB_Datetime_Timestamp_Field extends CMB_Field {

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_datepicker' ); ?> type="text" name="datetime_<?php esc_attr_e( $this->id ); ?>[date][]" value="<?php echo $this->value ? esc_attr_e( date( 'm\/d\/Y', $this->value ) ) : '' ?>" />
		<input <?php $this->id_attr('time'); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_text_small cmb_timepicker' ); ?> type="text" name="datetime_<?php esc_attr_e( $this->id ); ?>[time][]" value="<?php echo $this->value ? esc_attr_e( date( 'H:i A', $this->value ) ) : '' ?>" />

	<?php }

	public function parse_save_values() {

		// We need to handle the post data slightly different from the standard CMB.
		$values = isset( $_POST['datetime_' . $this->id] ) ? $_POST['datetime_' . $this->id] : array();

		if ( ! empty( $this->args['repeatable'] ) )
			foreach ( $values as &$value ) {
				end( $value );
				unset( $value[key( $value )] );
				reset( $value );
			}

		$r = array();

		for ( $i = 0; $i < count( $values['date'] ); $i++ )
			if( ! empty( $values['date'][$i] ) )
				$r[$i] = strtotime( $values['date'][$i] . ' ' . $values['time'][$i] );

		sort( $r );

		$r[] = '';

		$this->values = $r;

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

				<input class="cmb_oembed code" type="text" name="<?php esc_attr_e( $this->name ); ?>" id="<?php esc_attr_e( $this->name ); ?>" value="" />

			<?php else : ?>

				<div class="hidden"><input disabled class="cmb_oembed code" type="text" name="<?php esc_attr_e( $this->name ); ?>" id="<?php esc_attr_e( $this->name ); ?>" value="" /></div>

				<div style="position: relative">

				<?php if ( is_array( $this->value ) ) : ?>

					<span class="cmb_oembed"><?php echo $this->value['object']; ?></span>
					<input type="hidden" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( serialize( $this->value ) ); ?>" />

				<?php else : ?>

					<span class="cmb_oembed"><?php echo $this->value; ?></span>
					<input type="hidden" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->value ); ?>" />

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

		<textarea <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> rows="<?php echo ! empty( $this->args['rows'] ) ? esc_attr( $this->args['rows'] ) : 4; ?>" name="<?php esc_attr_e( $this->name ); ?>"><?php esc_attr_e( $this->value ); ?></textarea>

	<?php }

}

/**
 * Code style text field.
 *
 * Args:
 *  - int "rows" - number of rows in the <textarea>
 */
class CMB_Textarea_Field_Code extends CMB_Field {

	public function html() { ?>

		<textarea <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_textarea_code' ); ?> rows="<?php echo ! empty( $this->args['rows'] ) ? esc_attr( $this->args['rows'] ) : 4; ?>" name="<?php esc_attr_e( $this->name ); ?>"><?php esc_attr_e( $this->value ); ?></textarea>

	<?php }

}

/**
 *  Colour picker
 *
 */
class CMB_Color_Picker extends CMB_Field {

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr( 'cmb_colorpicker cmb_text_small' ); ?> type="text" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $this->get_value() ); ?>" />

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

		$id = 'select-' . rand( 0, 1000 );

		$val = (array) $this->get_value(); ?>

			<?php if ( $this->args['ajax_url'] ) : ?>

			<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> value="<?php esc_attr_e( implode( ',' , (array) $this->value ) ); ?>" name="<?php esc_attr_e( $this->name ); ?>" style="width: 100%" class="<?php esc_attr_e( $id ); ?>" id="<?php esc_attr_e( $id ); ?>" />

			<?php else : ?>

			<select <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> style="width: 100%" <?php echo ! empty( $this->args['multiple'] ) ? 'multiple' : '' ?> class="<?php esc_attr_e( $id ); ?>" name="<?php /*nasty hack*/ esc_attr_e( str_replace( '[', '[m', $this->name ) ); ?><?php echo ! empty( $this->args['multiple'] ) ? '[]' : ''; ?>">

					<?php if ( ! empty( $this->args['allow_none'] ) ) : ?>

						<option value="">None</option>

					<?php endif; ?>

					<?php foreach ( $this->args['options'] as $value => $name ): ?>

					   <option <?php selected( in_array( $value, $val ) ) ?> value="<?php esc_attr_e( $value ); ?>"><?php esc_attr_e( $name ); ?></option>

					<?php endforeach; ?>

				</select>
			<?php endif; ?>

		<script>

			jQuery( document ).ready( function() {

				var options = { placeholder: "Type to search" };

				<?php if ( $this->args['ajax_url'] ) : ?>

					var query = JSON.parse( '<?php echo esc_js( json_encode( $this->args['ajax_args'] ? wp_parse_args( $this->args['ajax_args'] ) : (object) array() ) ); ?>' );
					var posts = [];

					<?php if ( $this->args['multiple'] ) : ?>

						options.multiple = true;

					<?php endif; ?>

					<?php foreach ( array_filter( (array) $this->value ) as $post_id ) : ?>

						posts.push( { id: <?php echo esc_js( $post_id ); ?>, text: '<?php echo esc_js( get_the_title( $post_id ) ); ?>' } );

					<?php endforeach; ?>

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

					options.initSelection = function (element, callback) {
						return posts;
					}

				<?php endif; ?>

				setInterval( function() {

					jQuery( '.<?php echo esc_js( $id ); ?>' ).each( function( index, el ) {

						if ( jQuery( el ).is( ':visible' ) && ! jQuery( el ).hasClass( 'select2-added' ) )
							jQuery( this ).addClass( 'select2-added' ).select2( options );

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

			<input <?php $this->id_attr( 'item-' . $key ); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> type="radio" name="<?php esc_attr_e( $this->name ); ?>" value="<?php esc_attr_e( $key ); ?>" <?php checked( $key, $this->get_value() ); ?> />
			<label <?php $this->for_attr( 'item-' . $key ); ?> style="margin-right: 20px;">
				<?php esc_html_e( $value ); ?>
			</label>

			<?php endforeach; ?>

	<?php }

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

	public function title() {}

	public function html() { ?>

		<input <?php $this->id_attr(); ?> <?php $this->boolean_attr(); ?> <?php $this->class_attr(); ?> type="checkbox" name="checkbox_<?php esc_attr_e( $this->name ); ?>" value="1" <?php checked( $this->get_value() ); ?> />
		<label <?php $this->for_attr(); ?>><?php esc_html_e( $this->args['name'] ); ?></label>

			<input type="hidden" name="<?php esc_attr_e( $this->name ); ?>" value="1" />

	<?php }

}


/**
 * Standard title used as a splitter.
 *
 */
class CMB_Title extends CMB_Field {

	public function html() {}

}

/**
 * wysiwyg field.
 *
 */
class CMB_wysiwyg extends CMB_Field {

	public function html() { ?>

			<?php wp_editor( $this->get_value(), $this->name, $this->args['options'] );?>

	<?php }
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

			<h2 class="group-name"><?php esc_attr_e( $this->args['name'] ); ?></h2>

		<?php endif;

		foreach ( $meta as $value ) {

			$this->value = $value; ?>

			<div class="field-item" style="<?php esc_attr_e( $this->args['style'] ); ?>">
				<?php $this->html(); ?>
			</div>

		<?php }

		if ( $this->args['repeatable'] ) {

			$this->value = ''; ?>

				<div class="field-item hidden" style="<?php esc_attr_e( $this->args['style'] ); ?>">

					<?php $this->html(); ?>

				</div>

			<button href="#" class="button repeat-field">Add New</button>

		<?php }

	}

	public function add_field( CMB_Field $field ) {

		$key = $field->id;
		$field->original_id = $key;
		$field->id = $this->id . '[' . $field->id . '][]';
		$field->name = $field->id . '[]';
		$this->fields[$key] = $field;

	}

	public function html() {

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

				// Create the field object so it can sanitize it's data etc
				$field->values = (array) $values[$field->original_id][$key];
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