<?php
/**
* Plugin Name: DOG Sample
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-sample
* Description: Sample plugin intended to be used as a fresh start for new plugins
* Version: 1.0.0
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__SP_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__SP_PLUGIN_DIR . 'plugin.class.php');

Dog_Sample::requires(array('Dog_Shared'));
Dog_Sample::init();