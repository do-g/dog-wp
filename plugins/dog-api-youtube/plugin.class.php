<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Api_YouTube {

	const PLUGIN_SLUG = 'dog-api-youtube';
	const URL_VIDEO = 'https://www.youtube.com/watch?v=${id}';
	const URL_PLAYLIST = 'https://www.youtube.com/playlist?list=${id}';
	const URL_EMBED_VIDEO = 'https://www.youtube.com/embed/${id}';
	const URL_EMBED_PLAYLIST = 'https://www.youtube.com/embed/videoseries?list=${pid}';
	const URL_JS_API = 'https://www.youtube.com/iframe_api';
	const BASE_URL = 'https://www.googleapis.com/youtube/v3/';
	const CSS_CLASS_EMPTY = 'youtube-empty';
	const CSS_CLASS_ERROR = 'youtube-error';
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
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	public static function enqueue_assets() {
		if (self::config('load_js_api')) {
			wp_enqueue_script('youtube_js_api', self::URL_JS_API, null, null, true);
			wp_enqueue_script('dog_ay_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('dog_sh_scripts', 'youtube_js_api'), null, true);
		}
	}

	/***** LOGIC *****/

	private function get_url($query_vars = array()) {
		$query_vars = array_merge(array(
			'key' => self::config('server_api_key'),
		), $query_vars);
		$url = self::BASE_URL . ltrim($this->endpoint, '/');
		return dog__http_build_query($query_vars, $url);
	}

	protected function call($params = array()) {
		$json = file_get_contents($this->get_url($params));
		$response = json_decode($json);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return $this->set_error(json_last_error_msg());
		}
		if ($response->error) {
			return $this->set_error($response->error->message);
		}
		return $response;
	}

	public function get($params) {
		return $this->call($params);
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

	public static function get_video_url($video_id, $playlist_id = null) {
		$url = dog__replace_template_vars(self::URL_VIDEO, array('id' => $video_id));
		$query_vars = array();
		if ($playlist_id) {
			$query_vars['list'] = $playlist_id;
		}
		return dog__http_build_query($query_vars, $url);
	}

	public static function get_playlist_url($playlist_id) {
		return dog__replace_template_vars(self::URL_PLAYLIST, array('id' => $playlist_id));
	}

	public static function get_video_embed_url($video_id, $playlist_id = null, $autoplay = false) {
		$url = dog__replace_template_vars(self::URL_EMBED_VIDEO, array('id' => $video_id));
		$query_vars = array();
		if ($playlist_id) {
			$query_vars['list'] = $playlist_id;
		}
		if ($autoplay) {
			$query_vars['autoplay'] = 1;
		}
		return dog__http_build_query($query_vars, $url);
	}

	public static function get_playlist_embed_url($playlist_id, $autoplay = false) {
		$url = dog__replace_template_vars(self::URL_EMBED_PLAYLIST, array('id' => $playlist_id));
		$query_vars = array();
		if ($autoplay) {
			$query_vars['autoplay'] = 1;
		}
		return dog__http_build_query($query_vars, $url);
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__ay_options', array(
			'server_api_key' => null,
			'load_js_api' => false,
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

class Dog_Api_YouTube_Videos extends Dog_Api_YouTube {

	protected $endpoint = 'videos';

}

class Dog_Api_YouTube_Playlists extends Dog_Api_YouTube {

	protected $endpoint = 'playlists';

}

class Dog_Api_YouTube_PlaylistItems extends Dog_Api_YouTube {

	protected $endpoint = 'playlistItems';

}