<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

function dogx__enqueue_assets_low_priority() {
	if (!dog__min_styles_active()) {
		wp_enqueue_style('base_styles', dog__base_css_url('styles'), null, null);
		wp_enqueue_style('styles', dog__css_url('styles'), array('base_styles'), null);
		wp_enqueue_script('jquery');
		wp_enqueue_script('vendor_scripts', dog__js_url('vendor'), array('jquery'), null, true);
		wp_enqueue_script('base_scripts', dog__base_js_url('scripts'), array('dog_sh_scripts'), null, true);
		wp_enqueue_script('scripts', dog__js_url('scripts'), array('base_scripts'), null, true);
	}
}

function dogx__dependencies() {
	return array('Dog_Form');
}