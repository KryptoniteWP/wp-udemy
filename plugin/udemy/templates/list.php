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

<div class="udemy-list">
    <?php foreach ($courses as $course) { ?>

        <div class="udemy-list__item">
            <?php if ( isset ( $course['title'] ) ) { ?>
                <h3><?php echo $course['title']; ?></h3>
            <?php } ?>

            <?php if ( isset ( $course['headline'] ) ) { ?>
                <p><?php echo $course['headline']; ?></p>
            <?php } ?>
        </div>

    <?php } ?>
</div>