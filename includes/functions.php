<?php
/**
 * Functions
 *
 * @package     UFWP\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
        'items'       => array(),
        'lists'       => array(),
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
            $cache['items'][$items['id']] = ufwp_prepare_course_data_for_cache( $items );
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

    $num = 1;

    foreach ( $items as $id => $data ) {

        if ( is_numeric( $id ) ) {

            // Go easy on API and hold on after every 10 items
            if ($num > 0 && $num % 10 == 0) {
                ufwp_addlog( 'UPDATING PAUSED AFTER ' . $num . ' ITEMS' );
                sleep(5);
            }

            // Fetch course
            $course = ufwp_get_course_from_api( $id );

            if ( is_array( $course ) )
                $items[$id] = $course;

            // Update item count
            $num++;
        }
    }

    ufwp_addlog( 'BULK UPDATED ' . ( $num - 1 ) . ' ITEMS' );

    return $items;
}

/*
 * Bulk update lists via API
 */
function ufwp_bulk_update_lists( $lists ) {

    ufwp_addlog( 'BULK UPDATING LISTS' );

    $num = 1;

    foreach ( $lists as $id => $items ) {

        $args = unserialize( $id );

        if ( sizeof( $args ) > 0 ) {

            // Go easy on API and hold on after every 5 lists
            if ( $num > 0 && $num % 5 == 0 ) {
                ufwp_addlog( 'UPDATING PAUSED AFTER ' . $num . ' LISTS' );
                sleep(5);
            }

            // Fetch courses
            $courses = ufwp_get_courses_from_api( $args );

            if ( is_array( $courses ) )
                $lists[$id] = $courses;

            // Update list count
            $num++;
        }
    }

    ufwp_addlog( 'BULK UPDATED ' . ( $num - 1 ) . ' LISTS' );

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

    // Cleanup image cache
	ufwp_cleanup_image_cache_event();
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

        $course_ids = explode( ',', str_replace( array( ' ', ';' ), array( '', ',' ), sanitize_text_field( $atts['id'] ) ) );

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

        //ufwp_debug( $args, __FUNCTION__ . ' >> $args' );

        // Get courses
        if ( sizeof( $args ) > 0 ) {

            $courses_cache = ufwp_get_cache( $args );

            // Cache available
            if ( ! empty ( $courses_cache ) ) {
                $courses = $courses_cache;
            } else {
                $courses = ufwp_get_courses_from_api($args);

                if ( is_array( $courses ) ) {

                    // For some reason, the API does not use the passed "page_size" parameter and might return more results.
                    if ( ! empty ( $atts['items'] ) && is_numeric( $atts['items'] ) && sizeof( $courses ) > absint( $atts['items'] ) ) {
                        $courses = array_slice( $courses, 0, absint( $atts['items'] ) );
                    }

                    // Update cache.
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

    // Prepare courses
    $courses = ufwp_get_course_objects_from_array( $courses, $args );

    // Template
    $template_course  = ( isset ( $options['template_course'] ) ) ? $options['template_course'] : 'standard';
    $template_courses = ( isset ( $options['template_courses'] ) ) ? $options['template_courses'] : 'list';

    if ( isset ( $args['template'] ) ) {
        $template = str_replace( ' ', '', $args['template'] );
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
    }

    if ( ! file_exists( $file ) ) {
        _e( 'Template not found.', 'wp-udemy' );
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

    if ( ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'ufwp') || has_shortcode( $post->post_content, 'udemy') ) ) ) {
        return true;
    }

    return false;
}

/**
 * Embed AMP styles
 *
 * @param $file
 * @return mixed|string
 */
function ufwp_asset_embed( $file ) {

    $response = wp_remote_get( $file );

    if ( ! is_array( $response ) || ! isset( $response['body'] ) )
        return '';

    $content = $response['body'];

    $targetUrl = UFWP_URL . 'public/';

    $rewriteUrl = function ($matches) use ($targetUrl) {
        $url = $matches['url'];
        // First check also matches protocol-relative urls like //example.com
        if ( ( isset( $url[0] ) && '/' === $url[0] ) || false !== strpos( $url, '://' ) || 0 === strpos( $url, 'data:' ) ) {
            return $matches[0];
        }
        return str_replace( $url, $targetUrl . '/' . $url, $matches[0] );
    };

    $content = preg_replace_callback( '/url\((["\']?)(?<url>.*?)(\\1)\)/', $rewriteUrl, $content );
    $content = preg_replace_callback( '/@import (?!url\()(\'|"|)(?<url>[^\'"\)\n\r]*)\1;?/', $rewriteUrl, $content );
    // Handle 'src' values (used in e.g. calls to AlphaImageLoader, which is a proprietary IE filter)
    $content = preg_replace_callback( '/\bsrc\s*=\s*(["\']?)(?<url>.*?)(\\1)/i', $rewriteUrl, $content );

    return $content;
}

/**
 * Get AMP Styles
 *
 * @return mixed|null|string
 */
function ufwp_get_amp_styles() {

    $options_output = ufwp_get_options();

    // Core styles
    if ( ! ufwp_is_development() )
        $amp_styles = get_transient( 'ufwp_amp_styles' );

    if ( empty( $amp_styles ) ) {

        $amp_styles = '';

        $embed_urls = array(
            UFWP_URL . 'assets/dist/css/amp.css'
        );

        $embed_urls = apply_filters( 'ufwp_amp_embed_urls', $embed_urls );

        foreach ( $embed_urls as $embed_url ) {
            $amp_styles .= ufwp_asset_embed( $embed_url );
        }

        set_transient( 'ufwp_amp_styles', $amp_styles, 60 * 60 * 24 * 7 );
    }

    // Custom styles
    $custom_css_activated = ( isset ( $options_output['custom_css_activated'] ) && $options_output['custom_css_activated'] == '1' ) ? 1 : 0;
    $custom_css           = ( ! empty ( $options_output['custom_css'] ) ) ? $options_output['custom_css'] : '';

    if ( $custom_css_activated == '1' && $custom_css != '' ) {
        $amp_styles .= stripslashes( $custom_css );
    }

    if ( ! empty( $amp_styles ) )
        $amp_styles = ufwp_cleanup_css_for_amp( $amp_styles );

    return $amp_styles;
}

/**
 * Cleanup css for AMP usage
 *
 * @param string $css
 *
 * @return mixed|string
 */
function ufwp_cleanup_css_for_amp( $css = '' ) {

    $css = stripslashes( $css );

    // Remove important declarations
    $css = str_replace( '!important', '', $css );

    return $css;
}

/**
 * Get settings css
 *
 * @param bool $apply_prefix
 * @return string
 */
function ufwp_get_settings_css( $apply_prefix = true ) {

    $options = ufwp_get_options();

    //$prefix = ( $apply_prefix ) ? '.ufwp ' : '';
    $settings_css = '';

    return $settings_css;
}

/**
 * Prepare course data before being cached
 *
 * Only storing fields we need
 *
 * @param $data
 * @return mixed
 */
function ufwp_prepare_course_data_for_cache( $data ) {

    $fields = array(
        'id',
        'url', 'gift_url',
        'image_480x270', 'image_125_H', 'image_200_H', 'image_75x75',
        'title', 'headline', //'description',
        'primary_category', 'primary_subcategory',
        'num_subscribers',
        'is_paid', 'price', 'discount', 'discount_price',
        'avg_rating', 'num_reviews',
        'visible_instructors',
        'num_published_lectures', 'estimated_content_length', 'instructional_level', 'content_info',
        'bestseller_badge_content', 'is_recently_published', // Badges
        'published_time', 'last_update_date',
        'is_published',
    );

    $course_data = array();

    if ( is_array( $data ) && sizeof( $data ) > 0 ) {

        foreach ( $data as $key => $values ) {

            if ( in_array( $key, $fields ) )
                $course_data[$key] = $values;
        }
    }

    return $course_data;
}

/**
 * @return string
 */
function ufwp_get_downloaded_course_images_dirname() {
	return 'ufwp';
}

/**
 * Get uploads course images path
 *
 * @return null|string
 */
function ufwp_get_downloaded_course_images_path() {

	$upload_dir = wp_upload_dir();

	if ( $upload_dir['error'] !== false )
		return null;

	$path = trailingslashit( $upload_dir['basedir'] . '/' . ufwp_get_downloaded_course_images_dirname() );

	return $path;
}

/**
 * Get uploads course images url
 *
 * @return null|string
 */
function ufwp_get_downloaded_course_images_url() {

	$upload_dir = wp_upload_dir();

	if ( $upload_dir['error'] !== false )
		return null;

	$path = trailingslashit( $upload_dir['baseurl'] . '/' . ufwp_get_downloaded_course_images_dirname() );

	return $path;
}

/**
 * Check whether uploaded image already exists or not
 *
 * @param $file_name
 *
 * @return bool|null
 */
function ufwp_downloaded_course_image_exists( $file_name ) {

	$uploads_path = ufwp_get_downloaded_course_images_path();

	$file_path = $uploads_path . $file_name;

	return ( file_exists( $file_path ) ) ? true : false;
}

/**
 * Get uploads image url
 *
 * @param $file_name
 *
 * @return null|string
 */
function ufwp_get_downloaded_course_image_url( $file_name ) {

	$uploads_url = ufwp_get_downloaded_course_images_url();

	$file_url = $uploads_url . $file_name;

	return $file_url;
}

/**
 * Download course image
 *
 * @param $file_name
 * @param $file_url
 *
 * @return array|null
 */
function ufwp_download_course_image( $file_name, $file_url ) {

	// Download image
	$request = wp_remote_get( $file_url );

	$file = wp_remote_retrieve_body( $request );

	if ( ! $file )
		return null;

	// Omit file hash, if exists
    list( $file_url_without_hash ) = explode( '?', $file_url );

    $haystack = ( $file_url_without_hash ) ? $file_url_without_hash : $file_url;

    $file_extension = substr( $haystack , strrpos( $haystack, '.' ) + 1 );

	if ( ! in_array( $file_extension, array( 'jpg', 'jpeg', 'png' ) ) )
		return array( 'error' => __( 'Sorry, this file type is not permitted for security reasons.' ) );

    // Upload image
	$file_upload_dir = ufwp_get_downloaded_course_images_path();

	$new_file = $file_upload_dir . $file_name;

	// Are we able to create the upload folder?
	if ( ! wp_mkdir_p( $file_upload_dir ) ) {
		return array( 'error' => sprintf(
		/* translators: %s: directory path */
			__( 'Unable to create directory %s. Is its parent directory writable by the server?' ),
			$file_upload_dir
		) );
	}

	// Are we able to create the file?
	$ifp = @ fopen( $new_file, 'wb' );

	if ( ! $ifp )
		return array( 'error' => sprintf( __( 'Could not write file %s' ), $new_file ) );

	// Finally write the file
	@fwrite( $ifp, $file );
	fclose( $ifp );
	clearstatcache();

	// Set correct file permissions
	$stat  = @ stat( dirname( $new_file ) );
	$perms = $stat['mode'] & 0007777;
	$perms = $perms & 0000666;
	@ chmod( $new_file, $perms );
	clearstatcache();

	// Prepare uploaded file
	$file_upload_url = ufwp_get_downloaded_course_images_url();

	$file_url = $file_upload_url . $file_name;

	$upload = array(
		'path'  => $new_file,
		'url'   => $file_url,
		'type'  => $file_extension,
		'error' => false
	);

	return $upload;
}

/**
 * Handle cleanup image cache event
 */
function ufwp_cleanup_image_cache_event() {

	$download_images = ( 'download' === ufwp_get_option( 'images', false ) ) ? true : false;

	if ( ! $download_images )
		return;

	$download_img_cached = get_transient( 'ufwp_download_images_cached' );

	if ( ! $download_img_cached ) {
		ufwp_delete_images_cache();
		set_transient( 'ufwp_download_images_cached', 1, 60 * 60 * 24 ); // 24 hours
	}
}

/**
 * Delete downloaded images
 */
function ufwp_delete_images_cache() {

	$dir   = ufwp_get_downloaded_course_images_path();
	$dirit = new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS );
	$recit = new RecursiveIteratorIterator( $dirit, RecursiveIteratorIterator::CHILD_FIRST );

	foreach ( $recit as $file ) {
		$file->isDir() ? rmdir( $file ) : unlink( $file );
	}

	ufwp_addlog( '*** DOWNLOADED IMAGES MANUALLY DELETED ***' );

	return true;
}
