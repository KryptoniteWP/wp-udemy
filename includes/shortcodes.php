<?php
/**
 * Shortcodes
 *
 * @package     UFWP\Shortcodes
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Purchase code form
 */
function ufwp_add_shortcode( $atts ) {

    // Defaults
    $courses_list = false;

    $output = '';
    $output_args = array();

    // Get courses
    $courses = ufwp_get_courses( $atts );

    if ( is_string( $courses ) )
        return $courses;

    // Type: IDs
    if ( isset ( $atts['id'] ) ) {
        // Silence

    // Type: Lists
    } else {
        $courses_list = true;

        // Shortcode atts
        if ( isset ( $atts['grid'] ) )
            $output_args['grid'] = sanitize_text_field( $atts['grid'] );
    }

    if ( is_array( $courses ) & sizeof( $courses ) > 0 ) {

        // Items
        $output_args['items'] = sizeof( $courses );

        // Set type
        if ( isset ( $atts['type'] ) ) {
            $output_args['type'] = $atts['type'];
        } else {
            $output_args['type'] = ( $courses_list ) ? 'list' : 'single';
        }

        // Shortcode atts
        if ( isset ( $atts['style'] ) )
            $output_args['style'] = sanitize_text_field( $atts['style'] );

        if ( isset ( $atts['template'] ) )
            $output_args['template'] = sanitize_text_field( $atts['template'] );

        $output_args = apply_filters( 'ufwp_shortcode_output_args', $output_args, $atts );

        $output = ufwp_display_courses( $courses, $output_args );
    }

    // Strip line breaks and empty paragraphs
    $output = str_replace(array("\r", "\n", "<p></p>"), '', $output);

    return $output;
}
add_shortcode( 'ufwp', 'ufwp_add_shortcode' );
add_shortcode( 'udemy', 'ufwp_add_shortcode' );