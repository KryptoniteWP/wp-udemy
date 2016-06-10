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
function udemy_get_course_objects_from_array( $results = array() ) {

    $objects = array();

    if ( sizeof( $results ) > 0 ) {

        foreach ( $results as $result ) {
            $objects[] = new Udemy_Course( $result );
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
        'lists' => array()
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
 * Get courses
 */
function udemy_get_courses( $atts ) {

    //echo '<h4>Shortcode atts</h4>';
    //udemy_debug($atts);

    // Defaults
    $args = array();
    $courses = array();

    // IDs
    if ( isset ( $atts['id'] ) ) {

        $course_ids = explode(',', str_replace(' ', '', sanitize_text_field( $atts['id'] ) ) );

        foreach ( $course_ids as $id ) {

            $course_cache = udemy_get_cache( $id );

            // Cache available
            if ( $course_cache ) {
                echo '<p>Cache available for ' . $id . '!</p>';
                $courses[] = $course_cache;

                // Cache not available, fetch from API
            } else {
                echo '<p>Cache NOT available for ' . $id . '!</p>';
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
                echo '<p>Cache available for this list!</p>';
                $courses = $courses_cache;
            } else {
                echo '<p>Cache <u>NOT</u> available for this list!</p>';
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