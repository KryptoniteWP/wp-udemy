<?php
/**
 * Functions
 *
 * @package     UFWP\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Build course objects from result arrays
 */
function ufwp_get_course_objects_from_array( $items = array(), $args = array() ) {

    $objects = array();

    if ( sizeof( $items ) > 0 ) {

        foreach ( $items as $item ) {
            $objects[] = ( is_array( $item ) ) ? new UFWP_Course( $item, $args ) : $item;
        }
    }

    return $objects;
}

/*
 * Cache structure
 */
function ufwp_get_cache_structure() {
    return array(
        'items' => array(),
        'lists' => array(),
        'last_update' => 0
    );
}

/*
 * Update cache
 */
function ufwp_update_cache( $items, $key = false ) {

    $cache = get_option( 'ufwp_cache', ufwp_get_cache_structure() );

    // List of courses
    if ( $key ) {

        $serialized_key = serialize( $key );

        $cache['lists'][$serialized_key] = $items;

    // Single or multiple courses
    } else {

        if ( isset ( $items['id'] ) ) {
            $cache['items'][$items['id']] = $items;
        }
    }

    update_option( 'ufwp_cache', $cache );
}

/*
 * Get cache
 */
function ufwp_get_cache( $key ) {

    $cache = get_option( 'ufwp_cache', ufwp_get_cache_structure() );

    //ufwp_debug( $cache );

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
function ufwp_get_cache_key( $args ) {
    return ( is_numeric( $args ) ) ? $args : serialize( $args );
}

/*
 * Delete cache
 */
function ufwp_delete_cache() {
    ufwp_addlog( '*** CACHE MANUALLY DELETED ***' );
    delete_option( 'ufwp_cache' );
}

/*
 * Cleanup cache event
 */
function ufwp_cleanup_cache() {

    $cache = get_option( 'ufwp_cache', ufwp_get_cache_structure() );

    $last_update = ( isset ( $cache['last_update'] ) ) ? $cache['last_update'] : 0;

    $debug = false;

    if ( ( time() - $last_update ) > ( 7 * 60 * 60 * 60 ) || $debug ) {

        $cache = ufwp_get_cache_structure();
        $cache['last_update'] = $last_update;

        // Reset cache
        update_option( 'ufwp_cache', $cache );
    }
}

/*
 * Update cache event
 */
function ufwp_update_cache_event() {

    $options = ufwp_get_options();

    $cache = get_option( 'ufwp_cache', ufwp_get_cache_structure() );

    $cache_duration = ( ! empty ( $options['cache_duration'] ) ) ? intval( $options['cache_duration'] ) : 1440;
    $last_update = ( isset ( $cache['last_update'] ) ) ? intval( $cache['last_update'] ) : 0;

    $debug = false;

    if ( ( time() - $last_update ) > ( $cache_duration * 60 ) || $debug ) {

        $debug_start_time = microtime( true );

        ufwp_addlog( '*** START *** UPDATING CACHE ***' );

        // Single items
        $cache['items'] = ufwp_bulk_update_items( $cache['items'] );

        // Lists
        $cache['lists'] = ufwp_bulk_update_lists( $cache['lists'] );

        // Update timestamp
        $cache['last_update'] = time();

        // Update cache
        update_option( 'ufwp_cache', $cache );

        $debug_execution_time = microtime(true) - $debug_start_time;

        ufwp_addlog( '*** END *** UPDATING CACHE *** EXECUTION TIME: ' . $debug_execution_time . ' SECONDS ***' );
    }
}

/*
 * Bulk update items via API
 */
function ufwp_bulk_update_items( $items ) {

    ufwp_addlog( 'BULK UPDATING ITEMS' );

    $i = 1;

    foreach ( $items as $id => $data ) {

        if ( is_numeric( $id ) ) {

            // Go easy on API and hold on after every 10 items
            if ($i > 0 && $i % 10 == 0) {
                ufwp_addlog( 'UPDATING PAUSED AFTER ' . $i . ' ITEMS' );
                sleep(5);
            }

            // Fetch course
            $course = ufwp_get_course_from_api( $id );

            if ( is_array( $course ) )
                $items[$id] = $course;

            // Update item count
            $i++;
        }
    }

    ufwp_addlog( 'BULK UPDATED ' . ( $i - 1 ) . ' ITEMS' );

    return $items;
}

/*
 * Bulk update lists via API
 */
function ufwp_bulk_update_lists( $lists ) {

    ufwp_addlog( 'BULK UPDATING LISTS' );

    $i = 1;

    foreach ( $lists as $id => $items ) {

        $args = unserialize( $id );

        if ( sizeof( $args ) > 0 ) {

            // Go easy on API and hold on after every 5 lists
            if ($i > 0 && $i % 5 == 0) {
                ufwp_addlog( 'UPDATING PAUSED AFTER ' . $i . ' LISTS' );
                sleep(5);
            }

            // Fetch courses
            $courses = ufwp_get_courses_from_api( $args );

            if ( is_array( $courses ) )
                $lists[$id] = $courses;

            // Update list count
            $i++;
        }
    }

    ufwp_addlog( 'BULK UPDATED ' . ( $i - 1 ) . ' LISTS' );

    return $lists;
}

/*
 * Handle scheduled events
 */
function ufwp_scheduled_events() {

    // Cleanup cache
    ufwp_cleanup_cache();

    // Handle cache updates
    ufwp_update_cache_event();
}
add_action('ufwp_wp_scheduled_events', 'ufwp_scheduled_events');

/*
 * Get courses
 */
function ufwp_get_courses( $atts ) {

    if ( ! function_exists('curl_version') )
        return '<p style="color: darkorange; font-weight: bold;">' . __( 'Please activate PHP curl in order to display Udemy courses.', 'wp-udemy' ) . '</p>';

    // Defaults
    $args = array();
    $courses = array();

    // IDs
    if ( isset ( $atts['id'] ) ) {

        $course_ids = explode(',', str_replace( array( ' ', ';'), array( '', ','), sanitize_text_field( $atts['id'] ) ) );

        foreach ( $course_ids as $id ) {

            $course_cache = ufwp_get_cache( $id );

            // Cache available
            if ( $course_cache ) {

                $courses[] = $course_cache;

                // Cache not available, fetch from API
            } else {

                $course = ufwp_get_course_from_api( $id );
                $courses[] = $course;

                if ( is_array( $course ) ) {
                    ufwp_update_cache( $course );
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

            $category = ufwp_cleanup_category_name ( sanitize_text_field( $atts['category'] ) );
            $categories = ufwp_get_categories();

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

            $courses_cache = ufwp_get_cache( $args );

            // Cache available
            if ( $courses_cache ) {
                $courses = $courses_cache;
            } else {
                $courses = ufwp_get_courses_from_api($args);

                if ( is_array( $courses ) ) {
                    ufwp_update_cache( $courses, $args );
                }
            }
        }
    }

    return $courses;
}

/*
 * Display courses
 */
function ufwp_display_courses( $courses = array(), $args = array() ) {

    //ufwp_debug($courses);

    $options = ufwp_get_options();

    // Defaults
    $type = ( isset ( $args['type'] ) ) ? $args['type'] : 'single';
    $grid = ( isset ( $args['grid'] ) && is_numeric( $args['grid'] ) ) ? $args['grid'] : '3';

    if ( isset ( $args['style'] ) )
        $style = $args['style'];

    // Prepare courses
    $courses = ufwp_get_course_objects_from_array( $courses, $args );

    // Template
    $template_course = ( isset ( $options['template_course'] ) ) ? $options['template_course'] : 'standard';
    $template_courses = ( isset ( $options['template_courses'] ) ) ? $options['template_courses'] : 'list';

    if ( isset ( $args['template'] ) ) {
        $template = str_replace(' ', '', $args['template']);
    } elseif ( 'widget' === $type ) {
        $template = 'widget';
    } else {
        $template = ( sizeof( $courses ) > 1 ) ? $template_courses : $template_course;
    }

    // Get template file
    $file = ufwp_get_template_file( $template, $type );

    // Output
    ob_start();

    echo '<div class="ufwp">';

    if ( file_exists( $file ) ) {
        include( $file );
    } else {
        _e('Template not found.', 'wp-udemy');
    }

    echo '</div>';

    $output = ob_get_clean();

    return $output;
}

/*
 * Get template file
 */
function ufwp_get_template_file( $template, $type ) {

    $template_file = UFWP_DIR . 'templates/' . $template . '.php';

    $template_file = apply_filters( 'ufwp_template_file', $template_file, $template, $type );

    if ( file_exists( $template_file ) )
        return $template_file;

    return ( 'widget' === $type ) ? UFWP_DIR . 'templates/widget.php' : UFWP_DIR . 'templates/standard.php';
}

/*
 * Main categories
 */
function ufwp_get_categories() {
    return array('Academics','Business','Crafts-and-Hobbies','Design','Development','Games','Health-and-Fitness','Humanities','IT-and-Software','Language','Lifestyle','Marketing','Math-and-Science','Music','Office-Productivity','Other','Personal-Development','Photography','Social-Science','Sports','Teacher-Training','Technology','Test','Test-Prep');
}

/**
 * Check content if scripts must be loaded
 */
function ufwp_has_plugin_content() {

    global $post;

    if( ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'ufwp') || has_shortcode( $post->post_content, 'udemy') ) ) ) {
        return true;
    }

    return false;
}