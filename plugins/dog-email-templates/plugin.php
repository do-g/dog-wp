<?php
/**
* Plugin Name: DOG Email Templates
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-email-templates
* Description: Adds UI to edit email messages sent by website
* Version: 1.0.4
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__ET_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__ET_PLUGIN_DIR . 'plugin.class.php');

Dog_Email_Templates::requires(array('Dog_Shared'));
Dog_Email_Templates::init();