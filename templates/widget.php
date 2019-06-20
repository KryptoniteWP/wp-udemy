<?php
/**
 * Widget template
 *
 * @package Udemy
 *
 * @var UFWP_Course $course
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Check if course was forwarded
if ( ! isset ( $courses ) )
    return;
?>

<div class="ufwp-widget<?php if ( isset( $style ) ) echo ' ufwp-style-' . $style; ?>">

    <?php foreach ( $courses as $course ) { ?>

        <?php if ( is_string ( $course ) ) { echo '<p>' . $course . '</p>'; } else { ?>

            <div class="<?php $course->the_classes( 'ufwp-course' ); ?>"<?php $course->the_container(); ?>>
                <a class="ufwp-course__link" href="<?php echo $course->get_url(); ?>" target="_blank" rel="nofollow" title="<?php echo $course->get_title(); ?>">
                    <?php $course->the_badges(); ?>
                    <span class="ufwp-course__img">
                        <img src="<?php echo $course->get_image(); ?>" alt="<?php echo $course->get_image_alt(); ?>">
                    </span>

                    <span class="ufwp-course__content">
                        <span class="ufwp-course__title"><?php echo $course->get_title(); ?></span>
                        <span class="ufwp-course__details"><?php echo $course->get_details(); ?></span>

                        <span class="ufwp-course__footer">
                            <?php if ( $course->show_price() ) { ?>
                                <?php if ( $course->is_on_sale() ) { ?>
                                    <span class="ufwp-course__price ufwp-course__price--list"><?php echo $course->get_list_price(); ?></span>
                                <?php } ?>
                                <span class="ufwp-course__price"><?php echo $course->get_price(); ?></span>
                            <?php } ?>
                            <span class="ufwp-course__rating"><?php $course->the_star_rating(); ?></span>
                        </span>
                    </span>
                </a>
            </div>

        <?php } ?>

    <?php } ?>

</div>
