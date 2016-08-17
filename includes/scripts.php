<?php
/**
 * Scripts
 *
 * @package     UFWP\Scripts
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function ufwp_admin_scripts( $hook ) {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || UFWP_DEBUG ) ? '' : '.min';

    /**
     *	Settings page only
     */
    $screen = get_current_screen();

    if ( ! empty( $screen->base ) && ( $screen->base == 'settings_page_wp-udemy' || $screen->base == 'widgets' ) ) {

        wp_enqueue_script( 'ufwp_admin_js', UFWP_URL . 'public/assets/js/admin' . $suffix . '.js', array( 'jquery' ), UFWP_VER );
        wp_enqueue_style( 'ufwp_admin_css', UFWP_URL . 'public/assets/css/admin' . $suffix . '.css', false, UFWP_VER );

        do_action( 'ufwp_admin_enqueue_scripts' );
    }
}
add_action( 'admin_enqueue_scripts', 'ufwp_admin_scripts', 100 );

/**
 * Frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function ufwp_scripts( $hook ) {

    if ( ufwp_has_plugin_content() ) {
        ufwp_load_scripts();
    }
}
add_action( 'wp_enqueue_scripts', 'ufwp_scripts' );

/**
 * Load frontend scripts
 */
function ufwp_load_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || UFWP_DEBUG ) ? '' : '.min';

    //wp_enqueue_script( 'ufwp_scripts', UFWP_URL . 'public/assets/js/scripts' . $suffix . '.js', array( 'jquery' ), UFWP_VER, true );
    wp_enqueue_style( 'ufwp_styles', UFWP_URL . 'public/assets/css/styles' . $suffix . '.css', false, UFWP_VER );

    do_action( 'ufwp_enqueue_scripts' );
}