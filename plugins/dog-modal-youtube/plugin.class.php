<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Modal_Youtube {

	const PLUGIN_SLUG = 'dog-modal-youtube';
	const REL_FRAGMENT = 'dog-md-youtube-gallery';
	private static $_initialized = false;
	private static $_config = array();
	private static $_dependencies = array();

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
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	private static function load_config() {
		return apply_filters('dog__my_options', array(
			'auto_init' => false,
			'loop' => true,
			'auto_advance_delay' => 0,
			'rel_fragment' => self::REL_FRAGMENT,
		));
	}

	public static function config() {
		if (!self::$_config) {
			self::$_config = self::load_config();
		}
		$config = self::$_config;
		$args = func_get_args();
		while ($args) {
			$arg = array_shift($args);
			$config = $config[$arg];
		}
		return $config;
	}

	public static function enqueue_assets() {
		wp_enqueue_style('dog_my_styles', dog__plugin_url('styles.css', self::PLUGIN_SLUG), array('dog_md_styles'));
		wp_enqueue_script('dog_my_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('dog_md_scripts'), null, true);
		$js_vars = apply_filters('dog__my_options', self::config());
		wp_localize_script('dog_my_scripts', 'dog__my', $js_vars);
	}

	/***** REGISTER TRANSLATION LABELS *****/

	public static function register_labels() {
		$labels_file = realpath(dirname(__FILE__)) . '/_pll_labels.php';
		if (is_file($labels_file) && function_exists('pll_register_string')) {
			require_once($labels_file);
		}
	}

	/***** REQUIRE DEPENDENCIES *****/

	public static function requires($dependencies) {
		self::$_dependencies = $dependencies;
	}

	public static function check() {
		if (self::$_dependencies) {
			foreach (self::$_dependencies as $d) {
				if (!class_exists($d)) {
					return false;
				}
			}
		}
		return true;
	}

	public static function depends() {
        add_action('admin_notices', array(__CLASS__, 'depends_notice'));
        $plugin_dir = basename(dirname(__FILE__));
        $plugin_name = "{$plugin_dir}/plugin.php";
        deactivate_plugins($plugin_name);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
	}

	public static function depends_notice() {
		$plugin_path = dirname(__FILE__);
		$plugin_file = "{$plugin_path}/plugin.php";
		$plugin_data = get_plugin_data($plugin_file, false, false);
		?><div class="error"><p>Plugin <b><?= $plugin_data['Name'] ?></b> requires the following plugins to be installed and active: <b><?= str_replace('_', ' ', implode('</b>, <b>', self::$_dependencies)) ?></b></p></div><?php
	}

}