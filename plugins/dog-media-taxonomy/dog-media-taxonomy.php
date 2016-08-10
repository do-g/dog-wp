<?php
/**
* Plugin Name: DOG Media Taxonomy
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-media-taxonomy
* Description: Adds categories to media
* Version: 1.0
* Author: Dorin Gurău
* License: Private
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__MT_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__MT_PLUGIN_DIR . 'class.media-taxonomy.php');

Dog_Media_Taxonomy::init();

if ( is_admin() ) {

}