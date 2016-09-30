<?php
/**
* Plugin Name: DOG API YouTube
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-api-youtube
* Description: Connects with the YouTube Data API
* Version: 1.0.15
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AY_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AY_PLUGIN_DIR . 'plugin.class.php');

Dog_Api_YouTube::requires(array());
Dog_Api_YouTube::init();