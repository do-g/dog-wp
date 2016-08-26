<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

function dogx__enqueue_assets_low_priority($params) {
	if (!$params['cached_styles']) {
		wp_enqueue_style('vendor_styles', dog__css_url('vendor'), null, null);
		wp_enqueue_style('base_styles', dog__base_css_url('styles'), array('vendor_styles'), null);
		wp_enqueue_style('styles', dog__css_url('styles'), array('base_styles'), null);
	}
	if (!$params['cached_scripts']) {
		wp_enqueue_script('base_vendor_scripts', dog__base_js_url('vendor'), null, null, true);
		wp_enqueue_script('vendor_scripts', dog__js_url('vendor'), array('base_vendor_scripts'), null, true);
		wp_enqueue_script('dog_sh_scripts_shared', null, array('vendor_scripts'));
		wp_enqueue_script('base_scripts', dog__base_js_url('scripts'), array('dog_sh_scripts_shared'), null, true);
		wp_enqueue_script('scripts', dog__js_url('scripts'), array('base_scripts'), null, true);
		wp_localize_script('shared_scripts', 'dog__sh', Dog_Shared::get_js_vars());
	}
}

function dogx__minify_styles() {
	return array(
		dog__css_url('vendor'),
		dog__base_css_url('styles'),
		dog__css_url('styles'),
	);
}

function dogx__minify_scripts() {
	return array(
		dog__base_js_url('vendor'),
		dog__js_url('vendor'),
		dog__plugin_url('shared.js', Dog_Shared::PLUGIN_SLUG),
		dog__base_js_url('scripts'),
		dog__js_url('scripts'),
	);
}

function dogx__theme_setup() {
	register_nav_menu('location-main-menu', 'Locație Meniu Principal');
	add_image_size('xsmall', 400,  9999);
	add_image_size('small',  600,  9999);
	add_image_size('xlarge', 1200, 9999);
}