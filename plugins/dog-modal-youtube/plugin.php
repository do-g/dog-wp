<?php
/**
* Plugin Name: DOG Modal YouTube
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-modal-youtube
* Description: Adds YouTube modal popup & video gallery features to website
* Version: 1.0.5
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__MY_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__MY_PLUGIN_DIR . 'plugin.class.php');

Dog_Modal_Youtube::requires(array('Dog_Shared', 'Dog_Modal'));
Dog_Modal_Youtube::init();