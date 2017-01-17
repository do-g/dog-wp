<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Api_Facebook {

	const PLUGIN_SLUG = 'dog-api-facebook';
	const BASE_URL = 'https://graph.facebook.com/';
	const CSS_CLASS_EMPTY = 'facebook-empty';
	const CSS_CLASS_ERROR = 'facebook-error';
	protected $endpoint;
	private $_error;
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
		$version = self::config('fb_api_version') ? self::config('fb_api_version') . '/' : '';
		$url = self::BASE_URL . $version . ltrim($this->endpoint, '/');
		return dog__http_build_query($query_vars, $url);
	}

	protected function batch_call($data) {
		$options = array(
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => array(
				'access_token' => $this->access_token(),
				'include_headers' => 'false',
				'batch' => json_encode($data),
			),
		);
		$url = $this->get_url();
		return $this->call($url, $options);
	}

	protected function simple_call($data = array()) {
		$query_vars = array_merge(array(
			'access_token' => $this->access_token(),
		), $data);
		$url = $this->get_url($query_vars);
		return $this->call($url);
	}

	private function call($url, $options = array()) {
		$curl_options = array(
			CURLOPT_URL => $url,
		);
		$curl_options += $options;
		$response = dog__curl($curl_options, $err);
		if ($err) {
			return $this->set_error($err);
		}
		$response = json_decode($response);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return $this->set_error(json_last_error_msg());
		}
		if ($response->error) {
			return $this->set_error($response->error->message);
		}
		return $response;
	}

	private function access_token() {
		return self::config('fb_app_id') . '|' . self::config('fb_app_secret');
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
		return apply_filters('dog__af_options', array(
			'fb_app_id' => null,
			'fb_app_secret' => null,
			'fb_api_version' => null,
			'skip_cache_on_error' => true,
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

class Dog_Api_Facebook_Get extends Dog_Api_Facebook {

	public function get($params) {
		return $this->simple_call($params);
	}

}

class Dog_Api_Facebook_Item extends Dog_Api_Facebook_Get {

	public function __construct($id) {
		$this->endpoint = "/{$id}";
	}

}

class Dog_Api_Facebook_Page_Albums extends Dog_Api_Facebook_Get {

	public function __construct($page_id) {
		$this->endpoint = "/{$page_id}/albums";
	}

}

class Dog_Api_Facebook_Album_Photos extends Dog_Api_Facebook_Get {

	public function __construct($album_id) {
		$this->endpoint = "/{$album_id}/photos";
	}

}

class Dog_Api_Facebook_Batch extends Dog_Api_Facebook {

	private $batch = array();

	public function add($data) {
		$data = is_array($data) ? (object) $data : $data;
		array_push($this->batch, $data);
	}

	public function get() {
		return $this->batch ? $this->batch_call($this->batch) : null;
	}

}

class Dog_Api_Facebook_Get_Batch extends Dog_Api_Facebook_Batch {

	protected function prepare($endpoint, $params = array()) {
		return array(
			'method' => 'get',
			'relative_url' => $endpoint . ($params ? '?' . http_build_query($params) : ''),
		);
	}

	public function add($endpoint, $params = array()) {
		parent::add($this->prepare($endpoint, $params));
	}

}

class Dog_Api_Facebook_Item_Batch extends Dog_Api_Facebook_Get_Batch {

	public function add($item_id, $item_params = array()) {
		parent::add("/{$item_id}", $item_params);
	}

}

class Dog_Api_Facebook_Page_Album_Batch extends Dog_Api_Facebook_Get_Batch {

	public function add($page_id, $album_params = array()) {
		parent::add("/{$page_id}/albums", $album_params);
	}

}

class Dog_Api_Facebook_Album_Photos_Batch extends Dog_Api_Facebook_Get_Batch {

	public function add($album_id, $photo_params = array()) {
		parent::add("/{$album_id}/photos", $photo_params);
	}

}