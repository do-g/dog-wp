<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Shared {

	const PLUGIN_SLUG = 'dog-shared';
	const MENU_HOOK_PREFIX = 'optiuni-dog';
	const AJAX_CALLBACK = 'dog';
	private static $_config = array();
	private static $_initialized = false;

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		self::register_session();
		add_action('plugins_loaded', array(__CLASS__, 'register_labels'));
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_site_assets'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
		add_action('wp_ajax_' . self::AJAX_CALLBACK, array(__CLASS__, 'ajax_handler'));
		add_action('wp_ajax_nopriv_' . self::AJAX_CALLBACK, array(__CLASS__, 'ajax_handler'));
		add_action('admin_menu', array(__CLASS__, 'add_menu'));
		register_deactivation_hook(dog__get_plugin_name_from_path(__FILE__, true), array(__CLASS__, 'deactivate'));
		self::$_initialized = true;
	}

	public static function enqueue_site_assets() {
		wp_enqueue_script('dog_sh_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), self::config('script_dependencies'), null, true);
		wp_localize_script('dog_sh_scripts', 'dog__sh', self::get_js_vars());
		wp_localize_script('dog_sh_scripts', 'dog__non', self::get_nonces());
	}

	public static function enqueue_admin_assets() {
		wp_enqueue_style('dog_sh_admin_styles', dog__plugin_url('admin.css', self::PLUGIN_SLUG), null, null);
		wp_enqueue_script('dog_sh_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('jquery'), null, true);
		wp_enqueue_script('dog_sh_admin_scripts', dog__plugin_url('admin.js', self::PLUGIN_SLUG), array('dog_sh_scripts'), null, true);
		wp_localize_script('dog_sh_scripts', 'dog__sh', self::get_js_vars());
		wp_localize_script('dog_sh_scripts', 'dog__non', self::get_nonces());
	}

	public static function get_js_vars() {
		return apply_filters('dog__sh_js_vars', array(
			'lang' => dog__active_language(),
			'is_debug' => dog__is_debug(),
			'home_url' => dog__home_url(),
			'safe_home_url' => esc_url(dog__home_url()),
			'theme_url' => dog__theme_url('/'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'ajax_callback' => self::AJAX_CALLBACK,
			'nc_name' => DOG__NC_NAME,
			'nc_var_prefix' => DOG__NC_VAR_PREFIX,
			'hp_jar_name' => DOG__HP_JAR_NAME,
			'hp_time_name' => DOG__HP_TIMER_NAME,
			'ajax_response_status_success' => DOG__AJAX_RESPONSE_STATUS_SUCCESS,
			'ajax_response_status_error' => DOG__AJAX_RESPONSE_STATUS_ERROR,
			'labels' => array(
				'alert_response_error_nonce' => dog__txt('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi validat'),
				'alert_response_error' => dog__txt('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi procesat'),
				'alert_request_error' => dog__txt('Sistemul a întâmpinat o eroare. Cererea nu poate fi trimisă'),
			),
		));
	}

	public static function get_nonces() {
		return apply_filters('dog__sh_js_nonces', array());
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__sh_options', array(
			'script_dependencies' => array('vendor_scripts'),
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

	/***** AJAX *****/

	public static function ajax_handler() {
		$nonce_key = DOG__NC_NAME;
		$nonce = $_POST[$nonce_key];
		$method = $_POST['method'];
		$callable = explode('::', $method);
		$callable = count($callable) > 1 ? $callable : reset($callable);
		if (!check_ajax_referer(dog__string_to_key($method), $nonce_key, false)) {
			$response = dog__ajax_response_error(array('message' => dog__txt('Sistemul a întâmpinat o eroare. Cererea nu poate fi validată')));
		} else if (!is_callable($callable)) {
			$response = dog__ajax_response_error(array('message' => dog__txt('Sistemul a întâmpinat o eroare. Cererea nu poate fi procesată')));
		} else {
			$response = call_user_func($callable);
		}
		$response->$nonce_key = $nonce;
		wp_send_json($response);
	}

	/***** OPTIONS PAGE *****/

	public static function add_menu() {
	    add_menu_page(
	    	dog__txt('Opțiuni DOG'),
	    	dog__txt('Opțiuni DOG'),
	    	'administrator',
	    	self::PLUGIN_SLUG,
	    	null,
	    	'dashicons-layout'
	    );
	}

	/***** DEACTIVATION *****/

	public static function deactivate() {
		dog__switch_theme();
	}

	/***** REGISTER SESSION *****/

	public static function register_session() {
		if (!session_id()) {
        	session_start();
        }
	}

	/***** REGISTER TRANSLATION LABELS *****/

	public static function register_labels() {
		$labels_file = realpath(dirname(__FILE__)) . '/_pll_labels.php';
		if (is_file($labels_file) && function_exists('pll_register_string')) {
			require_once($labels_file);
		}
	}

}