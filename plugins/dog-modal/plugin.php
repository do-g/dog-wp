<?php
/**
* Plugin Name: DOG Modal & Gallery
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-sample
* Description: Adds modal popup & image gallery features to website
* Version: 1.0.34
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__MD_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__MD_PLUGIN_DIR . 'plugin.class.php');

Dog_Modal::requires(array());
Dog_Modal::init();