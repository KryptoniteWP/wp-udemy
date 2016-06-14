<?php
/**
 * Shortcodes
 *
 * @package     Udemy\Shortcodes
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Purchase code form
 */
function udemy_add_shortcode( $atts ) {

    // Defaults
    $courses_list = false;

    $output = '';
    $output_args = array();

    // Get courses
    $courses = udemy_get_courses( $atts );

    if ( is_string( $courses ) )
        return $courses;

    // Type: IDs
    if ( isset ( $atts['id'] ) ) {

        // Shortcode atts
        if ( isset ( $atts['url'] ) )
            $output_args['url'] = sanitize_text_field( $atts['url'] );

    // Type: Lists
    } else {
        $courses_list = true;

        // Shortcode atts
        if ( isset ( $atts['grid'] ) )
            $output_args['grid'] = sanitize_text_field( $atts['grid'] );
    }

    if ( is_array( $courses ) & sizeof( $courses ) > 0 ) {

        $output_args['type'] = ( $courses_list ) ? 'list' : 'single';

        // Shortcode atts
        if ( isset ( $atts['style'] ) )
            $output_args['style'] = sanitize_text_field( $atts['style'] );

        if ( isset ( $atts['template'] ) )
            $output_args['template'] = sanitize_text_field( $atts['template'] );

        $output = udemy_display_courses( $courses, $output_args );
    }

    return $output;
}
add_shortcode( 'udemy', 'udemy_add_shortcode' );