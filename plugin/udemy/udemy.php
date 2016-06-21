<?php
/**
 * Plugin Name:     Udemy
 * Plugin URI:      https://coder.flowdee.de/downloads/udemy-for-wordpress/
 * Description:     Display Udemy courses inside your WordPress posts and pages.
 * Version:         1.0.0
 * Author:          flowdee
 * Author URI:      http://flowdee.de
 * Text Domain:     udemy
 *
 * @package         Udemy
 * @author          flowdee
 * @copyright       Copyright (c) flowdee
 *
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Udemy' ) ) {

    /**
     * Main Udemy class
     *
     * @since       1.0.0
     */
    class Udemy {

        /**
         * @var         Udemy $instance The one true Udemy
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true Udemy
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new Udemy();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {

            // Plugin name
            define( 'UDEMY_NAME', 'Udemy' );

            // Plugin version
            define( 'UDEMY_VER', '1.0.0' );

            // Plugin path
            define( 'UDEMY_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'UDEMY_URL', plugin_dir_url( __FILE__ ) );

            // Debug
            $options = get_option('udemy');
            $debug = ( isset ( $options['developer_mode'] ) && $options['developer_mode'] == '1' ) ? true : false;

            define( 'UDEMY_DEBUG', $debug );
        }
        
        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {

            // Include scripts
            require_once UDEMY_DIR . 'includes/helper.php';

            if ( is_admin() ) {
                require_once UDEMY_DIR . 'includes/admin/plugins.php';
                require_once UDEMY_DIR . 'includes/admin/class.settings.php';
            }

            require_once UDEMY_DIR . 'includes/scripts.php';
            require_once UDEMY_DIR . 'includes/class.course.php';
            require_once UDEMY_DIR . 'includes/api-functions.php';
            require_once UDEMY_DIR . 'includes/functions.php';
            require_once UDEMY_DIR . 'includes/shortcodes.php';
            require_once UDEMY_DIR . 'includes/rewrites.php';
            require_once UDEMY_DIR . 'includes/widgets.php';
        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = UDEMY_DIR . '/languages/';
            $lang_dir = apply_filters( 'udemy_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'udemy' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'udemy', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/udemy/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/udemy/ folder
                load_textdomain( 'udemy', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/udemy/languages/ folder
                load_textdomain( 'udemy', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'udemy', false, $lang_dir );
            }
        }
    }
} // End if class_exists check

/**
 * The main function responsible for returning the one true Udemy
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \Udemy The one true Udemy
 *
 */
function udemy_load() {
    return Udemy::instance();
}
add_action( 'plugins_loaded', 'udemy_load' );

/**
 * The activation hook
 */
function udemy_activation() {

    if ( ! wp_next_scheduled ( 'udemy_wp_scheduled_events' ) )
        wp_schedule_event( time(), 'hourly', 'udemy_wp_scheduled_events' );

    // Flush rewrite rules on activation
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'udemy_activation' );

/**
 * The deactivation hook
 */
function udemy_deactivation() {
    wp_clear_scheduled_hook('udemy_wp_scheduled_events');

    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'udemy_deactivation');

/**
 * Plugin Updater
 */
include( dirname( __FILE__ ) . '/vendor/plugin-update-checker/plugin-update-checker.php' );

function udemy_plugin_updater() {

    try {
        $udemy_update_checker = PucFactory::buildUpdateChecker(
            'https://updates.flowdee.de/?action=get_metadata&slug=udemy-for-wordpress',
            __FILE__, //Full path to the main plugin file.
            'udemy-for-wordpress'
        );

    } catch (Exception $e) {
        // do nothing
    }
}
add_action( 'admin_init', 'udemy_plugin_updater', 0 );