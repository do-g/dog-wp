<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Shared {

	private static $_initialized = false;

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		add_action('plugins_loaded', array(__CLASS__, 'register_labels'));
		register_deactivation_hook(dog__get_full_plugin_name_from_path(__FILE__), array(__CLASS__, 'deactivate'));
		self::$_initialized = true;
	}

	public static function deactivate() {
		dog__switch_theme();
	}

	/***** REGISTER TRANSLATION LABELS *****/

	public static function register_labels() {
		$labels_file = realpath(dirname(__FILE__)) . '/_pll_labels.php';
		if (is_file($labels_file) && function_exists('pll_register_string')) {
			require_once($labels_file);
		}
	}

}