<?php
/**
* Plugin Name: DOG Form
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-sample
* Description: Simplified form development with improved security
* Version: 1.0.15
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__FR_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__FR_PLUGIN_DIR . 'plugin.class.php');

Dog_Form::requires(array('Dog_Shared'));
Dog_Form::init();