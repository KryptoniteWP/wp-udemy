<?php
/**
 * Plugin Name:     UFWP - Online Learning Courses
 * Plugin URI:      https://wordpress.org/plugins/wp-udemy/
 * Description:     Display Online Learning Courses from the best platform inside your WordPress posts and pages.
 * Version:         1.0.7
 * Author:          flowdee
 * Author URI:      http://flowdee.de
 * Text Domain:     wp-udemy
 *
 * @package         UFWP
 * @author          flowdee
 * @copyright       Copyright (c) flowdee
 *
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'UFWP' ) ) {

    /**
     * Main Udemy class
     *
     * @since       1.0.0
     */
    class UFWP {

        /**
         * @var         UFWP $instance The one true UFWP
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true UFWP
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new UFWP();
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
            define( 'UFWP_NAME', 'Online Learning Courses' );

            // Plugin version
            define( 'UFWP_VER', '1.0.7' );

            // Plugin path
            define( 'UFWP_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'UFWP_URL', plugin_dir_url( __FILE__ ) );

            // Debug
            $options = get_option('ufwp_settings');
            $debug = ( isset ( $options['developer_mode'] ) && $options['developer_mode'] == '1' ) ? true : false;

            define( 'UFWP_DEBUG', $debug );
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
            require_once UFWP_DIR . 'includes/helper.php';

            if ( is_admin() ) {
                require_once UFWP_DIR . 'includes/admin/plugins.php';
                require_once UFWP_DIR . 'includes/admin/class.settings.php';
            }

            require_once UFWP_DIR . 'includes/scripts.php';
            require_once UFWP_DIR . 'includes/class.course.php';
            require_once UFWP_DIR . 'includes/api-functions.php';
            require_once UFWP_DIR . 'includes/functions.php';
            require_once UFWP_DIR . 'includes/shortcodes.php';
            require_once UFWP_DIR . 'includes/widgets.php';
            require_once UFWP_DIR . 'includes/hooks.php';
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
            $lang_dir = UFWP_DIR . '/languages/';
            $lang_dir = apply_filters( 'ufwp_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'wp-udemy' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'wp-udemy', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/wp-udemy/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/wp-udemy/ folder
                load_textdomain( 'wp-udemy', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/wp-udemy/languages/ folder
                load_textdomain( 'wp-udemy', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'wp-udemy', false, $lang_dir );
            }
        }
    }
} // End if class_exists check

/**
 * The main function responsible for returning the one true UFWP
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \UFWP The one true UFWP
 *
 */
function ufwp_load() {

    $instance = UFWP::instance();

    do_action( 'ufwp_init' );

    return $instance;
}
add_action( 'plugins_loaded', 'ufwp_load' );

/**
 * The activation hook
 */
function ufwp_activation() {

    if ( ! wp_next_scheduled ( 'ufwp_wp_scheduled_events' ) )
        wp_schedule_event( time(), 'hourly', 'ufwp_wp_scheduled_events' );

    // Flush rewrite rules on activation
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ufwp_activation' );

/**
 * The deactivation hook
 */
function ufwp_deactivation() {
    wp_clear_scheduled_hook('ufwp_wp_scheduled_events');

    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'ufwp_deactivation');