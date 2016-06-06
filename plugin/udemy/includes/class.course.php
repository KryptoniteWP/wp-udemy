<?php
/**
 * Course
 *
 * @package     Udemy\Course
 * @since       1.0.0
 */


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('Udemy_Course')) {

    class Udemy_Course
    {
        public $course;
        public $options;

        public function __construct( $course ) {

            // Variables
            $this->course = $course;
        }

        public function get_title() {
            return ( isset ( $this->course['title'] ) ) ? $this->course['title'] : '';
        }

        public function get_image( $size = null ) {

            $image = 'image_480x270';

            if ( 'small' === $size )
                $image = 'image_125_H';

            return ( isset ( $this->course[$image] ) ) ? $this->course[$image] : '';
        }

        public function get_image_alt() {
            return ( isset ( $this->course['title'] ) ) ? str_replace('"', "'", $this->course['title'] ) : '';
        }

        public function get_headline() {
            return ( isset ( $this->course['headline'] ) ) ? $this->course['headline'] : '';
        }

        public function get_url() {

            global $udemy_args;

            if ( isset ( $udemy_args['url'] ) )
                return $udemy_args['url'];

            $url = 'https://www.udemy.com';

            if ( isset ( $this->course['url'] ) )
                $url .= $this->course['url'];

            return $url;
        }

        public function get_price() {
            return ( isset ( $this->course['price'] ) ) ? $this->course['price'] : '';
        }
    }
}