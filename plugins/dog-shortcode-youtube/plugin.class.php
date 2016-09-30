<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Shortcode_Youtube {

	const PLUGIN_SLUG = 'dog-shortcode-youtube';
	const TAG = 'dog-youtube';
	const TYPE_PLAYLIST = 'playlist';
	const TYPE_VIDEO = 'video';
	private static $_initialized = false;
	private static $_config = array();
	private static $_dependencies = array();
	private static $_recording = false;
	private static $_items = array();
	private static $_playlists = array();
	private static $_videos = array();
	private static $_errors = array();

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
			add_shortcode(self::TAG, array(__CLASS__, 'parse'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	private static function load_config() {
		return apply_filters('dog__sy_options', array(
			'templates' => array(
				'playlist' => dog__sibling_path('youtube-playlist.tpl.php', __FILE__),
				'video' => dog__sibling_path('youtube-video.tpl.php', __FILE__),
				'playlist_video' => dog__sibling_path('youtube-playlist-video.tpl.php', __FILE__),
			),
			'css_class' => array(
				'playlist' => null,
				'video' => null,
				'playlist_video' => null,
			),
			'url' => array(
				'playlist' => Dog_Api_YouTube::get_playlist_url('${id}'),
				'video' => Dog_Api_YouTube::get_video_url('${id}'),
				'playlist_video' => Dog_Api_YouTube::get_video_url('${vid}', '${pid}'),
			),
			'gallery_rel' => 'dog-md-youtube-gallery',
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

	public static function parse($attrs) {
		$id = $attrs['id'];
		if (!$id) {
			return;
		}
		self::$_items[$id] = $attrs;
		switch ($attrs['type']) {
			case self::TYPE_PLAYLIST:
				self::$_playlists[$id] = $attrs;
				if (!self::$_recording) {
					return self::load_playlist($attrs);
				}
				break;
			default:
				self::$_videos[$id] = $attrs;
				if (!self::$_recording) {
					return self::load_video($attrs);
				}
				break;
		}
	}

	private static function load_playlist($attrs) {
		$service = new Dog_Api_YouTube_Playlists();
		$response = $service->get($attrs['id'], array(
			'part' => 'snippet',
		));
		if ($response) {
			if ($response->items) {
				foreach ($response->items as $item) {
					return dog__get_file_output(self::config('templates', 'playlist'), array(
						'item' => $item,
						'attrs' => $attrs,
						'config' => self::config(),
					));
				}
			} else {
				return dog__txt('Serviciul nu a returnat nicio lista');
			}
		} else {
			return dog__debug_message(dog__txt('Serviciul a returnat o eroare. Clipul nu poate fi încărcat'), $service->get_error());
		}
	}

	private static function load_video($attrs) {
		$service = new Dog_Api_YouTube_Videos();
		$response = $service->get($attrs['id'], array(
			'part' => 'snippet',
		));
		if ($response) {
			if ($response->items) {
				foreach ($response->items as $item) {
					return dog__get_file_output(self::config('templates', 'video'), array(
						'item' => $item,
						'attrs' => $attrs,
						'config' => self::config(),
					));
				}
			} else {
				return dog__txt('Serviciul nu a returnat niciun video');
			}
		} else {
			return dog__debug_message(dog__txt('Serviciul a returnat o eroare. Lista nu poate fi încărcată'), $service->get_error());
		}
	}

	public static function load() {
		$html_items = array();
		$playlist_ids = array_keys(self::$_playlists);
		$service = new Dog_Api_YouTube_Playlists();
		$response = $service->get(implode(',', $playlist_ids), array(
			'part' => 'snippet',
		));
		if ($response) {
			if ($response->items) {
				foreach ($response->items as $item) {
					$html_items[$item->id] = dog__get_file_output(self::config('templates', 'playlist'), array(
						'item' => $item,
						'attrs' => self::$_playlists[$item->id],
						'config' => self::config(),
					));
				}
			} else {
				array_push(self::$_errors, dog__txt('Serviciul nu a returnat nicio lista'));
			}
		} else {
			array_push(self::$_errors, dog__debug_message(dog__txt('Serviciul a returnat o eroare. Listele nu pot fi încărcate'), $service->get_error()));
		}
		$video_ids = array_keys(self::$_videos);
		$service = new Dog_Api_YouTube_Videos();
		$response = $service->get(implode(',', $video_ids), array(
			'part' => 'snippet',
		));
		if ($response) {
			if ($response->items) {
				foreach ($response->items as $item) {
					$html_items[$item->id] = dog__get_file_output(self::config('templates', 'video'), array(
						'item' => $item,
						'attrs' => self::$_videos[$item->id],
						'config' => self::config(),
					));
				}
			} else {
				array_push(self::$_errors, dog__txt('Serviciul nu a returnat niciun video'));
			}
		} else {
			array_push(self::$_errors, dog__debug_message(dog__txt('Serviciul a returnat o eroare. Clipurile nu pot fi încărcate'), $service->get_error()));
		}
		$sorted_html_items = array();
		foreach (self::$_items as $id => $attrs) {
			array_push($sorted_html_items, $html_items[$id]);
		}
		self::$_recording = false;
		return implode('', $sorted_html_items);
	}

	public static function record() {
		self::$_items = self::$_playlists = self::$_videos = self::$_errors = array();
		self::$_recording = true;
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