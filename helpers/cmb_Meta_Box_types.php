<?php

/**
 * CMB field types
 * @since  1.0.0
 */
class cmb_Meta_Box_types {

	/**
	 * @todo test taxonomy methods with non-post objects
	 * @todo test all methods with non-post objects
	 */

	/**
	 * A single instance of this class.
	 * @var   cmb_Meta_Box_types object
	 * @since 1.0.0
	 */
	public static $instance = null;

	/**
	 * An iterator value for repeatable fields
	 * @var   integer
	 * @since 1.0.0
	 */
	public static $iterator = 0;

	/**
	 * Holds cmb_valid_img_types
	 * @var   array
	 * @since 1.0.0
	 */
	public static $valid = array();

	/**
	 * Current field type
	 * @var   string
	 * @since 1.0.0
	 */
	public static $type = 'text';

	/**
	 * Current field
	 * @var   array
	 * @since 1.0.0
	 */
	public static $field;

	/**
	 * Current field meta value
	 * @var   mixed
	 * @since 1.0.0
	 */
	public static $meta;

	/**
	 * Creates or returns an instance of this class.
	 * @since  1.0.0
	 * @return cmb_Meta_Box_types A single instance of this class.
	 */
	public static function get() {
		if ( self::$instance === null )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Generates a field's description markup
	 * @since  1.0.0
	 * @param  string  $desc      Field's description
	 * @param  boolean $paragraph Paragraph tag or span
	 * @return strgin             Field's description markup
	 */
	private static function desc( $paragraph = false ) {
		// Prevent description from printing multiple times for repeatable fields
		if ( self::$iterator > 0 ) {
			return '';
		}

		$tag = $paragraph ? 'p' : 'span';
		$desc = cmb_Meta_Box::$field['desc'];
		return "\n<$tag class=\"cmb_metabox_description\">$desc</$tag>\n";
	}

	/**
	 * Generates repeatable fields
	 * @since  1.0.0
	 * @param  array   $field Metabox field
	 * @param  mixed   $meta  Field's meta value
	 * @param  int     $object_id Object ID
	 * @param  string  $object_type  Object Type
	 */
	public static function render_repeatable_field( $field, $meta, $object_id, $object_type ) {

		// check for default content
		$default = isset( $field['default'] ) ? array( $field['default'] ) : false;
		// check for saved data
		if ( !empty( $meta ) ) {
			$meta = is_array( $meta ) ? array_filter( $meta ) : $meta;
			$meta = ! empty( $meta ) ? $meta : $default;
		} else {
			$meta = $default;
		}

		self::repeat_table_open( $class );

		if ( !empty( $meta ) ) {
			foreach ( (array) $meta as $val ) {
				self::open_repeat_row();
				self::$iterator = self::$iterator ? self::$iterator + 1 : 1;
				call_user_func( array( self::$instance, $field['type'] ), $field, $val, $object_id, $object_type );
				self::close_repeat_row();
			}
		} else {
			self::open_repeat_row();
			self::$iterator = 1;
			call_user_func( array( self::$instance, $field['type'] ), $field, $meta, $object_id, $object_type );
			self::close_repeat_row();
		}

		self::open_empty_row();
		self::$iterator = self::$iterator ? self::$iterator + 1 : 1;
		call_user_func( array( self::$instance, $field['type'] ), $field, null, $object_id, $object_type );
		self::close_repeat_row();

		self::repeat_table_close();
		// reset iterator
		self::$iterator = 0;
	}

	/**
	 * Generates repeatable field opening table markup for repeatable fields
	 * @since  1.0.0
	 * @param  string $class Field's class attribute
	 */
	private static function repeat_table_open( $class = '' ) {
		echo self::desc(), '<table id="', cmb_Meta_Box::$field['id'], '_repeat" class="cmb-repeat-table ', $class ,'"><tbody>';
	}

	/**
	 * Generates repeatable feild closing table markup for repeatable fields
	 * @since 1.0.0
	 */
	private static function repeat_table_close() {
		echo '</tbody></table><p class="add-row"><a data-selector="', cmb_Meta_Box::$field['id'] ,'_repeat" class="add-row-button button" href="#">'. __( 'Add Row', 'cmb' ) .'</a></p>';
	}

	private static function open_repeat_row() {
		echo '<tr class="repeat-row">';
		echo '<td>';
	}

	private static function open_empty_row() {
		echo '<tr class="empty-row">';
		echo '<td>';
	}

	private static function close_repeat_row() {
		echo '</td><td class="remove-row"><a class="button remove-row-button" href="#">'. __( 'Remove', 'cmb' ) .'</a></td>';
		echo '</tr>';
	}

	/**
	 * Determine a file's extension
	 * @since  1.0.0
	 * @param  string       $file File url
	 * @return string|false       File extension or false
	 */
	public static function get_file_ext( $file ) {
		$parsed = @parse_url( $file, PHP_URL_PATH );
		return $parsed ? strtolower( pathinfo( $parsed, PATHINFO_EXTENSION ) ) : false;
	}

	/**
	 * Determines if a file has a valid image extension
	 * @since  1.0.0
	 * @param  string $file File url
	 * @return bool         Whether file has a valid image extension
	 */
	public static function is_valid_img_ext( $file ) {
		$file_ext = self::get_file_ext( $file );

		self::$valid = empty( self::$valid ) ? (array) apply_filters( 'cmb_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ) ) : self::$valid;

		return ( $file_ext && in_array( $file_ext, self::$valid ) );
	}


	/**
	 * Begin Field Types
	 */

	public static function text( $field, $meta ) {
		echo '<input type="text" class="regular-text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc( true );
	}

	public static function text_small( $field, $meta ) {
		echo '<input class="cmb_text_small" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc();
	}

	public static function text_medium( $field, $meta ) {
		echo '<input class="cmb_text_medium" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc();
	}

	public static function text_email( $field, $meta ) {
		echo '<input class="cmb_text_email cmb_text_medium" type="email" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc( true );
	}

	public static function text_url( $field, $meta ) {
		echo '<input class="cmb_text_url cmb_text_medium regular-text" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', $meta, '" />', self::desc( true );
	}

	public static function text_date( $field, $meta ) {
		echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc();
	}

	public static function text_date_timestamp( $field, $meta ) {
		echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? date( 'm\/d\/Y', $meta ) : $field['default'], '" />', self::desc();
	}

	public static function text_datetime_timestamp( $field, $meta, $object_id ) {
		// This will be used if there is a select_timezone set for this field
		$tz_offset = cmb_Meta_Box::field_timezone_offset( $object_id );
		if ( !empty( $tz_offset ) ) {
			$meta -= $tz_offset;
		}

		echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '[date]" id="', $field['id'], '_date', $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? date( 'm\/d\/Y', $meta ) : $field['default'], '" />';
		echo '<input class="cmb_timepicker text_time" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '[time]" id="', $field['id'], '_time', $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? date( 'h:i A', $meta ) : $field['default'], '" />', self::desc();
	}

	public static function text_datetime_timestamp_timezone( $field, $meta ) {
		$datetime = unserialize($meta);
		$meta = $tzstring = false;

		if ( $datetime && $datetime instanceof DateTime ) {
			$tz = $datetime->getTimezone();
			$tzstring = $tz->getName();

			$meta = $datetime->getTimestamp() + $tz->getOffset( new DateTime('NOW') );
		}

		echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '[date]" id="', $field['id'], '_date', $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? date( 'm\/d\/Y', $meta ) : $field['default'], '" />';
		echo '<input class="cmb_timepicker text_time" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '[time]" id="', $field['id'], '_time', $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? date( 'h:i A', $meta ) : $field['default'], '" />';

		echo '<select name="', $field['id'], $field['repeatable'] ? '[]' : '', '[timezone]" id="', $field['id'], '_timezone', $field['repeatable'] ? '_'.self::$iterator : '', '">';
		echo wp_timezone_choice( $tzstring );
		echo '</select>', self::desc();
	}

	public static function text_time( $field, $meta ) {
		echo '<input class="cmb_timepicker text_time" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc();
	}

	public static function select_timezone( $field, $meta ) {
		$meta = ! empty( $meta ) ? $meta : $field['default'];
		if ( '' === $meta )
			$meta = cmb_Meta_Box::timezone_string();

		echo '<select name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '">';
		echo wp_timezone_choice( $meta );
		echo '</select>';
	}

	public static function text_money( $field, $meta ) {
		echo ! empty( $field['before'] ) ? '' : '$', ' <input class="cmb_text_money" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" />', self::desc();
	}

	public static function colorpicker( $field, $meta ) {
		$meta = ! empty( $meta ) ? $meta : $field['default'];
		$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
		if ( preg_match( '/^' . $hex_color . '/i', $meta ) ) // Value is just 123abc, so prepend #.
			$meta = '#' . $meta;
		elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta ) ) // Value doesn't match #123abc, so sanitize to just #.
			$meta = "#";
		echo '<input class="cmb_colorpicker cmb_text_small" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', $meta, '" />', self::desc();
	}

	public static function textarea( $field, $meta ) {
		echo '<textarea name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" cols="60" rows="10">', ! empty( $meta ) ? $meta : $field['default'], '</textarea>', self::desc( true );
	}

	public static function textarea_small( $field, $meta ) {
		echo '<textarea name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" cols="60" rows="4">', ! empty( $meta ) ? $meta : $field['default'], '</textarea>', self::desc( true );
	}

	public static function textarea_code( $field, $meta ) {
		echo '<pre><textarea name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" cols="60" rows="10" class="cmb_textarea_code">', ! empty( $meta ) ? $meta : $field['default'], '</textarea></pre>', self::desc( true );
	}

	public static function select( $field, $meta ) {
		if ( empty( $meta ) && !empty( $field['default'] ) ) $meta = $field['default'];
		echo '<select name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '">';
		foreach ($field['options'] as $option) {
			echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
		}
		echo '</select>', self::desc( true );
	}

	public static function radio( $field, $meta ) {
		if ( empty( $meta ) && !empty( $field['default'] ) ) $meta = $field['default'];
		echo '<ul>';
		$i = 1;
		foreach ($field['options'] as $option) {
			echo '<li class="cmb_option"><input type="radio" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', $i,'" value="', $option['value'], '" ', checked( $meta == $option['value'] ), ' /> <label for="', $field['id'], $i, '">', $option['name'].'</label></li>';
			$i++;
		}
		echo '</ul>', self::desc( true );
	}

	public static function radio_inline( $field, $meta ) {
		self::radio( $field, $meta );
	}

	public static function checkbox( $field, $meta ) {
		echo '<input class="cmb_option" type="checkbox" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" ', checked( ! empty( $meta ) ), ' value="on"/> <label for="', $field['id'], '">', self::desc() ,'</label>';
	}

	public static function multicheck( $field, $meta ) {
		echo '<ul>';
		$i = 1;
		foreach ( $field['options'] as $value => $name ) {
			echo '<li><input class="cmb_option" type="checkbox" name="', $field['id'], $field['repeatable'] ? '[]' : '', '[]" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', $i, '" value="', $value, '" ', checked( is_array( $meta ) && in_array( $value, $meta ) ), '  /> <label for="', $field['id'], $i, '">', $name, '</label></li>';
			$i++;
		}
		echo '</ul>', self::desc();
	}

	public static function multicheck_inline( $field, $meta ) {
		self::multicheck( $field, $meta );
	}

	public static function title( $field, $meta, $object_id, $object_type ) {
		$tag = $object_type == 'post' ? 'h5' : 'h3';
		echo '<'. $tag .' class="cmb_metabox_title">', $field['name'], '</'. $tag .'>', self::desc( true );
	}

	public static function wysiwyg( $field, $meta ) {
		wp_editor( $meta ? $meta : $field['default'], $field['id'], isset( $field['options'] ) ? $field['options'] : array() );
		echo self::desc( true );
	}

	public static function taxonomy_select( $field, $meta, $object_id ) {

		echo '<select name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '">';
		$names = wp_get_object_terms( $object_id, $field['taxonomy'] );
		$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
		foreach ( $terms as $term ) {
			if ( !is_wp_error( $names ) && !empty( $names ) && ! strcmp( $term->slug, $names[0]->slug ) ) {
				echo '<option value="' . $term->slug . '" selected>' . $term->name . '</option>';
			} else {
				echo '<option value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name . '</option>';
			}
		}
		echo '</select>', self::desc( true );
	}

	public static function taxonomy_radio( $field, $meta, $object_id ) {
		$names = wp_get_object_terms( $object_id, $field['taxonomy'] );
		$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
		echo '<ul>';
		$i = 1;
		foreach ( $terms as $term ) {
			$checked = ( !is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) );

			echo '<li class="cmb_option"><input type="radio" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', $i,'" value="'. $term->slug . '" ', checked( $checked ), ' /> <label for="', $field['id'], $i, '">' . $term->name . '</label></li>';
			$i++;
		}
		echo '</ul>', self::desc( true );
	}

	public static function taxonomy_radio_inline( $field, $meta ) {
		self::taxonomy_radio( $field, $meta );
	}

	public static function taxonomy_multicheck( $field, $meta, $object_id ) {
		echo '<ul>';
		$names = wp_get_object_terms( $object_id, $field['taxonomy'] );
		$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
		$i = 1;
		foreach ( $terms as $term ) {
			echo '<li><input class="cmb_option" type="checkbox" name="', $field['id'], $field['repeatable'] ? '[]' : '', '[]" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', $i,'" value="'. $term->slug . '" ';
			foreach ($names as $name) {
				checked( $term->slug == $name->slug );
			}

			echo ' /> <label for="', $field['id'], $i, '">' . $term->name . '</label></li>';
			$i++;
		}
		echo '</ul>', self::desc();
	}

	public static function taxonomy_multicheck_inline( $field, $meta ) {
		self::taxonomy_multicheck( $field, $meta );
	}

	public static function file_list( $field, $meta, $object_id ) {
		echo '<input class="cmb_upload_file cmb_upload_list" type="hidden" size="45" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" name="', $field['id'], '" value="', $meta, '" />';
		echo '<input class="cmb_upload_button button cmb_upload_list" type="button" value="'. __( 'Add or Upload File', 'cmb' ) .'" />', self::desc( true );

		echo '<ul id="', $field['id'], '_status', $field['repeatable'] ? '_'.self::$iterator : '', '" class="cmb_media_status attach_list">';

		if ( $meta ) {

			foreach ( $meta as $id => $fullurl ) {
				if ( self::is_valid_img_ext( $fullurl ) ) {
					echo
					'<li class="img_status">',
						wp_get_attachment_image( $id, array( 50, 50 ) ),
						'<p><a href="#" class="cmb_remove_file_button">'. __( 'Remove Image', 'cmb' ) .'</a></p>
						<input type="hidden" id="filelist-', $id ,'" name="', $field['id'] ,'[', $id ,']" value="', $fullurl ,'" />
					</li>';

				} else {
					$parts = explode( '/', $fullurl );
					for ( $i = 0; $i < count( $parts ); ++$i ) {
						$title = $parts[$i];
					}
					echo
					'<li>',
						__( 'File:', 'cmb' ), ' <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $fullurl, '" target="_blank" rel="external">'. __( 'Download', 'cmb' ) .'</a> / <a href="#" class="cmb_remove_file_button">'. __( 'Remove', 'cmb' ) .'</a>)
						<input type="hidden" id="filelist-', $id ,'" name="', $field['id'] ,'[', $id ,']" value="', $fullurl ,'" />
					</li>';
				}
			}
		}

		echo '</ul>';
	}

	public static function file( $field, $meta, $object_id, $object_type ) {
		$input_type_url = 'hidden';
		if ( 'url' == $field['allow'] || ( is_array( $field['allow'] ) && in_array( 'url', $field['allow'] ) ) )
			$input_type_url = 'text';
		echo '<input class="cmb_upload_file" type="' . $input_type_url . '" size="45" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" value="', $meta, '" />';
		echo '<input class="cmb_upload_button button" type="button" value="'. __( 'Add or Upload File', 'cmb' ) .'" />';

		$_id_name = $field['id'];
		if ( $field['repeatable'] ) {
			$_id_name .= '_' . self::$iterator;
		} 
		$_id_name .= '_id';

		$_id_meta = cmb_Meta_Box::get_data( $_id_name );

		// If there is no ID saved yet, try to get it from the url
		if ( $meta && ! $_id_meta ) {
			$_id_meta = cmb_Meta_Box::image_id_from_url( esc_url_raw( $meta ) );
		}

		echo '<input class="cmb_upload_file_id" type="hidden" id="', $_id_name, '" name="', $_id_name, '" value="', $_id_meta, '" />',
		self::desc( true ),
		'<div id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '_status" class="cmb_media_status">';
			if ( ! empty( $meta ) ) {

				if ( self::is_valid_img_ext( $meta ) ) {
					echo '<div class="img_status">';
					echo '<img style="max-width: 350px; width: 100%; height: auto;" src="', $meta, '" alt="" />';
					echo '<p><a href="#" class="cmb_remove_file_button" rel="', $field['id'], '">'. __( 'Remove Image', 'cmb' ) .'</a></p>';
					echo '</div>';
				} else {
					// $file_ext = self::get_file_ext( $meta );
					$parts = explode( '/', $meta );
					for ( $i = 0; $i < count( $parts ); ++$i ) {
						$title = $parts[$i];
					}
					echo __( 'File:', 'cmb' ), ' <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">'. __( 'Download', 'cmb' ) .'</a> / <a href="#" class="cmb_remove_file_button" rel="', $field['id'], '">'. __( 'Remove', 'cmb' ) .'</a>)';
				}
			}
		echo '</div>';
	}

	public static function oembed( $field, $meta, $object_id, $object_type ) {
		echo '<input class="cmb_oembed regular-text" type="text" name="', $field['id'], $field['repeatable'] ? '[]' : '', '" id="', $field['id'], $field['repeatable'] ? '_'.self::$iterator : '', '" value="', ! empty( $meta ) ? $meta : $field['default'], '" data-objectid="', $object_id ,'" data-objecttype="', $object_type ,'" />', self::desc( true );
		echo '<p class="cmb-spinner spinner" style="display:none;"><img src="'. admin_url( '/images/wpspin_light.gif' ) .'" alt="spinner"/></p>';
		echo '<div id="', $field['id'], '_status', $field['repeatable'] ? '_'.self::$iterator : '', '" class="cmb_media_status ui-helper-clearfix embed_wrap">';

			if ( $meta != '' )
				echo cmb_Meta_Box_ajax::get_oembed( $meta, $object_id, array(
					'object_type' => $object_type,
					'oembed_args' => array( 'width' => '640' ),
					'field_id'    => $field['id'].$field['repeatable'] ? '_'.self::$iterator : '',
				) );

		echo '</div>';
	}


	/**
	 * Default fallback. Allows rendering fields via "cmb_render_$name" hook
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		list( $field, $meta, $object_id, $object_type ) = $arguments;
		// When a non-registered field is called, send it through an action.
		do_action( "cmb_render_$name", $field, $meta, $object_id, $object_type );
	}

}
