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

function udemy_get_datetime( $timestamp ) {

    if ( ! is_numeric( $timestamp ) )
        return null;

    $date_format = get_option( 'date_format', 'm/d/Y' );
    $time_format = get_option( 'time_format', 'g:i:s A' );

    return get_date_from_gmt( date( $date_format . ' ' . $time_format, $timestamp ), $date_format . ' - ' . $time_format );
}

function udemy_the_assets() {
    echo UDEMY_URL . 'assets';
}

/**
 * Output data to a log for debugging reasons
 **/
function udemy_addlog( $string ) {

    if ( UDEMY_DEBUG ) {

        $log = get_option( 'udemy_log', '' );

        $string = date( 'd.m.Y H:i:s' ) . " >>> " . $string . "\n";
        $log .= $string;

        update_option( 'udemy_log', $log );
    }
}