<?php
/**
* Plugin Name: DOG Shared
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog
* Description: Holds shared plugin functionality
* Version: 1.0.38
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__SH_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__SH_PLUGIN_DIR . 'functions.php');
require_once(DOG__SH_PLUGIN_DIR . 'plugin.class.php');

Dog_Shared::init();