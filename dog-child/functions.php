<?php
require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');

function dogx__add_custom_image_sizes() {
	add_image_size('small', 600, 9999);
}

function dogx__enqueue_assets_low_priority() {
	if (dog__is_env_dev()) {
		wp_enqueue_style('styles', dog__css_url('styles'), array('base_styles'), null);
		wp_enqueue_script('scripts', dog__js_url('scripts'), array('base_scripts'), null, true);
	}
}