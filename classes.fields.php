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
			'cols' 		=> '12',
			'style' 	=> ''
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

		$this->description = ! empty( $this->args['desc'] ) ? $this->args['desc'] : '';

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

	public function get_values() {
		return $this->values;
	}

	public function set_values( array $values ) {
		$this->values = $values;

		unset( $this->value );
	}


	public function parse_save_values() {

	}

	public function parse_save_value() {

	}

	public function save( $post_id ) {

		delete_post_meta( $post_id, $this->id );

		foreach( $this->values as $v ) {
			
			$this->value = $v;
			$this->parse_save_value();

			if ( $this->value )
				add_post_meta( $post_id, $this->id, $this->value );
		}
	}

	public function title() {

		if ( $this->title )
			echo '<h4 class="field-title">' . $this->title . '</h4>';

	}

	public function description() {

		if ( $this->description )
			echo '<p class="cmb_metabox_description">' . $this->description . '</p>';

	}

	public function display() {

		// if there are no values and it's not repeateble, we want to do one with empty string
		if ( empty( $this->values ) && !  $this->args['repeatable'] )
			$this->values = array( '' );

		$this->title();

		$this->description();

		foreach ( $this->values as $value ) {

			$this->value = $value;

			echo '<div class="field-item" style="position: relative; '. $this->args['style'] . '">';

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
			<input type="text" name="<?php echo $this->name ?>" value="<?php echo htmlspecialchars( $this->get_value() ) ?>" />
		</p>
		<?php
	}
}
 
class CMB_Text_Small_Field extends CMB_Field {
 
	public function html() {
 
		?>
		<p>
			<input type="text" name="<?php echo $this->name ?>" value="<?php echo htmlspecialchars( $this->get_value() ) ?>" class="cmb_text_small"/>
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

class CMB_Image_Field extends CMB_Field {

	function enqueue_scripts() {
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

		));
		
	}

	function enqueue_styles() {
		wp_enqueue_style( 'tf-well-plupload-image', CMB_URL . '/css/plupload-image.css', array() );
	}

	function html() {

		$args = wp_parse_args( $this->args, array( 
			'allowed_extensions' => array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' ),
			'size' => array( 'width' => 200, 'height' => 200, 'crop' => true )
		) );

		$args['size'] = wp_parse_args( $args['size'], array( 'width' => 200, 'height' => 200, 'crop' => true ) );

		$attachment_id = $this->get_value();
		// Filter to change the drag & drop box background string
		$drop_text = 'Upload image';
		$extensions = implode( ',', $args['allowed_extensions'] );
		$img_prefix	= $this->id;
		$style = sprintf( 'width: %dpx; height: %dpx;', $args['size']['width'], $args['size']['height'] );

		$size_str = sprintf( 'width=%d&height=%d&crop=%s', $args['size']['width'], $args['size']['height'], $args['size']['crop'] );


		$html = "<div style='$style' class='hm-uploader " . ( ( $attachment_id ) ? 'with-image' : '' ) . "' id='{$img_prefix}-container'>";
		
		$html .= "<input type='hidden' class='field-id rwmb-image-prefix' value='{$img_prefix}' />";

		echo $html;
		
		$html = '';
		?>

		<input type="hidden" class="field-val" name="<?php echo $this->name ?>" value="<?php echo $attachment_id ?>" />

		<div style="<?php echo $style ?><?php echo ( $attachment_id ) ? '' : 'display: none;' ?> line-height: <?php echo $args['size']['height'] ?>px;" class="current-image">
			<?php if ( $attachment_id && wp_get_attachment_image( $attachment_id, $args['size'], false, 'id=' . $this->id ) ) : ?>
				<?php echo wp_get_attachment_image( $attachment_id, $args['size'], false, 'id=' . $this->id ) ?>
			<?php else : ?>
				<img src="" />
			<?php endif; ?>
			<div class="image-options">
				<a href="#" class="delete-image button-secondary">Delete</a>
			</div>
		</div>
		<?php
		
		// Show form upload
		?>
		<div style='<?php echo $style ?>' id='<?php echo $img_prefix ?>-dragdrop' data-extensions='<?php echo $extensions ?>' data-size='<?php echo $size_str ?>' class='rwmb-drag-drop upload-form'>
			<div class = 'rwmb-drag-drop-inside'>
				<p><?php echo $drop_text ?></p>
				<p>or</p>
				<p><input id='<?php echo $img_prefix ?>-browse-button' type='button' value='Select Files' class='button-secondary' /></p>
			</div>
		</div>

		<div style="<?php echo $style ?>" class="loading-block hidden">
			<img src="<?php echo get_bloginfo( 'template_url' ).'/framework/assets/images/spinner.gif'; ?>" />
		</div>
		<?php

		$html .= "</div>";

		echo $html;
	}


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

	public function html() {

		?>
		<p>
			<input class="cmb_text_url code" type="text" name="<?php echo $this->name ?>" value="<?php echo esc_url( $this->value ) ?>" />
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
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" />
		</p>
		<?php
	}
}

class CMB_Time_Field extends CMB_Field {

	public function html() {
	
		?>
		<p>
			<input class="cmb_text_small cmb_timepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value;?>"/>
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
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->name ?>" value="<?php echo $this->value ? date( 'm\/d\/Y', $this->value ) : '' ?>" />
		</p>
		<?php
	}
	
	public function parse_save_value() {
		$this->value = strtotime( $this->value );
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
			<input class="cmb_text_small cmb_datepicker" type="text" name="<?php echo $this->id ?>[date][]" value="<?php echo $this->value ? date( 'm\/d\/Y', $this->value ) : '' ?>" />
			<input class="cmb_text_small cmb_timepicker" type="text" name="<?php echo $this->id ?>[time][]" value="<?php echo $this->value ? date( 'H:i A', $this->value ) : '' ?>" />
		</p>

		<?php
	}
	
	public function parse_save_values() {
		
		$r = array();

		for ( $i = 0; $i < count( $this->values['date'] ); $i++ ) 
			if( ! empty( $this->values['date'][$i] ) )
				$r[$i] = strtotime( $this->values['date'][$i] . ' ' . $this->values['time'][$i] );

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

		<p>

			<?php if ( ! $this->value ) : ?>
				<?php echo '<input class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="" />'; ?>
			
			<?php else : ?>

				<?php echo '<div class="hidden"><input disabled class="cmb_oembed code" type="text" name="', $this->name, '" id="',$this->name, '" value="" /></div>'; ?>

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
			<input class="cmb_colorpicker cmb_text_small" type="text" name="<?php echo $this->name; ?>" value="<?php echo $this->get_value() ?>" />
		</p>
		<?php
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

		$this->args = wp_parse_args( $this->args, array( 'multiple' => false, 'ajax_url' => '' ) );
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
		?>
		<p>
			<?php if ( $this->args['ajax_url'] ) : ?>
				<input value="<?php echo implode( ',' , (array) $this->value ) ?>" name="<?php echo $this->name ?>" style="width: 100%" class="<?php echo $id ?>" id="<?php echo $id ?>" />
			<?php else : ?>
				<select <?php echo ! empty( $this->args['multiple'] ) ? 'multiple' : '' ?> class="<?php echo $id ?>" name="<?php echo $this->name ?>">

					<?php if ( ! empty( $this->args['allow_none'] ) ) : ?>

						<option value="">None</option>

					<?php endif; ?>
				
					<?php foreach ( $this->args['options'] as $value => $name ): ?>

					   <option <?php selected( $this->value, $value ) ?> value="<?php echo $value; ?>"><?php echo $name; ?></option>

					<?php endforeach; ?>

				</select>
			<?php endif; ?>
		</p>
		<script>
			jQuery( document ).ready( function() {

				var options = { placeholder: "Type to search" };

				<?php if ( $this->args['ajax_url'] ) : ?>
					var query = JSON.parse( '<?php echo json_encode( $this->args['ajax_args'] ? $this->args['ajax_args'] : (object) array() ) ?>' );
					var posts = [];

					<?php if ( $this->args['multiple'] ) : ?>
						options.multiple = true;
					<?php endif; ?>
					<?php foreach ( array_filter( (array) $this->value ) as $post_id ) : ?>
						posts.push( { id: <?php echo $post_id ?>, text: '<?php echo get_the_title( $post_id ) ?>' } )
					<?php endforeach; ?>

					options.ajax = {
						url: '<?php echo $this->args['ajax_url'] ?>',
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
					jQuery( '.<?php echo $id ?>' ).each( function( index, el ) {

						if ( jQuery(el).is(':visible') && ! jQuery( el ).hasClass( 'select2-added' ) ) {
							jQuery( this ).addClass('select2-added').select2( options );

						}
					});
				}, 300 );

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
	
	public function title() {

	}
	  
	public function html() {
		?>
		<p>
			<label>
				<input type="checkbox" name="checkbox_<?php echo $this->name ?>" value="1" <?php checked( $this->get_value() ); ?> />
				<?php echo $this->args['name'] ?>
			</label>
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

	public function html() {}

}

/**
 * wysiwyg field.
 *
 */
class CMB_wysiwyg extends CMB_Field {

	public function html() {

		?>
		<p>
			<?php wp_editor( $this->get_value(), $this->name, $this->args['options'] );?>
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

add_action( 'wp_ajax_cmb_post_select', 'cmb_ajax_post_select' );

function cmb_ajax_post_select() {

	$posts = get_posts( $_GET );

	$json = array();

	foreach ( $posts as $post )
		$json[] = array( 'id' => $post->ID, 'text' => get_the_title( $post->ID ) );

	echo json_encode( $json );

	exit;
});


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
			
				// Todo support for repeatble fields in groups
				$this->add_field( new $class( $f['uid'], $f['name'], (array) $field_value, $f ) ); 
				
			}
		}

	}

	public function enqueue_scripts() {
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
			<h2 class="group-name"><?php echo $this->args['name'] ?></h2>
		<?php endif;

		foreach ( $meta as $value ) {
			$this->value = $value;
			echo '<div class="field-item" style="' . $this->args['style'] . '">';
			$this->html();
			echo '</div>';

			?>

			<?php

		}

		if ( $this->args['repeatable'] ) {
			$this->value = '';
			echo '<div class="field-item hidden" style="' . $this->args['style'] . '">';
			$this->html();
			echo '</div>';
			?>
			<p style="margin-top: 12px;">
				<a href="#" class="button repeat-field">Add New</a>
			</p>
			<?php
		}
	}

	public function add_field( CMB_Field $field ) {

		$key = $field->id;
		$field->original_id = $key;
		$field->id = $this->id . '[' . $field->id . '][]';
		$field->name = $field->id . '[]';
		$this->fields[$key] = $field;
	}

	public function html() {

		$field = $this->args;
		$value = $this->value;

		if ( ! empty( $value ) )
			foreach ( $value as $field => $field_value )
				if ( ! empty( $field ) && ! empty( $this->fields[$field] ) )
					$this->fields[$field]->set_values( (array) $field_value );

		?>
		<div class="group <?php echo !empty( $field['repeatable'] ) ? 'cloneable' : '' ?>" style="position: relative">

			<?php if ( $this->args['repeatable'] ) : ?>
				<a class="delete-field button" style="position: absolute; top: -3px; right: -3px">X</a>
			<?php endif; ?>

			<?php CMB_Meta_Box::layout_fields( $this->fields ); ?>

		</div>

		<?php
	}

	public function parse_save_values() {
		$values = $this->values;

		$this->values = array();

		$first = reset( $values );

		foreach ( $first as $key => $field_val ) {

			$meta = array();

			foreach ( $this->fields as $field ) {

				// create the fiel object so it can sanitize it's data etc
				$field->values = (array) $values[$field->original_id][$key];
				$field->parse_save_values();

				// if the field is a repeatable field, store the whole array of them, if it's not repeatble, 
				// just store the first (and only) one directly
				if ( $field->args['repeatable'] )
					$meta[$field->original_id] = $field->values;
				else
					$meta[$field->original_id] = reset( $field->values );
			}

			if( $this->isNotEmptyArray( $meta ) )
				$this->values[] = $meta;

		}


		if ( $this->args['repeatable'] ) {
			end( $this->values );
			unset( $this->values[key( $this->values )] );
			reset( $this->values );

		}
	}
	
	private function isNotEmptyArray( $array ) {

		foreach ($array as &$value){
			if ( is_array( $value) ) {
				$value = $this->isNotEmptyArray($value);
			}
		}

		return array_filter($array);

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
