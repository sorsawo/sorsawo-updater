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
    
}

Sorsawo_Updater::instance();
