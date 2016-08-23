<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Sample {

	private static $_initialized = false;

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		add_action('init', array(__CLASS__, 'setup'));
		add_action('plugins_loaded', array(__CLASS__, 'register_labels'));
		self::$_initialized = true;
	}

	public static function setup() {
		if (self::check()) {
			dog__txt('Sample translation label');
		} else {
			add_action('admin_init', array(__CLASS__, 'requires'));
		}
	}

	/***** REGISTER TRANSLATION LABELS *****/

	public static function register_labels() {
		$labels_file = realpath(dirname(__FILE__)) . '/_pll_labels.php';
		if (is_file($labels_file) && function_exists('pll_register_string')) {
			require_once($labels_file);
		}
	}

	/***** REQUIRE DEPENDENCIES *****/

	public static function check() {
		return function_exists('dog__txt');
	}

	public static function requires() {
        add_action('admin_notices', array(__CLASS__, 'requires_notice'));
        $plugin_dir = basename(dirname(__FILE__));
        $plugin_name = "{$plugin_dir}/plugin.php";
        deactivate_plugins($plugin_name);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
	}

	public static function requires_notice() {
		$plugin_path = dirname(__FILE__);
		$plugin_file = "{$plugin_path}/plugin.php";
		$plugin_data = get_plugin_data($plugin_file, false, false);
		?><div class="error"><p>Plugin "<?= $plugin_data['Name'] ?>" requires the "DOG Shared" plugin to be installed and active</p></div><?php
	}

}