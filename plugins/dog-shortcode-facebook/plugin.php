<?php
/**
* Plugin Name: DOG Shortcode Facebook
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-shortcode-facebook
* Description: Integrates facebook albums and photos into website pages
* Version: 1.0.120
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__SF_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__SF_PLUGIN_DIR . 'plugin.class.php');

Dog_Shortcode_Facebook::requires(array('Dog_Shared', 'Dog_Api_Facebook'));
Dog_Shortcode_Facebook::init();