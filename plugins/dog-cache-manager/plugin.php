<?php
/**
* Plugin Name: DOG Cache Manager
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-cache-manager
* Description: Adds fragment cache functionality and UI
* Version: 1.0.14
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__CM_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__CM_PLUGIN_DIR . 'plugin.class.php');

Dog_Cache_Manager::requires(array('Dog_Shared'));
Dog_Cache_Manager::init();