<?php
/*
 * Box template
 *
 * @package Udemy
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Check if course was forwarded
if ( ! isset ( $courses ) )
    return;
?>

<?php foreach ( $courses as $course ) { ?>

    <?php if ( is_string ( $course ) ) { echo '<p>' . $course . '</p>'; } else { ?>

        <div class="udemy-course">
            <h3><?php echo $course->get_title(); ?></h3>

            <p><?php echo $course->get_headline(); ?></p>
        </div>

    <?php } ?>

<?php } ?>