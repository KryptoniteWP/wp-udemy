<?php
/**
 * Widgets
 *
 * @package     Udemy\Widgets
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Load widgets
 */
include_once UDEMY_DIR . 'includes/widgets/class.widget.courses.php';

/*
 * Register Widgets
 */
function udemy_register_widgets() {
    register_widget( 'Udemy_Courses_Widget' );
}
add_action( 'widgets_init', 'udemy_register_widgets' );

/*
 * Build shortcode
 */
function udemy_widget_do_shortcode( $atts = array() ) {

    if ( sizeof( $atts ) > 0 ) {

        // Build Shortcode
        $shortcode = '[udemy';

        foreach ( $atts as $key => $value ) {
            $shortcode .= ' ' . $key . '="' . $value . '"';
        }

        $shortcode .= '/]';

        // Execute Shortcode
        echo do_shortcode( $shortcode );

    } else {
        _e( 'Shortcode arguments missing.', 'udemy' );
    }
}

/*
 * Execute shortcodes within text widgets
 */
$options = udemy_get_options();

if ( isset ( $options['widget_text_shortcodes'] ) ) {
    add_filter( 'widget_text', 'do_shortcode');
}