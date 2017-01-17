<?php
/**
* Plugin Name: DOG Asset Optimiser
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-asset-optimiser
* Description: Minifies and compresses assets
* Version: 1.0.132
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AO_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AO_PLUGIN_DIR . 'plugin.class.php');

Dog_Asset_Optimiser::requires(array('Dog_Shared', 'Dog_Form'));
Dog_Asset_Optimiser::init();