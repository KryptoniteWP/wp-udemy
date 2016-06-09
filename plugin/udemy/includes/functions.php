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
 * Get courses
 */
function udemy_get_courses( $args = array() ) {

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

        return ( isset ( $result['results'] ) && is_array( $result['results'] ) && sizeof( $result['results'] ) > 0 ) ? udemy_get_course_objects( $result['results'] ) : __('No courses found.', 'udemy');

    } elseif ( isset ( $response['response']['code'] ) && $response['response']['code'] === 403 ) {
        return __( 'Client ID and/or password invalid.', 'udemy' );

    } else {
        return __( 'Courses could not be fetched. Please try again.', 'udemy' );
    }
}

/*
 * Build course objects from result arrays
 */
function udemy_get_course_objects( $results = array() ) {

    $objects = array();

    if ( sizeof( $results ) > 0 ) {

        foreach ( $results as $result ) {
            $objects[] = new Udemy_Course( $result );
        }
    }

    return $objects;
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

/*
 * Cache structure
 */
function udemy_get_cache_structure() {
    return array(
        'items' => array(),
        'lists' => array()
    );
}

/*
 * Update cache
 */
function udemy_update_cache( $items, $list = null ) {

    $cache = get_option( 'udemy_cache', udemy_get_cache_structure() );

    echo 'update cache!';

    if ( isset ( $cache['items'] ) ) {

        // Multiple course
        if ( is_array( $items ) ) {

            foreach ( $items as $item ) {

                if ( is_object( $item ) && method_exists( $item, 'get_id' ) )
                    $cache['items'][$item->get_id()] = $item;
            }

        // Single course
        } else {

            if ( is_object( $items ) && method_exists( $items, 'get_id' ) )
                $cache['items'][$items->get_id()] = $items;
        }
    }

    update_option( 'udemy_cache', $cache );
}

/*
 * Get cache
 */
function udemy_get_cache( $key, $list = false ) {

    $cache = get_option( 'udemy_cache', udemy_get_cache_structure() );

    //udemy_debug( $cache );

    if ( isset ( $cache['items'][$key] ) ) {
        return $cache['items'][$key];
    }

    return false;
}

/*
 * Build cache key
 */
function udemy_get_cache_key( $args ) {

    $key = '';

    return $key;
}

/*
 * Delete cache
 */
function udemy_delete_cache() {
    delete_option( 'udemy_cache' );
}

/*
 * Display courses
 */
$udemy_args = array();

function udemy_display_courses( $courses = array(), $args = array() ) {

    $options = udemy_get_options();

    //udemy_debug($courses);

    global $udemy_args;

    $udemy_args = $args;

    // Defaults
    $type = ( isset ( $args['type'] ) ) ? $args['type'] : 'single';
    $grid = ( isset ( $args['grid'] ) && is_numeric( $args['grid'] ) ) ? $args['grid'] : '3';

    if ( isset ( $args['style'] ) )
        $style = $args['style'];

    $template_course = ( isset ( $options['template_course'] ) ) ? $options['template_course'] : 'single';
    $template_courses = ( isset ( $options['template_courses'] ) ) ? $options['template_courses'] : 'list';

    if ( isset ( $args['template'] ) ) {
        $template = str_replace(' ', '', $args['template'] );
    } else {
        $template = ( sizeof( $courses ) > 1 ) ? $template_courses : $template_course;
    }

    // Get template file
    $file = udemy_get_template_file( $template );

    // Output
    ob_start();

    echo '<div class="udemy-wp">';

    if ( file_exists( $file ) ) {
        include( $file );
    } else {
        _e('Template not found.', 'udemy');
    }

    echo '</div>';

    $output = ob_get_clean();

    return $output;
}

/*
 * Get template file
 */
function udemy_get_template_file( $template ) {

    $template_file = UDEMY_DIR . 'templates/' . $template . '.php';

    // Check theme folder
    if ( $custom_template_file = locate_template( array( 'udemy/' . $template . '.php' ) ) ) {
        return $custom_template_file;
    }

    if ( file_exists( $template_file ) )
        return $template_file;

    return UDEMY_DIR . 'templates/single.php';
}

/*
 * Main categories
 */
function udemy_get_categories() {
    return array('Academics','Business','Crafts-and-Hobbies','Design','Development','Games','Health-and-Fitness','Humanities','IT-and-Software','Language','Lifestyle','Marketing','Math-and-Science','Music','Office-Productivity','Other','Personal-Development','Photography','Social-Science','Sports','Teacher-Training','Technology','Test','Test-Prep');
}