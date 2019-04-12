<?php

final class CF7_AdditionalTypes_Datepicker {
	public $title;
	public $type;
	public $supports_required = true;

	public function __construct( $type ) {
		$this->title = _x( 'Date Picker', 'Tag Label', CF7AT_TEXTDOMAIN );
		$this->type  = $type;
		$cf7at       = CF7_AdditionalTypes::get_instance();
		$cf7at->add_js_asset( 'momentjs', 'moment-with-locales.min.js' );
		$cf7at->add_js_asset( 'ion_calendar', 'ion.calendar.min.js', [ 'jquery', 'momentjs' ], true );

		if ( is_admin() ) {
			global $pagenow;
			if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpcf7' ) {
				$cf7at->add_js_asset( 'momentjs', 'moment-with-locales.min.js', [], true, true );
				$cf7at->add_js_asset( 'ion_calendar', 'ion.calendar.min.js', [ 'jquery', 'momentjs' ], true, true );
				$cf7at->load_css_on_admin();
				add_action( 'admin_print_scripts', [ $this, 'admin_footer_script' ], 90 );
			}
		}
	}

	private function php2moment( $str ) {
		$replacements = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent (Number of days in the given month)
			'L' => '', // no equivalent (Leap year)
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent (Swatch Internet time)
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since Moment.js 1.6.0 (Timezone identifier)
			'I' => '', // no equivalent (Whether or not the date is in daylight saving time)
			'O' => '', // no equivalent (Difference to Greenwich time (GMT) in hours)
			'P' => '', // no equivalent (Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3))
			'T' => '', // no equivalent (Timezone abbreviation)
			'Z' => '', // no equivalent (Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.)
			'c' => '', // no equivalent (ISO 8601 date)
			'r' => '', // no equivalent (RFC 2822 formatted date)
			'U' => 'X',
		];

		$result = '';
		for ( $i = 0;$i < strlen( $str );$i++ ) {
			$result .= isset( $replacements[ $str[ $i ] ] ) ? $replacements[ $str[ $i ] ] : $str[ $i ];
		}

		return $result;
	}

	// Datepicker Shortcode Handler
	public function shortcode_handler( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type );

		$class .= ' wpcf7-text wpcf7-validates-as-' . $this->type;

		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = [];

		$atts['class']    = $tag->get_class_option( $class );
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
		$atts['size']     = 40;

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$values = [
			'default' => $tag->get_option( 'default', '', true ),
			'years'   => $tag->get_option( 'years', '', true ),
		];
		$lang   = $locale = get_locale();
		if ( strpos( $locale, '_' ) ) {
			$lang = explode( '_', $locale )[0];
		}
		$sundayFirst = intval( get_option( 'start_of_week' ) ) == 0;
		$format      = get_option( 'date_format' );

		$html = '';

		$atts['type']             = 'text';
		$atts['name']             = $tag->name;
		$atts['data-years']       = ! empty( $values['years'] ) ? $values['years'] : '80';
		$atts['data-lang']        = $lang;
		$atts['data-format']      = $this->php2moment( $format );
		$atts['data-sundayFirst'] = var_export( $sundayFirst, true );
		if ( ! empty( $values['default'] ) ) {
			$atts['data-startDate'] = $values['default'];
			$atts['value']          = $values['default'];
		}

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap wpcf7-form-datepicker-wrap %1$s"><input %2$s>%3$s</span>',
			sanitize_html_class( $tag->name ),
			$atts,
			$validation_error
		);

		return $html;
	}

	function validation_filter( $result, $tag ) {
		$name = $tag->name;

		if ( isset( $_POST[ $name ] ) && is_array( $_POST[ $name ] ) ) {
			foreach ( $_POST[ $name ] as $key => $value ) {
				if ( '' === $value ) {
					unset( $_POST[ $name ][ $key ] );
				}
			}
		}

		$empty = ! isset( $_POST[ $name ] ) || empty( $_POST[ $name ] ) && '0' !== $_POST[ $name ];

		if ( $tag->is_required() && $empty ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
		}

		return $result;
	}

	// Datepicker Tag Generator
	function tag_generator( $contact_form, $args = '' ) {
		$args        = wp_parse_args( $args, [] );
		$description = __( 'Generate a date picker.', CF7AT_TEXTDOMAIN );
		?>
<div class="control-box"><fieldset>
	<legend><?php echo esc_html( $description ); ?></legend>

	<table class="form-table"><tbody>
		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>"></td></tr>

		<tr><th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th><td>
			<fieldset>
			<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
			<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
			</fieldset>
		</td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-range-type' ); ?>"><?php echo esc_html( _x( 'Years Range', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></label></th><td>
			<select name="range_type" class="oneline" id="<?php echo esc_attr( $args['content'] . '-range-type' ); ?>">
				<option value="noy"><?php echo esc_html( _x( 'Number of Years', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></option>
				<option value="range"><?php echo esc_html( _x( 'Range', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></option>
			</select>
		</td></tr>

		<tr id="<?php echo esc_attr( $args['content'] . '-noy-row' ); ?>">
			<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-noy' ); ?>"><?php echo esc_html( _x( 'Number of Years', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></label></th>
			<td>
				<input type="text" name="noy" class="oneline" id="<?php echo esc_attr( $args['content'] . '-noy' ); ?>" placeholder="80"><br>
				<span class="description"><?php echo esc_html( _x( 'The range will be the number of years counted back from the current one. Useful for birthdays to give a realistic range.', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></span>
			</td>
		</tr>

		<tr id="<?php echo esc_attr( $args['content'] . '-range-row' ); ?>" style="display:none">
			<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-range-from' ); ?>"><?php echo esc_html( _x( 'Range', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></label></th>
			<td>
				<input type="text" name="range_from" style="width:90px" id="<?php echo esc_attr( $args['content'] . '-range-from' ); ?>"> –
				<input type="text" name="range_to" style="width:90px" id="<?php echo esc_attr( $args['content'] . '-range-to' ); ?>">
			</td>
		</tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-default' ); ?>"><?php echo esc_html( _x( 'Default Date', 'Datepicker Tag Generator', CF7AT_TEXTDOMAIN ) ); ?></label></th>
		<td><input type="text" name="default" class="oneline cf7at-ion-calendar" id="<?php echo esc_attr( $args['content'] . '-default' ); ?>"></td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>"></td></tr>

		<tr><th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>"></td></tr>
	</tbody></table>
</fieldset></div>

<div class="insert-box">
	<input type="text" name="<?php echo $this->type; ?>" class="tag code" readonly="readonly" onfocus="this.select()">

	<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>">
	</div>

	<br class="clear">

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( 'To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.', 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"></label></p>
</div>
<script>jQuery(document).ready(function($) {
	$('#<?php echo esc_attr( $args['content'] . '-range-type' ); ?>').on('change', function() {
		var prefix = '#<?php echo esc_attr( $args['content'] ); ?>-';

		$(prefix+'noy').add(prefix+'range-from').add(prefix+'range-to').val("");
		if(this.value == "noy") {
			$(prefix+'noy-row').show();
			$(prefix+'range-row').hide();
		} else if(this.value == "range") {
			$(prefix+'noy-row').hide();
			$(prefix+'range-row').show();
		}
	});
	$('#<?php echo esc_attr( $args['content'] . '-noy' ); ?>').on('change', function() {
		$('#<?php echo esc_attr( $args['content'] . '-default' ); ?>').data('years', this.value);
	});
});</script>
		<?php
	}

	public function admin_footer_script() {
		$lang = $locale = get_locale();
		if ( strpos( $locale, '_' ) ) {
			$lang = explode( '_', $locale )[0];
		}
		$sundayFirst = intval( get_option( 'start_of_week' ) ) == 0;
		$format      = get_option( 'date_format' );
		?>
<style>
.ic__datepicker {z-index:200000;}
</style>
<script>
jQuery(document).ready(function($) {
	$(".cf7at-ion-calendar").ionDatePicker({
		"lang": "<?php echo $lang; ?>",
		"format": "<?php echo $this->php2moment( $format ); ?>",
		"sundayFirst": <?php var_export( $sundayFirst ); ?>
	});
});
</script>
		<?php
	}
}
