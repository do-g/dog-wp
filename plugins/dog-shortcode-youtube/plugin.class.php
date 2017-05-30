<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Shortcode_YouTube {

	const PLUGIN_SLUG = 'dog-shortcode-youtube';
	const TAG = 'dog-youtube';
	const ATTR_OBJECT = 'object';
	const ATTR_CHANNEL = 'channel';
	const ATTR_PLAYLIST = 'playlist';
	const ATTR_ID = 'id';
	const ATTR_IGNORE = 'ignore';
	const ATTR_MODE = 'mode';
	const OBJECT_PLAYLIST = 'playlist';
	const OBJECT_VIDEO = 'video';
	const MODE_LIST = 'list';
	const MODE_EMBED = 'embed';
	const KEYWORD_WRAPPER = 'wrapper';
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
			add_shortcode(self::TAG, array(__CLASS__, 'parse'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	/***** LOGIC *****/

	public static function parse($attrs, $content = null) {
		if (!is_array($attrs) || !isset($attrs[self::ATTR_OBJECT])) {
			$error_message = dog__txt('Missing parameter "${param}"', array('param' => self::ATTR_OBJECT));
			return self::show_message(null, null, $error_message);
		}
		switch ($attrs[self::ATTR_OBJECT]) {
			case self::OBJECT_PLAYLIST:
				return self::process_playlist($attrs, $content);
			case self::OBJECT_VIDEO:
				return self::process_video($attrs, $content);
			default:
				$error_message = dog__txt('Unknown object "${object}"', array('object' => $attrs[self::ATTR_OBJECT]));
				return self::show_message(null, null, $error_message);
		}
	}

	private static function process_playlist($attrs, $content) {
		$object = self::OBJECT_PLAYLIST;
		$mode = dog__value_or_default($attrs[self::ATTR_MODE], self::MODE_LIST);
		$channel = trim(dog__get_shortcode_attr(self::ATTR_CHANNEL, $attrs, $content, true), ',');
		$id = trim(dog__get_shortcode_attr(self::ATTR_ID, $attrs, $content, true), ',');
		$ignore = $attrs[self::ATTR_IGNORE] ? dog__trim_explode(dog__get_shortcode_attr(self::ATTR_IGNORE, $attrs, $content, true)) : array();
		$params = array(
			'part' => 'snippet,player',
		);
		if (!$channel && !$id) {
			$error_message = dog__txt('Object "${object}" requires either "${channel}" or "${id}" parameters to be specified', array('object' => $object, 'channel' => self::ATTR_CHANNEL, 'id' => self::ATTR_ID));
			return self::show_message($object, $mode, $error_message);
		} else if ($id) {
			$params['id'] = str_replace(' ', '', $id);
		} else {
			$params['channelId'] = str_replace(' ', '', $channel);
			$params['maxResults'] = 50;
		}
		$service = new Dog_Api_YouTube_Playlists();
		$response = $service->get($params);
		if ($response) {
			if ($response->items) {
				$output = '';
				$template = self::config('templates', $object, $mode);
				if (!is_file($template) || !is_readable($template)) {
					$error_message = dog__txt('Template "${template}" for object "${object}" in mode "${mode}" not found or unreadable', array('template' => $template, 'object' => $object, 'mode' => $mode));
					return self::show_message($object, $mode, $error_message);
				}
				foreach ($response->items as $playlist) {
					if (in_array($playlist->id, $ignore)) {
						continue;
					}
					$playlist->thumbnail = self::best_image_size(self::config('thumb_width', $object), $playlist->snippet->thumbnails);
					$output .= dog__get_file_output($template, array(
						'type' => $object,
						'mode' => $mode,
						'item' => $playlist,
						'attrs' => $attrs,
						'config' => self::config(),
					));
				}
				return $output ? self::wrap($output, $object, $mode) : self::show_message($object, $mode);
			} else {
				return self::show_message($object, $mode);
			}
		} else {
			$error_message = $service->get_error();
			$error_message = $error_message ? $error_message : dog__txt('Unknown error');
			return self::show_message($object, $mode, $error_message);
		}
	}

	private static function process_video($attrs, $content) {
		$object = self::OBJECT_VIDEO;
		$mode = dog__value_or_default($attrs[self::ATTR_MODE], self::MODE_EMBED);
		$playlist = trim(dog__get_shortcode_attr(self::ATTR_PLAYLIST, $attrs, $content, true), ',');
		$id = trim(dog__get_shortcode_attr(self::ATTR_ID, $attrs, $content, true), ',');
		$ignore = $attrs[self::ATTR_IGNORE] ? dog__trim_explode(dog__get_shortcode_attr(self::ATTR_IGNORE, $attrs, $content, true)) : array();
		$params = array(
			'part' => 'snippet',
		);
		if (!$playlist && !$id) {
			$error_message = dog__txt('Object "${object}" requires either "${playlist}" or "${id}" parameters to be specified', array('object' => $object, 'playlist' => self::ATTR_PLAYLIST, 'id' => self::ATTR_ID));
			return self::show_message($object, $mode, $error_message);
		} else if ($id) {
			$service = new Dog_Api_YouTube_Videos();
			$params['id'] = str_replace(' ', '', $id);
			$params['part'] .= ',player';
		} else {
			$service = new Dog_Api_Youtube_PlaylistItems();
			$params['playlistId'] = str_replace(' ', '', $playlist);
			$params['maxResults'] = 50;
			$mode = self::MODE_LIST;
		}
		$response = $service->get($params);
		if ($response) {
			if ($response->items) {
				$output = '';
				$template = self::config('templates', $object, $mode);
				if (!is_file($template) || !is_readable($template)) {
					$error_message = dog__txt('Template "${template}" for object "${object}" in mode "${mode}" not found or unreadable', array('template' => $template, 'object' => $object, 'mode' => $mode));
					return self::show_message($object, $mode, $error_message);
				}
				foreach ($response->items as $video) {
					$video_id = $video->snippet->resourceId->videoId ? $video->snippet->resourceId->videoId : $video->id;
					if (in_array($video_id, $ignore)) {
						continue;
					}
					$video->thumbnail = self::best_image_size(self::config('thumb_width', $object), $video->snippet->thumbnails);
					$output .= dog__get_file_output($template, array(
						'type' => $object,
						'mode' => $mode,
						'item' => $video,
						'attrs' => $attrs,
						'config' => self::config(),
					));
				}
				return $output ? self::wrap($output, $object, $mode) : self::show_message($object, $mode);
			} else {
				return self::show_message($object, $mode);
			}
		} else {
			$error_message = $service->get_error();
			$error_message = $error_message ? $error_message : dog__txt('Unknown error');
			return self::show_message($object, $mode, $error_message);
		}
	}

	private static function wrap($content, $type = null, $mode = null) {
		$template = self::config('templates', self::KEYWORD_WRAPPER);
		if (!is_file($template) || !is_readable($template)) {
			$error_message = dog__txt('Template "${template}" for object "${object}" not found or unreadable', array('template' => $template, 'object' => self::KEYWORD_WRAPPER));
			return self::get_message($error_message);
		}
		return dog__get_file_output($template, array(
			'content' => $content,
			'type' => self::KEYWORD_WRAPPER,
			'obj_type' => $type,
			'mode' => $mode,
			'attrs' => $attrs,
			'config' => self::config(),
		));
	}

	private static function best_image_size($target_size, $size_list) {
		$sizes = array();
		foreach ($size_list as $key => $item) {
			$sizes[$key] = $item->width;
		}
		$key = dog__closest($target_size, $sizes, true);
		return $size_list->$key->url;
	}

	/***** UTILITIES *****/

	public static function get_code($params) {
		return dog__get_shortcode_text(self::TAG, $params);
	}

	public static function debug($public_message, $debug_message) {
		return dog__debug_message($public_message, $debug_message, self::config('debug'));
	}

	public static function get_message($error_message = null, $empty_message = null, $error_public_message = null) {
		if ($error_message) {
			$message = dog__value_or_default($error_public_message, dog__txt('Modulul a returnat o eroare. Unele resurse nu pot fi afișate.'));
			if (is_array($error_message)) {
				$error_message = implode('. ', $error_message);
			}
			$message = Dog_Api_YouTube::error_message(self::debug($message, $error_message));
		} else {
			$message = dog__value_or_default($empty_message, dog__txt('Nu am găsit resurse.'));
			$message = Dog_Api_YouTube::empty_message($message);
		}
		return $message;
	}

	public static function show_message($object, $mode, $error_message = null, $empty_message = null, $error_public_message = null) {
		$message = self::get_message($error_message, $empty_message, $error_public_message);
		return self::wrap($message, $object, $mode);
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__sy_options', array(
			'templates' => array(
				self::OBJECT_PLAYLIST => array(
					self::MODE_LIST => dog__sibling_path('playlist-list.tpl.php', __FILE__),
					self::MODE_EMBED => dog__sibling_path('playlist-embed.tpl.php', __FILE__),
				),
				self::OBJECT_VIDEO => array(
					self::MODE_LIST => dog__sibling_path('video-list.tpl.php', __FILE__),
					self::MODE_EMBED => dog__sibling_path('video-embed.tpl.php', __FILE__),
				),
				self::KEYWORD_WRAPPER => dog__sibling_path('wrapper.tpl.php', __FILE__),
			),
			'css_class' => array(
				self::OBJECT_PLAYLIST => null,
				self::OBJECT_VIDEO => null,
				self::KEYWORD_WRAPPER => null,
			),
			'url' => array(
				self::OBJECT_PLAYLIST => Dog_Api_YouTube::get_playlist_url('${id}'),
				self::OBJECT_VIDEO => Dog_Api_YouTube::get_video_url('${id}', '${pid}'),
			),
			'thumb_width' => array(
				self::OBJECT_PLAYLIST => 600,
				self::OBJECT_VIDEO => 600,
			),
			'gallery_rel' => 'dog-md-youtube-gallery',
			'debug' => false,
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