<?php

final class CF7_AdditionalTypes_Rangeslider {
	public $title;
    public $type;
    public $supports_required = false;

	public function __construct( $type ) {
		$this->title = _x( 'Range Slider', 'Tag Label', CF7AT_TEXTDOMAIN );
        $this->type  = $type;
		$cf7at       = CF7_AdditionalTypes::get_instance();
		$cf7at->add_js_asset( 'ion_rangeslider', 'ion.rangeSlider.min.js', [ 'jquery' ] );
	}

	public function shortcode_handler( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type );

		$class .= ' wpcf7-validates-as-'.$this->type;

		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = [];

		$atts['class']    = $tag->get_class_option( $class );
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$values = $tag->values;
		if ( $data = (array) $tag->get_data_option() ) {
			$values = array_merge( $values, array_values( $data ) );
		}

		$html = null;
		if ( count( $values ) > 0 ) {
			$default = intval( $tag->get_option( 'default', 'int', true ) ) - 1;
			if ( ! isset( $values[ $default ] ) ) {
				$default = 0;
			}

			$atts['type']             = 'text';
			$atts['name']             = $tag->name;
			$atts['value']            = $values[ $default ];
			$atts['data-min']         = 1;
			$atts['data-max']         = count( $values );
			$atts['data-values-json'] = '[\'' . implode( "','", $values ) . '\']';
			$atts['data-from']        = $default;
			$labels                   = '';

			for ( $i = 0;$i < count( $values );$i++ ) {
				$labels .= '<span data-index="' . $i . '" class="wpcf7-form-'.$this->type.'-label ' . ( $default != $i ? 'hidden' : '' ) . '">' . $values[ $i ] . '</span>';
			}

			$atts = wpcf7_format_atts( $atts );
			$html = sprintf(
				'<span class="wpcf7-form-control-wrap wpcf7-form-'.$this->type.'-wrap %1$s"><input %2$s>%3$s</span>',
				sanitize_html_class( $tag->name ),
				$atts,
				$validation_error
			);
		}

		return $html;
	}

	public function validation_filter( $result, $tag ) {
		$tag = new WPCF7_Shortcode( $tag );

		$name = $tag->name;

		$value = isset( $_POST[ $name ] )
			? trim( strtr( (string) $_POST[ $name ], "\n", ' ' ) )
			: '';

		// $min = $tag->get_option( 'min', 'signed_int', true );
		// $max = $tag->get_option( 'max', 'signed_int', true );

		/* if ( '' != $value && ! wpcf7_is_number( $value ) ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_number' ) );
		} elseif ( '' != $value && '' != $min && (float) $value < (float) $min ) {
			$result->invalidate( $tag, wpcf7_get_message( 'number_too_small' ) );
		} elseif ( '' != $value && '' != $max && (float) $max < (float) $value ) {
			$result->invalidate( $tag, wpcf7_get_message( 'number_too_large' ) );
		} */

		return $result;
	}

	public function tag_generator( $contact_form, $args = '' ) {
		$args        = wp_parse_args( $args, [] );
		$description = __( 'Generate a range slider.', CF7AT_TEXTDOMAIN );
		?>
<div class="control-box"><fieldset>
	<legend><?php echo esc_html( $description ); ?></legend>

	<table class="form-table"><tbody>
		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Values', 'contact-form-7' ) ); ?></label></th><td>
			<textarea name="values" class="values" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>"></textarea>
			<div><i>One value per line</i></div>
		</td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-default' ); ?>"><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?></label></th><td>
            <select name="default" class="defaultvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-default' ); ?>">
                <option value=""><?php echo esc_html( _x( 'None', 'Default Value Drop-Down', CF7AT_TEXTDOMAIN ) ); ?></option>
            </select>
        </td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th><td>
            <input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" />
        </td></tr>
	</tbody></table>
</fieldset></div>

<div class="insert-box">
	<input type="text" name="<?php echo $this->type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
        <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( 'To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.', 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
		<?php
	}

	/* public function messages( $messages ) {
		return array_merge(
			$messages,
			[
				'invalid_number'   => array(
					'description' => __( 'Number format that the sender entered is invalid', 'contact-form-7' ),
					'default'     => __( 'Number format seems invalid.', 'contact-form-7' ),
				),

				'number_too_small' => array(
					'description' => __( 'Number is smaller than minimum limit', 'contact-form-7' ),
					'default'     => __( 'This number is too small.', 'contact-form-7' ),
				),

				'number_too_large' => array(
					'description' => __( 'Number is larger than maximum limit', 'contact-form-7' ),
					'default'     => __( 'This number is too large.', 'contact-form-7' ),
				),
			]
		);
	} */
}
