<?php
/**
 * API Functions
 *
 * @package     Udemy\APIFunctions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

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
 * Get single course from API
 */
function udemy_get_course_from_api( $id ) {

    if ( ! is_numeric( $id ) )
        return __( 'Course ID must be a number.', 'udemy' );

    $data_args = udemy_api_get_course_data_args();

    $url = 'https://www.udemy.com/api-2.0/courses/' . $id . '?fields[course]=' . $data_args;

    $response = wp_remote_get( esc_url_raw( $url ), array(
        'timeout' => 15,
        'sslverify' => false
    ));

    //udemy_debug($response);

    // Response okay
    if ( ! is_wp_error( $response ) && is_array( $response ) && isset ( $response['response']['code'] ) && $response['response']['code'] === 200 ) {

        $result = json_decode(wp_remote_retrieve_body($response), true);

        return new Udemy_Course( $result );
    } else {
        return __( 'Course not found.', 'udemy' );
    }
}

/*
 * Get courses from API
 */
function udemy_get_courses_from_api( $args = array() ) {

    $defaults = array(
        'page' => 1,
        'page_size' => 10,
    );

    $args = wp_parse_args( $args, $defaults );

    //echo '<h4>Parsed args</h4>';
    //udemy_debug($args);

    $options = udemy_get_options();

    if ( empty( $options['api_client_id'] ) || empty( $options['api_client_password'] ) )
        return false;

    $url = 'https://www.udemy.com/api-2.0/courses/';
    $url = add_query_arg( $args, $url );

    $data_args = udemy_api_get_course_data_args();
    $url = $url . '&fields[course]=' . $data_args;

    //echo '<h4>URL calling</h4>';
    //udemy_debug($url);

    $response = wp_remote_get( esc_url_raw( $url ), array(
        'timeout' => 15,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $options['api_client_id'] . ':' . $options['api_client_password'] )
        ),
        'sslverify' => false
    ));

    //udemy_debug($response);

    // Response okay
    if ( ! is_wp_error( $response ) && is_array( $response ) && isset ( $response['response']['code'] ) && $response['response']['code'] === 200 ) {
        $result = json_decode(wp_remote_retrieve_body($response), true);

        return ( isset ( $result['results'] ) && is_array( $result['results'] ) && sizeof( $result['results'] ) > 0 ) ? udemy_get_course_objects_from_array( $result['results'] ) : __('No courses found.', 'udemy');

    } elseif ( isset ( $response['response']['code'] ) && $response['response']['code'] === 403 ) {
        return __( 'Client ID and/or password invalid.', 'udemy' );

    } else {
        return __( 'Courses could not be fetched. Please try again.', 'udemy' );
    }
}

/*
 * Get course arguments
 */
function udemy_api_get_course_data_args() {

    return '@all';

    /*
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

    $args = implode(',', $args);

    return $args;
    */
}