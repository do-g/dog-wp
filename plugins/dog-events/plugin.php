<?php
/**
* Plugin Name: DOG Events
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-events
* Description: Helps manage and schedule one time and recurrent events
* Version: 1.0.1
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__EV_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__EV_PLUGIN_DIR . 'plugin.class.php');

Dog_Events::requires(array('Dog_Shared'));
Dog_Events::init();