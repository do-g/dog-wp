<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Api_YouTube {

	const PLUGIN_SLUG = 'dog-api-youtube';
	const URL_VIDEO = 'https://www.youtube.com/watch?v=${id}';
	const URL_PLAYLIST = 'https://www.youtube.com/playlist?list=${id}';
	const URL_EMBED_VIDEO = 'https://www.youtube.com/embed/${id}';
	const URL_EMBED_PLAYLIST = 'https://www.youtube.com/embed/videoseries?list=${pid}';
	const URL_JS_API = 'https://www.youtube.com/iframe_api';
	private $base_url = 'https://www.googleapis.com/youtube/v3/';
	protected $url_fragment;
	private $error;
	private static $_initialized = false;
	private static $_dependencies = array();

	public function __construct() {}

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
		$config = self::config();
		if ($config['load_js_api']) {
			wp_enqueue_script('youtube_js_api', self::URL_JS_API, null, null, true);
			wp_enqueue_script('dog_ay_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('dog_sh_scripts', 'youtube_js_api'), null, true);
		}
	}

	private static function config() {
		return apply_filters('dog__ay_options', array(
			'server_api_key' => null,
			'load_js_api' => false,
		));
	}

	protected function get_url($params = array()) {
		$config = self::config();
		$params = array_merge(array('key' => $config['server_api_key']), $params);
		return $this->base_url . $this->url_fragment . '?' . http_build_query($params);
	}

	protected function request($params = array()) {
		$json = file_get_contents($this->get_url($params));
		$response = json_decode($json);
		if (!$response) {
			$this->set_error(json_last_error_msg());
			return false;
		}
		if ($response->error) {
			$this->set_error($response->error->message);
			return false;
		}
		return $response;
	}

	public function get_error() {
		return $this->error;
	}

	protected function set_error($message) {
		$this->error = $message;
	}

	public static function get_video_url($video_id, $playlist_id = null) {
		$url = dog__replace_template_vars(self::URL_VIDEO, array('id' => $video_id));
		$query = array();
		if ($playlist_id) {
			$query['list'] = $playlist_id;
		}
		return $url . ($query ? '?' . urldecode(http_build_query($query)) : '');
	}

	public static function get_playlist_url($playlist_id) {
		return dog__replace_template_vars(self::URL_PLAYLIST, array('id' => $playlist_id));
	}

	public static function get_video_embed_url($video_id, $playlist_id = null, $autoplay = false) {
		$url = dog__replace_template_vars(self::URL_EMBED_VIDEO, array('id' => $video_id));
		$query = array();
		if ($playlist_id) {
			$query['list'] = $playlist_id;
		}
		if ($autoplay) {
			$query['autoplay'] = 1;
		}
		return $url . ($query ? '?' . urldecode(http_build_query($query)) : '');
	}

	public static function get_playlist_embed_url($playlist_id, $autoplay = false) {
		$url = dog__replace_template_vars(self::URL_EMBED_PLAYLIST, array('id' => $playlist_id));
		$query = array();
		if ($autoplay) {
			$query['autoplay'] = 1;
		}
		return $url . ($query ? '?' . urldecode(http_build_query($query)) : '');
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

	protected $url_fragment = 'videos';

	public function __construct($api_key = null) {
		parent::__construct($api_key);
	}

	public function get($ids, $params) {
		$ids = is_array($ids) ? $ids : array($ids);
		return $this->request(array_merge(array(
			'id' => implode(',', $ids),
		), $params));
	}

}

class Dog_Api_YouTube_Playlists extends Dog_Api_YouTube {

	protected $url_fragment = 'playlists';

	public function __construct($api_key = null) {
		parent::__construct($api_key);
	}

	public function get($ids, $params) {
		$ids = is_array($ids) ? $ids : array($ids);
		return $this->request(array_merge(array(
			'id' => implode(',', $ids),
		), $params));
	}

}

class Dog_Api_YouTube_PlaylistItems extends Dog_Api_YouTube {

	protected $url_fragment = 'playlistItems';
	private   $playlist_id;

	public function __construct($playlist_id, $api_key = null) {
		$this->playlist_id = $playlist_id;
		parent::__construct($api_key);
	}

	public function get_all($params) {
		return $this->request(array_merge(array(
			'playlistId' => $this->playlist_id
		), $params));
	}

}