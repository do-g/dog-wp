<?php
/**
* Plugin Name: DOG Shortcode Youtube
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-shortcode-youtube
* Description: Enables shortcode that helps include youtube playlists and videos on a page
* Version: 1.0.68
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__SY_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__SY_PLUGIN_DIR . 'plugin.class.php');

Dog_Shortcode_YouTube::requires(array('Dog_Shared', 'Dog_Api_YouTube'));
Dog_Shortcode_YouTube::init();