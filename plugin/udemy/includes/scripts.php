<?php
/**
 * Scripts
 *
 * @package     Udemy\Scripts
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
function udemy_admin_scripts( $hook ) {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || UDEMY_DEBUG ) ? '' : '.min';

    /**
     *	Settings page only
     */
    $screen = get_current_screen();

    if ( ! empty( $screen->base ) && ( $screen->base == 'settings_page_udemy' || $screen->base == 'widgets' ) ) {

        wp_enqueue_script( 'udemy_admin_js', UDEMY_URL . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ), UDEMY_VER );
        wp_enqueue_style( 'udemy_admin_css', UDEMY_URL . '/assets/css/admin' . $suffix . '.css', false, UDEMY_VER );
    }
}
add_action( 'admin_enqueue_scripts', 'udemy_admin_scripts', 100 );

/**
 * Frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function udemy_scripts( $hook ) {

    global $post;

    if( ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'udemy') ) ) {
        udemy_load_scripts();
    }
}
add_action( 'wp_enqueue_scripts', 'udemy_scripts' );

/**
 * Load frontend scripts
 */
function udemy_load_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || UDEMY_DEBUG ) ? '' : '.min';

    wp_enqueue_script( 'udemy_scripts', UDEMY_URL . 'assets/js/scripts' . $suffix . '.js', array( 'jquery' ), UDEMY_VER, true );
    wp_enqueue_style( 'udemy_styles', UDEMY_URL . 'assets/css/styles' . $suffix . '.css', false, UDEMY_VER );
}