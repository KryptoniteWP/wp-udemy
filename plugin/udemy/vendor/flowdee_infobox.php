<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Flowdee_Infobox' ) ) {

    class Flowdee_Infobox {

        private $debug = false;

        private $lang_de;

        private $url = 'https://cdn.flowdee.de/coder/infobox-plugins.json';
        private $data = null;

        private $slug;
        private $max;

        /**
         * Construct the plugin object
         */
        public function __construct() {

            // Set language
            $this->lang_de = ( strpos( get_bloginfo('language') ,'de-') !== false ) ? true : false;

            if ( false === ( $this->data = get_transient( 'flowdee_infobox_data' ) ) || $this->debug ) {

                // Fetch data
                $response = wp_remote_get($this->url, array());

                if (is_wp_error($response) || $response['response']['code'] != '200' || empty($response['body'])) {
                    return null;
                }

                $json = json_decode($response['body']);
                $this->data = $json;

                set_transient('flowdee_infobox_data', $this->data, 60 * 60 * 24);
            }
        }

        /*
         * Set plugin slug
         */
        public function set_plugin_slug($slug) {
            $this->slug = $slug;
        }

        /*
         * Output
         */
        public function display() {

            // Quit if data is not available
            if ( is_null($this->data) )
                return;

            // Set max amount of boxes to show
            $this->max = ( isset($this->data->max) ) ? $this->data->max : 1;

            // Loop boxes
            if ( !isset($this->data->boxes) )
                return;

            $this->display_styles();

            $boxes_left = $this->max;
            $boxes_removable = array();

            foreach ( $this->data->boxes as $box ) {

                if ( isset($box->plugins) && sizeof($box->plugins) > 0 && !in_array( $this->slug, $box->plugins) || isset($box->id) && in_array($box->id, $boxes_removable ) ) {
                    continue;
                }

                if ( !$this->lang_de && strpos($box->id,'_de') !== false ) {
                    continue;
                }

                $this->display_box($box);

                if ( $this->lang_de && isset($box->id) && strpos($box->id,'_de') !== false ) {
                    $boxes_removable[] = str_replace('_de', '', $box->id);
                }

                // Handle box count
                $boxes_left--;

                if ($boxes_left == 0)
                    break;
            }

        }

        private function display_box($box) {
            ?>

            <div class="postbox">
                <?php if (!empty($box->title)) { ?>
                    <h3><span><?php echo $box->title; ?></span></h3>
                <?php } ?>
                <div class="inside">

                    <!-- Header Text -->
                    <?php if (!empty($box->header)) { ?>
                        <p><?php echo $box->header; ?></p>
                    <?php } ?>

                    <!-- Infobox Image -->
                    <?php if (!empty($box->img)) { ?>
                        <?php if (!empty($box->img_link)) { ?>
                            <a href="<?php echo esc_url($box->img_link); ?>" target="_blank" rel="nofollow">
                                <img src="<?php echo esc_url($box->img); ?>" class="flowdee-infobox__img" />
                            </a>
                        <?php } else { ?>
                            <img src="<?php echo esc_url($box->img); ?>" class="flowdee-infobox__img" />
                        <?php } ?>
                    <?php } ?>

                    <!-- Infobox Text -->
                    <?php if (!empty($box->text) && is_array($box->text)) { ?>
                        <?php foreach ($box->text as $p) { ?>
                            <p><?php echo $p; ?></p>
                        <?php } ?>
                    <?php } ?>

                    <!-- Infobox Links -->
                    <?php if (!empty($box->links)) {
                        $links = '';

                        foreach ($box->links as $name => $href) {
                            $links .= '<li><a href="' . esc_url($href) . '" target="_blank">' . $name . '</a></li>';
                        }

                        if (!empty($links)) {
                            echo '<ul>' . $links . '</ul>';
                        }
                    } ?>

                    <!-- Footer Text -->
                    <?php if (!empty($box->footer)) { ?>
                        <p><?php echo $box->footer; ?></p>
                    <?php } ?>
                </div>
            </div>

            <?php
        }

        private function display_styles() {
            ?>
            <style>
                .flowdee-infobox__img { display: block; max-width: 100%; height: auto; margin: 0 auto; }
            </style>
            <?php
        }

        /*
         * Debug
         */
        private function debug($arg) {
            echo '<pre>';
            print_r($arg);
            echo '</pre>';
        }
    }
}