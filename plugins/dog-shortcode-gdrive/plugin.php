<?php
/**
* Plugin Name: DOG Shortcode Google Drive
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-shortcode-gdrive
* Description: Helps embed drive documents into posts
* Version: 1.0.28
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__SG_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__SG_PLUGIN_DIR . 'plugin.class.php');

Dog_Shortcode_GoogleDrive::requires(array('Dog_Shared', 'Dog_Api_GoogleDrive'));
Dog_Shortcode_GoogleDrive::init();