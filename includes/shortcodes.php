<?php
/**
 * Shortcodes
 *
 * @package     UFWP\Shortcodes
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Maybe cleanup content in order to remove empty p and br tags for our shortcodes
 */
function ufwp_maybe_cleanup_shortcode_output( $content ) {

    // array of custom shortcodes requiring the fix
    $block = join("|",array(
        'ufwp', 'udemy'
    ) );

    // opening tag
    $rep = preg_replace( "/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/","[$2$3]",$content );

    // closing tag
    $rep = preg_replace( "/(<p>)?\[\/($block)](<\/p>|<br \/>)?/","[/$2]",$rep );

    return $rep;
}
add_filter( 'the_content', 'ufwp_maybe_cleanup_shortcode_output' );

/**
 * Register our main shortcode
 *
 * @param $atts
 * @return string|null
 */
function ufwp_add_shortcode( $atts ) {

    // Defaults
    $courses_list = false;

    $output      = '';
    $output_args = array();

    // Get courses
    $courses = ufwp_get_courses( $atts );

    if ( is_string( $courses ) )
        return $courses;

    // Type: IDs
    if ( ! isset( $atts['id'] ) ) {
        $courses_list = true;

        // Shortcode atts
        if ( isset ( $atts['grid'] ) )
            $output_args['grid'] = sanitize_text_field( $atts['grid'] );
    }

    if ( is_array( $courses ) & sizeof( $courses ) > 0 ) {

        // Items
        $output_args['items'] = sizeof( $courses );

        // Set type
        $output_args['type'] = ( $courses_list ) ? 'list' : 'single';

        if ( isset ( $atts['type'] ) ) {
            $output_args['type'] = $atts['type'];
        }

        // Shortcode atts
        if ( isset ( $atts['style'] ) )
            $output_args['style'] = sanitize_text_field( $atts['style'] );

        if ( ufwp_is_amp() ) {
            $output_args['template'] = 'amp';
        } elseif ( isset ( $atts['template'] ) ) {
            $output_args['template'] = sanitize_text_field( $atts['template'] );
        }

        if ( isset ( $atts['price'] ) && in_array( $atts['price'], array( 'show', 'hide', 'none' ) ) )
            $output_args['price'] = sanitize_text_field( $atts['price'] );

        $output_args = apply_filters( 'ufwp_shortcode_output_args', $output_args, $atts );

        $output = ufwp_display_courses( $courses, $output_args );
    }

    // Remove empty paragraphs
    //$output = str_replace(array("<p></p>"), '', $output);

    // Remove unwanted line breaks from output
    $output = preg_replace('/^\s+|\n|\r|\s+$/m', '', $output );

    return $output;
}
add_shortcode( 'ufwp', 'ufwp_add_shortcode' );
add_shortcode( 'udemy', 'ufwp_add_shortcode' );

/**
 * Debug
 */
add_shortcode('udemy_debug', function( $atts ) {



});
