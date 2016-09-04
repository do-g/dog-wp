<?php
/**
* Plugin Name: DOG Security
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-security
* Description: Checks whether all PHP files block
* Version: 1.0.7
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__SC_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__SC_PLUGIN_DIR . 'plugin.class.php');

Dog_Security::requires(array('Dog_Shared', 'Dog_Form'));
Dog_Security::init();