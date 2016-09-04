<?php
/**
* Plugin Name: DOG Asset Features
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-asset-features
* Description: Minifies and compresses assets
* Version: 1.0.89
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AF_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AF_PLUGIN_DIR . 'plugin.class.php');

Dog_Asset_Features::requires(array('Dog_Shared', 'Dog_Form'));
Dog_Asset_Features::init();