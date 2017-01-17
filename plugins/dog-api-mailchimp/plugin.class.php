<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Api_MailChimp {

	const PLUGIN_SLUG = 'dog-api-mailchimp';
	const BASE_URL = 'https://${data_center}.api.mailchimp.com/3.0/';
	const CSS_CLASS_EMPTY = 'mailchimp-empty';
	const CSS_CLASS_ERROR = 'mailchimp-error';
	protected $endpoint;
	private $_error;
	private $_response;
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
		self::$_initialized = true;
	}

	public static function setup() {
		if (self::check()) {

		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	/***** LOGIC *****/

	private function get_url($query_vars = array()) {
		$url = dog__replace_template_vars(self::BASE_URL, array(
			'data_center' => end(explode('-', self::config('api_key'))),
		)) . ltrim($this->endpoint, '/');
		return dog__http_build_query($query_vars, $url);
	}

	protected function post_call($data = array(), $verb = null) {
		$options = array(
			CURLOPT_POSTFIELDS => json_encode($data),
		);
		if ($verb) {
			$options[CURLOPT_CUSTOMREQUEST] = $verb;
		} else {
			$options[CURLOPT_POST] = 1;
		}
		$url = $this->get_url();
		return $this->call($url, $options);
	}

	protected function get_call($data = array()) {
		$url = $this->get_url($data);
		return $this->call($url);
	}

	private function call($url, $options = array()) {
		$curl_options = array(
			CURLOPT_URL => $url,
			CURLOPT_USERPWD => 'doghttpusr:' . self::config('api_key'),
		);
		$curl_options += $options;
		$this->_response = dog__curl($curl_options, $err, $status);
		if ($err) {
			return $this->set_error($err);
		}
		$this->_response = json_decode($this->_response);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return $this->set_error(json_last_error_msg());
		}
		if ($status != 200) {
			return $this->set_error($this->_response->detail);
		}
		return $this->_response;
	}

	protected function to_hash($hash_or_email) {
		return strpos($hash_or_email, '@') !== false ? md5(strtolower($hash_or_email)) : $hash_or_email;
	}

	public function get_response() {
		return $this->_response;
	}

	public function get_error() {
		return $this->_error;
	}

	protected function set_error($message) {
		$this->_error = $message;
	}

	public static function error_message($message) {
		if (self::config('skip_cache_on_error')) {
			dogx__skip_cache();
		}
		return '<div class="error ' . self::CSS_CLASS_ERROR . '">' . $message . '</div>';
	}

	public static function empty_message($message) {
		return '<div class="empty ' . self::CSS_CLASS_EMPTY . '">' . $message . '</div>';
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__am_options', array(
			'api_key' => null,
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

class Dog_Api_MailChimp_List_Members extends Dog_Api_MailChimp {

	const STATUS_SUBSCRIBED = 'subscribed';
	const STATUS_PENDING = 'pending';
	private $list_id;

	public function __construct($list_id) {
		$this->list_id = $list_id;
	}

	public function create($params) {
		$this->endpoint = "/lists/{$this->list_id}/members";
		return $this->post_call($params);
	}

	public function update($params, $hash = null) {
		$hash = $hash ? $hash : $this->to_hash($params['email_address']);
		$this->endpoint = "/lists/{$this->list_id}/members/{$hash}";
		return $this->post_call($params, 'PATCH');
	}

	public function upsert($params, $hash = null) {
		$hash = $hash ? $hash : $this->to_hash($params['email_address']);
		$this->endpoint = "/lists/{$this->list_id}/members/{$hash}";
		return $this->post_call($params, 'PUT');
	}

	public function delete($hash_or_email) {
		$this->endpoint = "/lists/{$this->list_id}/members/" . $this->to_hash($hash_or_email);
		return $this->post_call(null, 'DELETE');
	}

	public function get($hash_or_email = null, $params = array()) {
		$hash = $this->to_hash($hash_or_email);
		$this->endpoint = "/lists/{$this->list_id}/members" . ($hash ? "/{$hash}" : '');
		return $this->get_call($params);
	}

}