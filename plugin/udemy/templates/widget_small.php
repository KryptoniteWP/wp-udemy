<?php
/*
 * Standard template
 *
 * @package Udemy
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Check if course was forwarded
if ( ! isset ( $courses ) )
    return;
?>

<div class="udemy-widget-small<?php if ( isset( $style ) ) echo ' udemy-style-' . $style; ?>">

    <?php foreach ( $courses as $course ) { ?>

        <?php if ( is_string ( $course ) ) { echo '<p>' . $course . '</p>'; } else { ?>

            <div class="udemy-course" data-udemy-course-id="<?php echo $course->get_id(); ?>">
                <a class="udemy-course__link" href="<?php echo $course->get_url(); ?>" target="_blank" rel="nofollow" title="<?php echo $course->get_title(); ?>">
                    <span class="udemy-course__img" style="background-image: url('<?php echo $course->get_image('widget_small'); ?>');"></span>

                    <span class="udemy-course__content">
                        <span class="udemy-course__title"><?php echo $course->get_title(); ?></span>

                        <span class="udemy-course__footer">
                            <span class="udemy-course__price"><?php echo $course->get_price(); ?></span>
                            <span class="udemy-course__rating"><?php $course->the_star_rating(); ?></span>
                        </span>
                    </span>
                </a>
            </div>

        <?php } ?>

    <?php } ?>

</div>
