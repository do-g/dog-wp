<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Api_Json {

	const PLUGIN_SLUG = 'dog-api-json';
	const QUERY_VAR_OUTPUT_FORMAT = 'dog_output_format';
	const QUERY_VAR_METHOD = 'method';
	const QUERY_VAR_LIMIT = 'limit';
	const QUERY_VAR_KEY = 'key';
	const STATUS_OK = 1;
	const STATUS_ERR = 0;
	const METHOD_PREFIX = 'dogj__';
	private static $_initialized = false;
	private static $_config = array();
	private static $_dependencies = array();

	/***** INIT *****/

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		add_action('init', array(__CLASS__, 'setup'));
		add_action('plugins_loaded', array(__CLASS__, 'register_labels'));
		register_activation_hook(dirname(__FILE__) . '/plugin.php', array(__CLASS__, 'activate'));
		register_deactivation_hook(dirname(__FILE__) . '/plugin.php', array(__CLASS__, 'flush_rewrite_rules'));
		self::$_initialized = true;
	}

	public static function setup() {
		if (self::check()) {
			if (self::config('enable_api')) {
				self::add_rewrite_rules();
				if (!is_admin()) {
					add_filter('query_vars', array(__CLASS__, 'register_query_vars'));
					add_filter('dog__override_template', array(__CLASS__, 'override_template'));
				}
			}
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	private static function add_rewrite_rules() {
		add_rewrite_rule('json', 'index.php?' . self::QUERY_VAR_OUTPUT_FORMAT . '=json', 'top');
	}

	public static function flush_rewrite_rules() {
		delete_option('rewrite_rules');
	}

	public static function activate() {
		self::add_rewrite_rules();
		flush_rewrite_rules();
	}

	public static function register_query_vars($vars) {
	    array_push($vars, self::QUERY_VAR_OUTPUT_FORMAT);
		return $vars;
	}

	public static function override_template($template) {
		if (get_query_var(self::QUERY_VAR_OUTPUT_FORMAT) == 'json') {
	        $template = 'json';
	    }
		return $template;
	}

	public static function validate_access_token($token = null) {
		$token = $token ? $token : self::config('access_token');
		return $_GET[self::QUERY_VAR_KEY] == $token;
	}

	private static function out($status, $data) {
		$response = new stdClass();
		$response->status = $status;
		$response->data = $data;
		wp_send_json($response);
	}

	public static function send($data) {
		self::out(self::STATUS_OK, $data);
	}

	public static function send_error($message) {
		self::out(self::STATUS_ERR, $message);
	}

	public static function respond() {
		$endpoint = $_GET[self::QUERY_VAR_METHOD];
		if (!$endpoint) {
			self::send_error('Invalid service request');
		}
		$method = self::METHOD_PREFIX . $endpoint;
		if (!is_callable($method)) {
			self::send_error('Invalid service method');
		}
		$response = call_user_func($method);
		self::send($response);
	}

	public static function get_query_var($key) {
		switch ($key) {
			case self::QUERY_VAR_LIMIT:
				$value = absint($_GET[$key]);
				break;
			default:
				$value = null;
				break;
		}
		return $value;
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__aj_options', array(
			'access_token' => null,
			'enable_api' => null,
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