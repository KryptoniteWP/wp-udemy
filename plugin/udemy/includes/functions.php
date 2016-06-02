<?php
/**
 * Functions
 *
 * @package     Udemy\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Get options
 */
function udemy_get_options() {
    return get_option( 'udemy', array() );
}

/*
 * Validate API credentials
 */
function udemy_validate_api_credentials( $client_id, $client_password ) {

    if ( empty( $client_id ) || empty( $client_password ) )
        return false;

    $url = 'https://www.udemy.com/api-2.0/courses/';

    $response = wp_remote_get( esc_url_raw( $url ), array(
        'timeout' => 15,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_password )
        ),
        'sslverify' => false
    ));

    //udemy_debug($response);

    // Prepare validation
    $validation = array(
        'status' => false,
        'error' => __( 'Undefined error', 'udemy' ),
    );

    // Handle response
    if ( ! is_wp_error( $response ) && is_array( $response ) && isset ( $response['response']['code'] ) ) {

        if ( $response['response']['code'] === 200 ) {
            $validation['status'] = true;
            $validation['error'] = '';

        } elseif ( $response['response']['code'] === 403 ) {
            $validation['status'] = false;
            $validation['error'] = __( 'Client ID and/or password invalid.', 'udemy' );
        }
    }

    // Return validation
    return $validation;
}

/*
 * Get course
 */
function udemy_get_course( $id ) {

    if ( ! is_numeric( $id ) )
        return __( 'Course ID must be a number.', 'udemy' );

    $args = udemy_api_get_course_args();
    $args = implode(',', $args);

    $url = 'https://www.udemy.com/api-2.0/courses/' . $id . '?fields[course]=' . $args;

    $response = wp_remote_get( esc_url_raw( $url ), array(
        'timeout' => 15,
        'sslverify' => false
    ));

    //udemy_debug($response);

    // Response okay
    if ( ! is_wp_error( $response ) && is_array( $response ) && isset ( $response['response']['code'] ) && $response['response']['code'] === 200 ) {
        return json_decode( wp_remote_retrieve_body( $response ), true );

    } else {
        return __( 'Course not found.', 'udemy' );
    }
}

/*
 * Get courses
 */
function udemy_get_courses( $id ) {

    if ( ! is_numeric( $id ) )
        return __( 'Course ID must be a number.', 'udemy' );

    $options = udemy_get_options();

    if ( empty( $options['api_client_id'] ) || empty( $options['api_client_password'] ) )
        return false;

    $url = 'https://www.udemy.com/api-2.0/courses/';

    $response = wp_remote_get( esc_url_raw( $url ), array(
        'timeout' => 15,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $options['api_client_id'] . ':' . $options['api_client_password'] )
        ),
        'sslverify' => false
    ));

    udemy_debug($response);

    // Response okay
    if ( ! is_wp_error( $response ) && is_array( $response ) && isset ( $response['response']['code'] ) && $response['response']['code'] === 200 ) {
        return json_decode( wp_remote_retrieve_body( $response ), true );

    } else {
        return __( 'Courses not found.', 'udemy' );
    }
}

/*
 * Get course arguments
 */
function udemy_api_get_course_args() {

    $args = array(
        'title',
        'headline',
        'image_480x270',
        'price',
        'is_paid',
        'num_lectures',
        'locale',
        'avg_rating',
        'created',
        'description',
        'image_100x100',
        'num_reviews',
        'num_subscribers',
        'url',
        'status_label'
    );

    return $args;
}

/*
 * Display courses
 */
function udemy_display_courses( $courses = array(), $args = array() ) {

    //udemy_debug($courses);

    // Defaults
    $type = 'single';
    $template = ( isset ( $args['template'] ) ? $args['template'] : $type );

    // Get template file
    $file = udemy_get_template_file( $template, $type );

    // Output
    ob_start();

    echo '<div class="udemy-wp">';

    foreach ($courses as $course) {

        // Valid data
        if ( is_array( $course ) ) {

            // Final render
            if ( file_exists( $file ) ) {
                include( $file );
            } else {
                _e('Template not found.', 'udemy');
            }

        // Holding an error/notice message
        } else {
            echo '<p>' . $course . '</p>';
        }
    }

    echo '</div>';

    $output = ob_get_clean();

    return $output;
}

/*
 * Get template file
 */
function udemy_get_template_file( $template, $type ) {

    $default_template = UDEMY_DIR . 'templates/' .$type . '.php';

    // Check theme folder
    if ( $custom_template_file = locate_template( array( 'udemy/' . $template . '.php' ) ) ) {
        return $custom_template_file;
    }

    return $default_template;
}

