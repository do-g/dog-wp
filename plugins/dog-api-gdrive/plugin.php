<?php
/**
* Plugin Name: DOG API Google Drive
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-api-gdrive
* Description: Connects with the Google Drive API
* Version: 1.0.0
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AG_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AG_PLUGIN_DIR . 'plugin.class.php');

Dog_Api_GoogleDrive::requires(array('Dog_Shared'));
Dog_Api_GoogleDrive::init();