<?php
require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');

function dogx__enqueue_assets_low_priority() {
	if (dog__is_env_dev()) {
		wp_enqueue_style('styles', dog__css_url('styles'), array('base_styles'), null);
		wp_enqueue_script('scripts', dog__js_url('scripts'), array('base_scripts'), null, true);
	}
}

function dogx__theme_setup() {
	register_nav_menu('location-main-menu', 'Locație Meniu Principal');
	add_image_size('small', 600, 9999);
}