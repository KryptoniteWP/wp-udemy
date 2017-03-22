<?php
/**
 * Helper
 *
 * @package     UFWP\Helper
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ufwp_debug( $args, $title = false ) {

    if ( $title ) {
        echo '<h3>' . $title . '</h3>';
    }

    if ( $args ) {
        echo '<pre>';
        print_r($args);
        echo '</pre>';
    }
}

function ufwp_cleanup_category_name( $category ) {

    $category = str_replace('And', 'and', ucwords( $category, '-' ) );

    return $category;
}

function ufwp_get_datetime( $timestamp ) {

    if ( ! is_numeric( $timestamp ) )
        return null;

    $date_format = get_option( 'date_format', 'm/d/Y' );
    $time_format = get_option( 'time_format', 'g:i:s A' );

    return date( $date_format . ' ' . $time_format, $timestamp );
}

function ufwp_the_assets() {
    echo UFWP_URL . 'public/assets';
}

/**
 * Output data to a log for debugging reasons
 **/
function ufwp_addlog( $string ) {

    if ( UFWP_DEBUG ) {

        $log = get_option( 'ufwp_log', '' );

        $string = date( 'd.m.Y H:i:s' ) . " >>> " . $string . "\n";
        $log .= $string;

        update_option( 'ufwp_log', $log );
    }
}

/*
 * Get options
 */
function ufwp_get_options() {
    return get_option( 'ufwp_settings', array() );
}