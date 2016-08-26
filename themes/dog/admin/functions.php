<?php

require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');

function dog_admin__assets_include_css($styles) {
	return array_merge($styles, dog__extend_with('minify_styles', array()));
}

function dog_admin__assets_include_js($scripts) {
	return array_merge($scripts, dog__extend_with('minify_scripts', array()));
}

function dog_admin__menu_order($menu_order) {
	return dog__override_with('admin_menu_order', array('index.php', 'edit.php', 'edit.php?post_type=page', 'edit-comments.php'));
}

function dog_admin__alter_top_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wpseo-menu');
}

add_action('wp_before_admin_bar_render', 'dog_admin__alter_top_bar');
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'dog_admin__menu_order');
add_filter('dog__af_include_css', 'dog_admin__assets_include_css');
add_filter('dog__af_include_js', 'dog_admin__assets_include_js');
dog__call_x_function('admin_hooks');