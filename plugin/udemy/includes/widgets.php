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
include_once UDEMY_DIR . 'includes/widgets/class.widget.search.php';

/*
 * Register Widgets
 */
function udemy_register_widgets() {
    register_widget( 'Udemy_Courses_Widget' );
    register_widget( 'Udemy_Search_Widget' );
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

/*
 * Handle shortcode in text widgets
 */
function udemy_widget_text( $widget_text, $instance, $widget ) {

    static $text_widget_scripts_loaded = false;

    if ( has_shortcode( $instance['text'], 'udemy' ) ) {

        // Add widget template if missing
        if ( strpos( $instance['text'], 'template') === false ) {
            $widget_text = str_replace( '[udemy', '[udemy template="widget"', $widget_text );

        // Reset invalid templates
        } elseif ( strpos( $instance['text'], 'template="standard"') !== false ) {
            $widget_text = str_replace( 'template="standard"', 'template="widget"', $widget_text );

        } elseif ( strpos( $instance['text'], 'template="grid"') !== false ) {
            $widget_text = str_replace( 'template="grid"', 'template="widget"', $widget_text );

        } elseif ( strpos( $instance['text'], 'template="list"') !== false ) {
            $widget_text = str_replace( 'template="list"', 'template="widget"', $widget_text );
        }

        // Load scripts
        if ( ! $text_widget_scripts_loaded ) {
            udemy_load_scripts();
            $text_widget_scripts_loaded = true;
        }
    }

    return $widget_text;
}
add_filter( 'widget_text', 'udemy_widget_text', 1, 3 );