<?php
/**
* Plugin Name: DOG Labels
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-sample
* Description: Reads all theme and plugin labels and makes them available for translation
* Version: 1.0.19
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__LB_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__LB_PLUGIN_DIR . 'plugin.class.php');

Dog_Labels::requires(array('Dog_Shared', 'Dog_Form'));
Dog_Labels::init();