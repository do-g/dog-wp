<?php
/**
* Plugin Name: DOG API MailChimp
* Plugin URI: http://public.dorinoanagurau.ro/wp/plugins/dog-api-mailchimp
* Description: Connects with the MailChimp API
* Version: 1.0.6
* Author: Dorin Gurău
* License: Private
* Text Domain: dog
*/

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__AM_PLUGIN_DIR', plugin_dir_path(__FILE__ ));

require_once(DOG__AM_PLUGIN_DIR . 'plugin.class.php');

Dog_Api_MailChimp::requires(array('Dog_Shared'));
Dog_Api_MailChimp::init();