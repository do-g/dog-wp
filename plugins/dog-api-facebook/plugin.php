<?php
/**
* Plugin Name: DOG API Facebook
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-api-facebook
* Description: Connects with the Facebook Graph API
* Version: 1.0.15
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AF_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AF_PLUGIN_DIR . 'plugin.class.php');

Dog_Api_Facebook::requires(array('Dog_Shared'));
Dog_Api_Facebook::init();