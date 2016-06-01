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