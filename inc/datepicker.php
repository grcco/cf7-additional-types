<?php

// To-Do
//
// Continue here

// https://github.com/IonDen/ion.calendar/


// Datepicker Shortcode Handler
function wpcf7_datepicker_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) ) return '';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-validates-as-datepicker';

	if ( $validation_error )
		$class .= ' wpcf7-not-valid';

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    if ( $tag->is_required() ) $atts['aria-required'] = 'true';

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $values = $tag->values;
	if ( $data = (array) $tag->get_data_option() ) {
		$values = array_merge( $values, array_values( $data ) );
	}

    $html = null;
    if(count($values) > 0) {
        $default = intval($tag->get_option( 'default', 'int', true ))-1;
        if(!isset($values[$default])) $default = 0;

        $atts['type'] = 'text';
        $atts['name'] = $tag->name;
        $atts['value'] = $values[$default];
        $atts['data-min'] = 1;
        $atts['data-max'] = count($values);
        $atts['data-values-json'] = '[\''.implode("','", $values).'\']';
        $atts['data-from'] = $default;
        $labels = '';

        for($i=0;$i<count($values);$i++) {
            $labels .= '<span data-index="'.$i.'" class="wpcf7-form-datepicker-label '.($default!=$i?'hidden':'').'">'.$values[$i].'</span>';
        }

        $atts = wpcf7_format_atts( $atts );
        $html = sprintf('<span class="wpcf7-form-control-wrap wpcf7-form-datepicker-wrap %1$s"><input %2$s>%3$s</span>',
            sanitize_html_class( $tag->name ), $atts, $validation_error );
    }

    return $html;
}

/* Validation filter */
/*function wpcf7_rangeslider_validation_filter( $result, $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	$name = $tag->name;

	if ( isset( $_POST[$name] ) && is_array( $_POST[$name] ) ) {
		foreach ( $_POST[$name] as $key => $value ) {
			if ( '' === $value )
				unset( $_POST[$name][$key] );
		}
	}

	$empty = ! isset( $_POST[$name] ) || empty( $_POST[$name] ) && '0' !== $_POST[$name];

	if ( $tag->is_required() && $empty ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}

	return $result;
}*/


// Datepicker Tag Generator
function wpcf7_tag_generator_datepicker( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'datepicker';
	$description = "Generate a datepicker.";
?>
<div class="control-box">
<fieldset>
<legend><?=esc_html( $description )?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

    <tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Values', 'contact-form-7' ) ); ?></label></th>
	<td><textarea name="values" class="values" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>"></textarea></td>
	</tr>

    <tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-default' ); ?>"><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?></label></th>
	<td><input type="number" name="default" class="defaultvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-default' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>
</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
<?php
}
