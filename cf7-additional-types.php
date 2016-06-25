<?php
/**
 * Plugin Name: Contact Form 7 – Additional Types
 * Plugin URI: http://jankim.com/
 * Description: Additional input types for the Contact Form 7 plugin. Currently implemented: rangeslider
 * Version: 0.9b
 * Author: Janis Freimann
 * Author URI: http://jankim.com/
 * Developer: Janis Freimann
 * Developer E-Mail: janis.freimann@gmail.com
 * Text Domain: cf7-additional-types
 * Domain Path: /languages
 *
 * Copyright: © 2016 Janis Freimann
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Credits:
 *
 * this plugin uses Ion.RangeSlider, distributed under the MIT license:
 *
    Copyright (C) 2016 by Denis Ineshin

    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
*/

if( !defined('ABSPATH') ) exit;

final class CF7_AdditionalTypes {
    const PLUGIN_NAME = "Contact Form 7 – Additional Types";
    const PLUGIN_VERSION = "0.9b";
    private $types;

    function __construct() {
        $this->types = array('rangeslider');
    }
    
    function enqueue_scripts_styles() {
        wp_enqueue_style( 'wpcf7_rangeslider_stylesheet', plugin_dir_url( __FILE__ ).'assets/style.css' );

        wp_register_script('ion_rangeslider', plugin_dir_url( __FILE__ ).'assets/ion.rangeSlider.min.js' );

        wp_enqueue_script('wpcf7_rangeslider_js', plugin_dir_url( __FILE__ ).'assets/rangeslider.js', array('jquery', 'ion_rangeslider'));
    }

    function register_cf7_shortcodes() {
        foreach($this->types as $item) {
            $func = 'wpcf7_'.$item.'_shortcode_handler';

            if(!function_exists($func))
                $this->_stop_and_deactivate();
            else
                wpcf7_add_shortcode( array($item, $item.'*'), $func, true );
        }
    }
    
    function add_tag_generator() {
        $tag_generator = WPCF7_TagGenerator::get_instance();
        foreach($this->types as $item) {
            $func = 'wpcf7_tag_generator_'.$item;

            if(!function_exists($func))
                $this->_stop_and_deactivate();
            else
                $tag_generator->add( $item, $item, $func );
        }
    }

    private function _stop_and_deactivate() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf( __('This version of %s is broken or incompatible with your WordPress installation. You can try to reinstall it from the WordPress repository.', 'cf7-additional-types'), self::PLUGIN_NAME ) );
    }
}

$cf7_additional_types = new CF7_AdditionalTypes();
add_action( 'wpcf7_init', array($cf7_additional_types, 'register_cf7_shortcodes') );
add_action( 'wp_enqueue_scripts', array($cf7_additional_types, 'enqueue_scripts_styles') );
add_action( 'wpcf7_admin_init', array($cf7_additional_types, 'add_tag_generator'), 18 );


function wpcf7_rangeslider_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) ) return '';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-validates-as-rangeslider';

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
            $labels .= '<span data-index="'.$i.'" class="wpcf7-form-rangeslider-label '.($default!=$i?'hidden':'').'">'.$values[$i].'</span>';
        }

        $atts = wpcf7_format_atts( $atts );
        $html = sprintf('<span class="wpcf7-form-control-wrap wpcf7-form-rangeslider-wrap %1$s"><input %2$s>%3$s</span>',
            sanitize_html_class( $tag->name ), $atts, $validation_error );
    }

    return $html;
}


function wpcf7_tag_generator_rangeslider( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'rangeslider';
	$description = "Generate a range slider.";
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
