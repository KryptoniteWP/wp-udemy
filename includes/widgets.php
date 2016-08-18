<?php
/**
 * Widgets
 *
 * @package     UFWP\Widgets
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Load widgets
 */
include_once UFWP_DIR . 'includes/widgets/class.widget.courses.php';
include_once UFWP_DIR . 'includes/widgets/class.widget.search.php';

/*
 * Register Widgets
 */
function ufwp_register_widgets() {
    register_widget( 'UFWP_Courses_Widget' );
    register_widget( 'UFWP_Search_Widget' );
}
add_action( 'widgets_init', 'ufwp_register_widgets' );

/*
 * Build shortcode
 */
function ufwp_widget_do_shortcode( $atts = array() ) {

    if ( sizeof( $atts ) > 0 ) {

        // Build Shortcode
        $shortcode = '[ufwp';

        foreach ( $atts as $key => $value ) {
            $shortcode .= ' ' . $key . '="' . $value . '"';
        }

        $shortcode .= '/]';

        // Execute Shortcode
        echo do_shortcode( $shortcode );

    } else {
        _e( 'Shortcode arguments missing.', 'wp-udemy' );
    }
}

/*
 * Execute shortcodes within text widgets
 */
$options = ufwp_get_options();

if ( isset ( $options['widget_text_shortcodes'] ) ) {
    add_filter( 'widget_text', 'do_shortcode');
}

/*
 * Handle shortcode in text widgets
 */
function ufwp_widget_text( $widget_text, $instance, $widget ) {

    static $text_widget_scripts_loaded = false;

    if ( has_shortcode( $instance['text'], 'ufwp' ) || has_shortcode( $instance['text'], 'udemy' ) ) {

        // Add widget template if missing
        if ( strpos( $instance['text'], 'template') === false ) {
            $widget_text = str_replace( '[ufwp', '[ufwp template="widget"', $widget_text );

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
            ufwp_load_scripts();
            $text_widget_scripts_loaded = true;
        }
    }

    return $widget_text;
}
add_filter( 'widget_text', 'ufwp_widget_text', 1, 3 );