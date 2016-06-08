<?php
/*
 * Grid template
 *
 * @package Udemy
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Check if course was forwarded
if ( ! isset ( $courses ) )
    return;
?>

<div class="udemy-grid<?php if ( isset( $style ) ) echo ' udemy-style-' . $style; ?><?php if ( isset( $grid ) ) echo ' udemy-grid--col-' . $grid; ?>">

    <?php foreach ( $courses as $course ) { ?>

        <?php if ( is_string ( $course ) ) continue; ?>

        <div class="udemy-grid__item">

            <div class="udemy-course" data-udemy-course-id="<?php echo $course->get_id(); ?>">
                <a class="udemy-course__link" href="<?php echo $course->get_url(); ?>" target="_blank" rel="nofollow" title="<?php echo $course->get_title(); ?>">
                    <img class="udemy-course__img" src="<?php echo $course->get_image(); ?>" alt="<?php echo $course->get_image_alt(); ?>">

                    <span class="udemy-course__content">
                        <span class="udemy-course__title"><?php echo $course->get_title(); ?></span>
                        <span class="udemy-course__details"><?php echo $course->get_details(); ?></span>
                        <span class="udemy-course__rating"><?php $course->the_star_rating(); ?> <?php echo $course->get_rating(); ?></span>
                        <span class="udemy-course__price"><?php echo $course->get_price(); ?></span>
                    </span>
                </a>
            </div>
        </div>

    <?php } ?>
</div>