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

/**
 * Geto ptions
 *
 * @return array
 */
function ufwp_get_options() {
    return get_option( 'ufwp_settings', array() );
}

/**
 * Get single option
 *
 * @param $key
 * @param null $default
 * @return null
 */
function ufwp_get_option( $key, $default = null ) {
    $options = ufwp_get_options();

    return ( isset( $options[$key] ) ) ? $options[$key] : $default;
}

/**
 * Check if AMP endpoint
 */
function ufwp_is_amp() {

    if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() )
        return true;

    if ( function_exists( 'is_wp_amp' ) && is_wp_amp() )
        return true;

    return false;
}

/**
 * Cleanup category name
 *
 * @param $category
 * @return mixed
 */
function ufwp_cleanup_category_name( $category ) {

    $category = str_replace('And', 'and', ucwords( $category, '-' ) );

    return $category;
}

/**
 * Get WP global settings date time
 *
 * @param $timestamp
 * @return false|null|string
 */
function ufwp_get_datetime( $timestamp ) {

    if ( ! is_numeric( $timestamp ) )
        return null;

    $date_format = get_option( 'date_format', 'm/d/Y' );
    $time_format = get_option( 'time_format', 'g:i:s A' );

    return date( $date_format . ' ' . $time_format, $timestamp );
}

/**
 * Output the assets url
 */
function ufwp_the_assets() {
    echo UFWP_URL . 'public';
}

/**
 * Check whether it's development environment or not
 */
function ufwp_is_development() {
    return ( strpos( get_bloginfo( 'url' ), 'kryptonitewp-downloads.local' ) !== false ) ? true : false;
}

/**
 * Check whether we are on our admin pages or not
 *
 * @return bool
 */
function ufwp_is_plugin_admin_area() {

    $screen = get_current_screen();

    return ( strpos( $screen->id, 'ufwp' ) !== false || strpos( $screen->id, 'wp-udemy' ) !== false ) ? true : false;
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

/**
 * Debug function
 *
 * @param $args
 * @param bool $title
 */
function ufwp_debug( $args, $title = false ) {

	if ( ! ufwp_is_development() )
		return;

	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {

		if ( $title ) {
			echo '<h3>' . esc_html( $title ) . '</h3>';
		}

		if ( $args ) {
			echo '<pre>';
			print_r( $args );
			echo '</pre>';
		}
	}
}

/**
 * Debug log
 *
 * @param $log
 */
function ufwp_debug_log ( $log )  {

    if ( ! ufwp_is_development() )
        return;

    if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {

	    if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
            return;
        }
        error_log( $log );
    }
}
