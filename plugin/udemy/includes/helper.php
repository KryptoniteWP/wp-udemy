<?php
/**
 * Helper
 *
 * @package     Udemy\Helper
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function udemy_debug( $args ) {
    echo '<pre>';
    print_r($args);
    echo '</pre>';
}

function udemy_cleanup_category_name( $category ) {

    $category = str_replace('And', 'and', ucwords( $category, '-' ) );

    return $category;
}