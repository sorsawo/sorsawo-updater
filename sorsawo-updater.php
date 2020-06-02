<?php
/**
 * Plugin Name: Sorsawo Updater
 * Plugin URI: https://github.com/sorsawo/sorsawo-updater/
 * Description: A plugin to automatically update themes and plugins designed by Sorsawo.Com.
 * Author: Sorsawo.Com
 * Author URI: https://sorsawo.com
 * Version: 1.0
 * Text Domain: sorsawo-updater
 * Domain Path: languages
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Sorsawo_Updater {
    
    private static $_instance = NULL;
    
    /**
     * Initialize all variables, filters and actions
     */
    public function __construct() {
        add_action( 'admin_init',               array( $this, 'options_init' ) );
        add_action( 'init',               array( $this, 'load_plugin_textdomain' ), 0 );
        add_filter( 'http_request_args',  array( $this, 'dont_update_plugin' ), 5, 2 );
    }
    
    /**
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function instance() {
        if ( NULL === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'sorsawo-updater' );
        
        unload_textdomain( 'sorsawo-updater' );
        load_textdomain( 'sorsawo-updater', WP_LANG_DIR . '/sorsawo-updater/sorsawo-updater-' . $locale . '.mo' );
        load_plugin_textdomain( 'sorsawo-updater', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }
    
    public function dont_update_plugin( $r, $url ) {
        if ( 0 !== strpos( $url, 'https://api.wordpress.org/plugins/update-check/1.1/' ) ) {
            return $r; // Not a plugin update request. Bail immediately.
        }
        
        $plugins = json_decode( $r['body']['plugins'], true );
        unset( $plugins['plugins'][plugin_basename( __FILE__ )] );
        $r['body']['plugins'] = json_encode( $plugins );
        
        return $r;
    }
    
    public function options_init() {
        register_setting( 'general', 'sorsawo_updater', array( $this, 'validate_options' ) );
        
        add_settings_field( 'sorsawo_username', '<label for="sorsawo-username">' . __( 'Sorsawo Username' , 'sorsawo-updater' ) . '</label>', array( $this, 'username_field' ), 'general', 'default' );
        add_settings_field( 'sorsawo_apikey', '<label for="sorsawo-apikey">' . __( 'Sorsawo API Key' , 'sorsawo-updater' ) . '</label>', array( $this, 'apikey_field' ), 'general', 'default' );
    }
    
    public function validate_options( $input ) {
        $valid = get_option( 'sorsawo_updater' );
        $valid['username'] = sanitize_text_field( $input['username'] );
        $valid['apikey'] = sanitize_text_field( $input['apikey'] );
        
        return $valid;
    }
    
    public function username_field() {
        $option = get_option( 'sorsawo_updater' );
        echo '<input type="text" id="sorsawo-username" name="sorsawo_updater[username]" value="', esc_attr( $option['username'] ), '" class="regular-text" />';
        echo '<p class="description">' . __( 'This is your username to login to Sorsawo.Com', 'sorsawo-updater' ) . '</p>';
    }
    
    public function apikey_field() {
        $option = get_option( 'sorsawo_updater' );
        echo '<input type="text" id="sorsawo-apikey" name="sorsawo_updater[apikey]" value="', esc_attr( $option['apikey'] ), '" class="regular-text" />';
        echo '<p class="description">' . __( 'Please login to Sorsawo.Com to see your API Key', 'sorsawo-updater' ) . '</p>';
    }
    
}

Sorsawo_Updater::instance();
