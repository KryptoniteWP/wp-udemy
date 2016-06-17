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
 * Build course objects from result arrays
 */
function udemy_get_course_objects_from_array( $items = array() ) {

    $objects = array();

    if ( sizeof( $items ) > 0 ) {

        foreach ( $items as $item ) {
            $objects[] = ( is_array( $item ) ) ? new Udemy_Course( $item ) : $item;
        }
    }

    return $objects;
}

/*
 * Cache structure
 */
function udemy_get_cache_structure() {
    return array(
        'items' => array(),
        'lists' => array(),
        'last_update' => 0
    );
}

/*
 * Update cache
 */
function udemy_update_cache( $items, $key = false ) {

    $cache = get_option( 'udemy_cache', udemy_get_cache_structure() );

    // List of courses
    if ( $key ) {

        $serialized_key = serialize( $key );

        $cache['lists'][$serialized_key] = $items;

    // Single or multiple courses
    } else {

        // Multiple courses
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
function udemy_get_cache( $key ) {

    $cache = get_option( 'udemy_cache', udemy_get_cache_structure() );

    //udemy_debug( $cache );

    // List of items
    if ( is_array( $key ) ) {

        $serialized_key = serialize( $key );

        if ( isset ( $cache['lists'][$serialized_key] ) ) {
            return $cache['lists'][$serialized_key];
        }

    // Single item
    } else {

        if ( isset ( $cache['items'][$key] ) ) {
            return $cache['items'][$key];
        }
    }

    return false;
}

/*
 * Build cache key
 */
function udemy_get_cache_key( $args ) {
    return ( is_numeric( $args ) ) ? $args : serialize( $args );
}

/*
 * Delete cache
 */
function udemy_delete_cache() {
    delete_option( 'udemy_cache' );
}

/*
 * Cleanup cache event
 */
function udemy_cleanup_cache() {

    $cache = get_option( 'udemy_cache', udemy_get_cache_structure() );

    $last_update = ( isset ( $cache['last_update'] ) ) ? $cache['last_update'] : 0;

    $debug = false;

    if ( ( time() - $last_update ) > ( 7 * 60 * 60 * 60 ) || $debug ) {

        $cache = udemy_get_cache_structure();
        $cache['last_update'] = $last_update;

        // Reset cache
        update_option( 'udemy_cache', $cache );
    }
}

/*
 * Update cache event
 */
function udemy_update_cache_event() {

    $options = udemy_get_options();

    $cache = get_option( 'udemy_cache', udemy_get_cache_structure() );

    $cache_duration = ( ! empty ( $options['cache_duration'] ) ) ? intval( $options['cache_duration'] ) : 1440;
    $last_update = ( isset ( $cache['last_update'] ) ) ? intval( $cache['last_update'] ) : 0;

    $debug = true;

    if ( ( time() - $last_update ) > ( $cache_duration * 60 ) || $debug ) {

        $debug_start_time = microtime( true );

        udemy_addlog( '*** START *** UPDATING CACHE ***' );

        // Single items
        $cache['items'] = udemy_bulk_update_items( $cache['items'] );

        // Lists
        $cache['lists'] = udemy_bulk_update_lists( $cache['lists'] );

        // Update timestamp
        $cache['last_update'] = time();

        // Update cache
        update_option( 'udemy_cache', $cache );

        $debug_execution_time = microtime(true) - $debug_start_time;

        udemy_addlog( '*** END *** UPDATING CACHE *** EXECUTION TIME: ' . $debug_execution_time . ' SECONDS ***' );
    }
}

/*
 * Bulk update items via API
 */
function udemy_bulk_update_items( $items ) {

    udemy_addlog( 'BULK UPDATING ITEMS' );

    $i = 1;

    foreach ( $items as $id => $data ) {

        if ( is_numeric( $id ) ) {

            // Go easy on API and hold on after every 10 items
            if ($i > 0 && $i % 10 == 0) {
                udemy_addlog( 'UPDATING PAUSED AFTER ' . $i . ' ITEMS' );
                sleep(5);
            }

            // Fetch course
            $course = udemy_get_course_from_api( $id );

            if ( is_object( $course ) )
                $items[$id] = $course;

            // Update item count
            $i++;
        }
    }

    udemy_addlog( 'BULK UPDATED ' . ( $i - 1 ) . ' ITEMS' );

    return $items;
}

/*
 * Bulk update lists via API
 */
function udemy_bulk_update_lists( $lists ) {

    udemy_addlog( 'BULK UPDATING LISTS' );

    $i = 1;

    foreach ( $lists as $id => $items ) {

        $args = unserialize( $id );

        if ( sizeof( $args ) > 0 ) {

            // Go easy on API and hold on after every 5 lists
            if ($i > 0 && $i % 5 == 0) {
                udemy_addlog( 'UPDATING PAUSED AFTER ' . $i . ' LISTS' );
                sleep(5);
            }

            // Fetch courses
            $courses = udemy_get_courses_from_api( $args );

            if ( is_array( $courses ) )
                $lists[$id] = $courses;

            // Update list count
            $i++;
        }
    }

    udemy_addlog( 'BULK UPDATED ' . ( $i - 1 ) . ' LISTS' );

    return $lists;
}

/*
 * Handle scheduled events
 */
function udemy_scheduled_events() {

    // Cleanup cache
    udemy_cleanup_cache();

    // Handle cache updates
    udemy_update_cache_event();
}
add_action('udemy_wp_scheduled_events', 'udemy_scheduled_events');

/*
 * Get courses
 */
function udemy_get_courses( $atts ) {

    if ( ! function_exists('curl_version') )
        return '<p style="color: darkorange; font-weight: bold;">' . __( 'Please activate PHP curl in order to display Udemy courses.' ) . '</p>';

    // Defaults
    $args = array();
    $courses = array();

    // IDs
    if ( isset ( $atts['id'] ) ) {

        $course_ids = explode(',', str_replace( array( ' ', ';'), array( '', ','), sanitize_text_field( $atts['id'] ) ) );

        foreach ( $course_ids as $id ) {

            $course_cache = udemy_get_cache( $id );

            // Cache available
            if ( $course_cache ) {
                $courses[] = $course_cache;

                // Cache not available, fetch from API
            } else {
                $course = udemy_get_course_from_api( $id );
                $courses[] = $course;

                if ( is_object( $course ) ) {
                    udemy_update_cache( $course );
                }
            }
        }

        // Lists
    } else {

        // Page size
        if ( isset ( $atts['items'] ) && is_numeric( $atts['items'] ) )
            $args['page_size'] = $atts['items'];

        // Language
        if ( isset ( $atts['lang'] ) )
            $args['language'] = $atts['lang'];

        // Order
        if ( isset ( $atts['orderby'] ) ) {

            if ( 'sales' === $atts['orderby'] )
                $orderby = 'best_seller';

            if ( 'date' === $atts['orderby'] )
                $orderby = 'enrollment';

            if ( 'trends' === $atts['orderby'] )
                $orderby = 'trending';

            if ( ! empty ( $orderby ) )
                $args['ordering'] = $orderby;
        }

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

        // Get courses
        if ( sizeof( $args ) > 0 ) {

            $courses_cache = udemy_get_cache( $args );

            // Cache available
            if ( $courses_cache ) {
                $courses = $courses_cache;
            } else {
                $courses = udemy_get_courses_from_api($args);

                if ( is_array( $courses ) ) {
                    udemy_update_cache( $courses, $args );
                }
            }
        }
    }

    return $courses;
}

/*
 * Display courses
 */
$udemy_args = array();

function udemy_display_courses( $courses = array(), $args = array() ) {

    //udemy_debug($courses);

    $options = get_option('udemy');

    global $udemy_args;

    $udemy_args = $args;

    // Prepare courses
    $courses = udemy_get_course_objects_from_array( $courses );

    // Defaults
    $type = ( isset ( $args['type'] ) ) ? $args['type'] : 'single';
    $grid = ( isset ( $args['grid'] ) && is_numeric( $args['grid'] ) ) ? $args['grid'] : '3';

    if ( isset ( $args['style'] ) )
        $style = $args['style'];

    $template_course = ( isset ( $options['template_course'] ) ) ? $options['template_course'] : 'standard';
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

    return UDEMY_DIR . 'templates/standard.php';
}

/*
 * Main categories
 */
function udemy_get_categories() {
    return array('Academics','Business','Crafts-and-Hobbies','Design','Development','Games','Health-and-Fitness','Humanities','IT-and-Software','Language','Lifestyle','Marketing','Math-and-Science','Music','Office-Productivity','Other','Personal-Development','Photography','Social-Science','Sports','Teacher-Training','Technology','Test','Test-Prep');
}

/*
 * Get redirect affiliate url
 */
function udemy_get_course_affiliate_url( $url, $encode = true ) {

    $options = udemy_get_options();

    if ( empty ( $options['affiliate_publisher_id'] ) && ! isset ( $options['credits'] ) )
        return $url;

    // Take publisher id, only if empty and credits activated the the other one
    $publisher_id = ( ! empty ( $options['affiliate_publisher_id'] ) ) ? esc_attr( trim( $options['affiliate_publisher_id'] ) ) : 'rAHrr6IQKiQ';

    // Static ID for Udemys advertiser program
    $merchant_id = '39197';

    // Encoding url
    $encoded_url = ( $encode ) ? urlencode( $url ) : $url;

    // Building final url
    $url = 'http://click.linksynergy.com/deeplink?id=' . $publisher_id . '&type=10&mid=' . $merchant_id . '&murl=' . $encoded_url;

    return $url;
}