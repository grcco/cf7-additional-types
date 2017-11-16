<?php
/**
 * Plugin Name: CF7 Additional Types
 * Plugin URI: https://github.com/meostudio/cf7-additional-types
 * Description: Additional input field types for Contact Form 7.
 * Version: 2.0.0a
 * Author: meo.studio
 * Author URI: https://meo.studio/
 * Developer: Janis Freimann
 * Developer E-Mail: janis@meo.studio
 * Text Domain: cf7-additional-types
 * Domain Path: /languages
 *
 * Copyright: © 2017 meostudio OÜ
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Credits:
 * this plugin uses Ion.RangeSlider and Ion.Calendar, distributed under the MIT license.
 *
*/

if( !defined('ABSPATH') ) exit;

final class CF7_AdditionalTypes {
    const PLUGIN_NAME = "CF7 Additional Types";
    const PLUGIN_VERSION = "2.0";
    const PLUGIN_TEXTDOMAIN = "cf7-additional-types";
    private $types = [
        'rangeslider',
    ];
    private $instances = [];
    private $assets = [];

    private static $instance;

    private function __construct() { }

    public function construct() {
        load_plugin_textdomain( CF7AT_TEXTDOMAIN, false, plugin_dir_url( __FILE__ ).'languages' );

        add_action( 'admin_init', [$this, 'dependencies_check'] );
        add_action( 'wpcf7_init', [$this, 'register_cf7_shortcodes'] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts_styles'] );
        add_action( 'wpcf7_admin_init', [$this, 'add_tag_generators'], 18 );

        foreach($this->types as $type) {
            require plugin_dir_path( __FILE__ ). "inc/{$type}.php";

            $classname = __CLASS__ . '_' . ucfirst($type);
            $this->instances[$type] = new $classname();
            $instance = &$this->instances[$type];

            if(!method_exists($instance, 'shortcode_handler') || !property_exists($instance, 'title')) {
                $this->_stop_and_deactivate();
            }
        }
    }

    public static function init() {
        if ( empty( self::$instance ) ) {
            self::$instance = new self;
            define( 'CF7AT_TEXTDOMAIN', self::PLUGIN_TEXTDOMAIN );

            self::$instance->construct();
        }
    }

    public static function get_instance() {
        if ( empty( self::$instance ) ) {
            self::init();
        }

        return self::$instance;
    }

    function add_js_asset($name, $filename, $dependepcies = [], $in_footer = false) {
        if(!is_array($dependepcies)) $dependepcies = [];
        if(!is_bool($in_footer)) $in_footer = false;

        $this->assets[] = [$name, $filename, $dependepcies, $in_footer];
    }

    function enqueue_scripts_styles() {
        if($this->_check_for_cf7()) {
            wp_enqueue_style( 'wpcf7-additional-types', plugin_dir_url( __FILE__ ).'assets/css/style.css' );

            foreach($this->assets as $asset) {
                wp_enqueue_script( $asset[0], plugin_dir_url( __FILE__ ).'assets/js/'.$asset[1], $asset[2], self::PLUGIN_VERSION, $asset[3] );
            }
        }
    }

    function register_cf7_shortcodes() {
        foreach($this->instances as $type => $instance) {
            wpcf7_add_shortcode( [$type, $type.'*'], [$instance, 'shortcode_handler'], true );

            if(method_exists($instance, 'validation_filter')) {
                add_filter( 'wpcf7_validate_'.$type, [$instance, 'validation_filter'], 10, 2 );
                add_filter( 'wpcf7_validate_'.$type.'*', [$instance, 'validation_filter'], 10, 2 );
                if(method_exists($instance, 'messages')) {
                    add_filter( 'wpcf7_messages', [$instance, 'messages'] );
                }
            }
        }
    }

    function add_tag_generators() {
        $tag_generator = WPCF7_TagGenerator::get_instance();

        foreach($this->instances as $type => $instance) {
            if(!method_exists($instance, 'tag_generator')) {
                $this->_stop_and_deactivate();
            } else {
                $tag_generator->add( $type, $instance->title, [$instance, 'tag_generator'] );
            }
        }
    }

    function dependencies_check() {
        if( !$this->_check_for_cf7() && is_admin() ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-warning is-dissmissible">'.
                    '<p>'. sprintf( __( 'You need to install and activate the Contact Form 7 Plugin to use %s.', CF7AT_TEXTDOMAIN ), self::PLUGIN_NAME ) .'</p>'.
                    '</div>';
            } );
        }
    }

    private function _check_for_cf7() {
        return defined('WPCF7_PLUGIN');
    }

    private function _stop_and_deactivate() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf( __( 'This version of %s is broken or incompatible with your WordPress installation. Please try to reinstall it from the WordPress repository.', CF7AT_TEXTDOMAIN ), self::PLUGIN_NAME ) );
    }
}

CF7_AdditionalTypes::init();
