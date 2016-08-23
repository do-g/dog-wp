<?php
/**
* Plugin Name: DOG Updater
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-updater
* Description: Updates DOG plugins and themes
* Version: 1.0.23
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__UP_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__UP_PLUGIN_DIR . 'plugin.class.php');

Dog_Updater::init();