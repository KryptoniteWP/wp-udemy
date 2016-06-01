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

    // Single courses
    if ( empty ( $atts['course'] ) )
        return __('Course ID missing.', 'udemy');

    // Defaults
    $args = array();

    // Single courses
    if ( isset ( $atts['template'] ) )
        $args['template'] = sanitize_text_field( $atts['template'] );

    $course_ids = explode(',', str_replace(' ', '', sanitize_text_field( $atts['course'] ) ) );
    $courses = array();

    foreach ( $course_ids as $id ) {
        $courses[] = udemy_get_course( $id );
    }

    $output = udemy_display_courses( $courses );

    //edd_envato_customers_the_purchase_code_form( $actions );

    return $output;
}
add_shortcode( 'udemy', 'udemy_add_shortcode' );