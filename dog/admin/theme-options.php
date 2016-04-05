<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
require_once(realpath(dirname(__FILE__)) . '/functions.php');
add_action('admin_menu', 'dog_admin__add_menu');
add_action('admin_enqueue_scripts', 'dog_admin__enqueue_assets', 99999);
add_action('wp_ajax_' . DOG_ADMIN__WP_ACTION_AJAX_CALLBACK, 'dog_admin__ajax_handler');
dog__call_x_function('admin_hooks');