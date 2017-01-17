<?php
/**
* Plugin Name: DOG API JSON
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-api-json
* Description: Exposes website data to 3rd party tools
* Version: 1.0.47
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AJ_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AJ_PLUGIN_DIR . 'plugin.class.php');

Dog_Api_Json::requires(array('Dog_Shared'));
Dog_Api_Json::init();