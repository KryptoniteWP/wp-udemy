<?php
/**
 * Scripts
 *
 * @package     UFWP\Scripts
 * @since       1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function ufwp_admin_scripts() {

    /**
     *	Settings page only
     */
    $screen = get_current_screen();

    if ( ! empty( $screen->base ) && ( $screen->base == 'settings_page_wp-udemy' || $screen->base == 'widgets' ) ) {

        wp_enqueue_script( 'ufwp-admin', UFWP_URL . 'assets/dist/js/admin.js', array( 'jquery' ), UFWP_VER );
        wp_enqueue_style( 'ufwp-admin', UFWP_URL . 'assets/dist/css/admin.css', false, UFWP_VER );

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
function ufwp_scripts() {

    // Don't enqueue scripts or styles in AMP mode.
    if ( function_exists( 'is_amp_endpoint' ) &&  is_amp_endpoint() )
        return;

    //wp_enqueue_script( 'ufwp', UFWP_URL . 'assets/dist/js/main.js', array( 'jquery' ), UFWP_VER, true );
    wp_enqueue_style( 'ufwp', UFWP_URL . 'assets/dist/css/main.css', false, UFWP_VER );

    do_action( 'ufwp_enqueue_scripts' );
}
add_action( 'wp_enqueue_scripts', 'ufwp_scripts' );