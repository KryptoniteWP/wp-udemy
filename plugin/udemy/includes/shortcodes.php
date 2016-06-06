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

    //echo '<h4>Shortcode atts</h4>';
    //udemy_debug($atts);

    // Defaults
    $courses_single = false;
    $courses_list = false;

    $output = '';
    $output_args = array();
    $args = array();
    $courses = array();

    // IDs
    if ( isset ( $atts['id'] ) ) {

        $courses_single = true;

        $course_ids = explode(',', str_replace(' ', '', sanitize_text_field( $atts['id'] ) ) );

        foreach ( $course_ids as $id ) {
            $courses[] = udemy_get_course( $id );
        }

        // Shortcode atts
        if ( isset ( $atts['url'] ) )
            $output_args['url'] = sanitize_text_field( $atts['url'] );

    // Lists
    } else {

        $courses_list = true;

        // Page size
        if ( isset ( $atts['items'] ) && is_numeric( $atts['items'] ) )
            $args['page_size'] = $atts['items'];

        // Categories
        if ( isset ( $atts['category'] ) ) {

            $category = udemy_cleanup_category_name ( sanitize_text_field( $atts['category'] ) );
            $categories = udemy_get_categories();

            if ( in_array( $category, $categories ) ) {
                $args['category'] = $category;
            } else {
                $args['subcategory'] = $category;
            }
        }

        // Search
        if ( isset ( $atts['search'] ) )
            $args['search'] = sanitize_text_field( $atts['search'] );

        //echo '<h4>Shortcode args</h4>';
        //udemy_debug($args);

        // Get courses
        if ( sizeof( $args ) > 0 ) {
            $courses = udemy_get_courses($args);

            //udemy_debug($courses);
        }

        // Shortcode atts
        if ( isset ( $atts['grid'] ) )
            $output_args['grid'] = sanitize_text_field( $atts['grid'] );
    }

    if ( is_string( $courses ) )
        return $courses;

    if ( is_array( $courses ) & sizeof( $courses ) > 0 ) {

        $output_args['type'] = ( $courses_list ) ? 'list' : 'single';

        // Shortcode atts
        if ( isset ( $atts['template'] ) )
            $output_args['template'] = sanitize_text_field( $atts['template'] );

        $output = udemy_display_courses( $courses, $output_args );
    }

    //edd_envato_customers_the_purchase_code_form( $actions );

    return $output;
}
add_shortcode( 'udemy', 'udemy_add_shortcode' );