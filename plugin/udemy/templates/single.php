<?php
/*
 * Box template
 *
 * @package Udemy
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Check if course was forwarded
if ( ! isset ( $course ) )
    return;
?>

<div class="udemy-box">
    <h3><?php echo $course['headline']; ?></h3>
</div>
