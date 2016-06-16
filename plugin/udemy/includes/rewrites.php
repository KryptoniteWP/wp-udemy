<?php
/**
 * Rewrites
 *
 * @package     Udemy\Rewrites
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Add custom rewrite rules
 */
function udemy_add_rewrite_rules() {

    global $wp_rewrite;

    $new_non_wp_rules = array(
        'udemy-course/(.*)'    => 'wp-content/plugins/udemy/public/redirect.php?course=$1',
    );

    $wp_rewrite->non_wp_rules += $new_non_wp_rules;

    //udemy_debug($wp_rewrite);
}
add_action('generate_rewrite_rules', 'udemy_add_rewrite_rules');