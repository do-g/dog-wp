<?php
/**
* Plugin Name: DOG Media Features
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-media-taxonomy
* Description: Adds categories, mime types, custom filters, etc. to media
* Version: 1.0.48
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__MF_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__MF_PLUGIN_DIR . 'plugin.class.php');

Dog_Media_Features::init();