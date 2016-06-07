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
            <span class="udemy-course__title"><?php echo $course->get_title(); ?></span>

            <span class="udemy-course__details"><?php echo $course->get_details(); ?></span>
        </div>

    <?php } ?>

<?php } ?>