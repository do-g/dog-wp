<?php
require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');

define('DOG__EMAIL_CONTACT', null);

function dogx__enqueue_assets_low_priority($params) {
	if (!$params['cached_styles']) {
		wp_enqueue_style('vendor', dog__css_url('vendor'), array('base_styles'), null);
		wp_enqueue_style('styles', dog__css_url('styles'), array('vendor'), null);
	}
	if (!$params['cached_scripts']) {
		wp_enqueue_script('vendor', dog__js_url('vendor'), array('base_scripts'), null, true);
		wp_enqueue_script('scripts', dog__js_url('scripts'), array('vendor'), null, true);
	}
}

function dogx__minify_styles() {
	return array(
		dog__css_url('vendor'),
		dog__css_url('styles')
	);
}

function dogx__minify_scripts() {
	return array(
		dog__js_url('vendor'),
		dog__js_url('scripts')
	);
}

function dogx__theme_setup() {
	register_nav_menu('location-main-menu', 'Locație Meniu Principal');
	add_image_size('xsmall', 400,  9999);
	add_image_size('small',  600,  9999);
	add_image_size('xlarge', 1200, 9999);
}