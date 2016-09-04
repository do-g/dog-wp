<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Modal {

	const PLUGIN_SLUG = 'dog-modal';
	private static $_initialized = false;
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
			add_action('wp_enqueue_scripts', array(__CLASS__, 'register_site_assets'));
			add_action('wp_footer', array(__CLASS__, 'inject_html'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	public static function register_site_assets() {
		wp_enqueue_style('dog_md_styles', dog__plugin_url('styles.css', self::PLUGIN_SLUG), array('base_styles'));
		wp_enqueue_script('dog_md_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('base_scripts'), null, true);
		$js_vars = apply_filters('dog__md_options', array(
			'popup' => array(
				'auto_init' => false,
			),
			'gallery' => array(
				'labels' => array(
					'of' => dog__txt('din'),
				),
				'images' => array(
					'left_arrow_url' => dog__img_url('left-nav-arrow.svg'),
					'right_arrow_url' => dog__img_url('right-nav-arrow.svg'),
				),
			),
			'image_gallery' => array(
				'auto_init' => false,
				'auto_advance_delay' => 0,
				'use_background_images' => false,
			),
		));
		wp_localize_script('dog_md_scripts', 'dog__md', $js_vars);
	}

	public static function inject_html() {
		echo file_get_contents(dog__sibling_path('markup.html', __FILE__));
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