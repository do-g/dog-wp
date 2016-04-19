<?php
require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');

function dogx__enqueue_assets_low_priority($params) {
	if (!$params['cached_styles']) {
		wp_enqueue_style('styles', dog__css_url('styles'), array('base_styles'), null);
	}
	if (!$params['cached_scripts']) {
		wp_enqueue_script('scripts', dog__js_url('scripts'), array('base_scripts'), null, true);
	}
}

function dogx__minify_styles() {
	return array(
		dog__css_url('styles')
	);
}

function dogx__minify_scripts() {
	return array(
		dog__js_url('scripts')
	);
}

function dogx__theme_setup() {
	register_nav_menu('location-main-menu', 'Locație Meniu Principal');
	add_image_size('small', 600, 9999);
}