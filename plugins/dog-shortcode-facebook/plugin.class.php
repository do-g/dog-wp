<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Shortcode_Facebook {

	const PLUGIN_SLUG = 'dog-shortcode-facebook';
	const TAG = 'dog-facebook';
	const ATTR_OBJECT = 'object';
	const ATTR_PAGE = 'page';
	const ATTR_ALBUM = 'album';
	const ATTR_ID = 'id';
	const ATTR_IGNORE = 'ignore';
	const ATTR_MODE = 'mode';
	const OBJECT_ALBUM = 'album';
	const OBJECT_PHOTO = 'photo';
	const MODE_LIST = 'list';
	const KEYWORD_WRAPPER = 'wrapper';
	const RESULTS_LIMIT = 200;
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
		switch ($attrs[self::ATTR_OBJECT]) {
			case self::OBJECT_ALBUM:
				return self::process_album($attrs, $content);
			case self::OBJECT_PHOTO:
				return self::process_photo($attrs, $content);
			default:
				$public_message = dog__txt('Modulul a returnat o eroare. Conținutul nu poate fi încărcat.');
				if (!isset($attrs[self::ATTR_OBJECT])) {
					$debug_message = dog__txt('Lipsește parametrul "' . self::ATTR_OBJECT . '"');
				} else {
					$debug_message = dog__txt('Obiectul "' . $attrs[self::ATTR_OBJECT] . '" nu este recunoscut');
				}
				return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message($public_message, $debug_message)));
		}
	}

	private static function process_album($attrs, $content) {
		$object = self::OBJECT_ALBUM;
		$mode = dog__value_or_default($attrs[self::ATTR_MODE], self::MODE_LIST);
		$page = dog__get_shortcode_attr(self::ATTR_PAGE, $attrs, $content, true);
		$id = dog__get_shortcode_attr(self::ATTR_ID, $attrs, $content, true);
		$ignore = $attrs[self::ATTR_IGNORE] ? dog__trim_explode(dog__get_shortcode_attr(self::ATTR_IGNORE, $attrs, $content, true)) : array();
		$params = array(
			'fields' => 'link,cover_photo',
		);
		$output = '';
		$album_links = array();
		if (!$page && !$id) {
			$public_message = dog__txt('Modulul a returnat o eroare. Albumele nu pot fi încărcate.');
			$debug_message = dog__txt('Pentru obiectul "' . $object . '" este necesar să se specifice unul din parametrii "' . self::ATTR_PAGE . '" sau "' . self::ATTR_ID . '"');
			return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message($public_message, $debug_message)), $object, $mode);
		} else if ($id) {
			$service = new Dog_Api_Facebook_Item_Batch();
			$item_ids = dog__trim_explode($id);
		} else {
			$service = new Dog_Api_Facebook_Page_Album_Batch();
			$item_ids = dog__trim_explode($page);
			$params['limit'] = self::RESULTS_LIMIT;
		}
		foreach ($item_ids as $item_id) {
			$service->add($item_id, $params);
		}
		$batch_response = $service->get();
		if ($batch_response) {
			$service2 = new Dog_Api_Facebook_Item_Batch();
			$template = self::config('templates', $object, $mode);
			if (!is_file($template) || !is_readable($template)) {
				$public_message = dog__txt('Modulul a returnat o eroare. Albumele nu pot fi afișate.');
				$debug_message = dog__txt('Fișierul template "' . $template . '" pentru obiectul "' . $object . '" în modul "' . $mode . '" nu există sau nu poate fi citit');
				return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message($public_message, $debug_message)), $object, $mode);
			}
			foreach ($batch_response as $raw_response) {
				$response = json_decode($raw_response->body);
				if ($response->error) {
					$output .= Dog_Api_Facebook::error_message(dog__debug_message(dog__txt('Serviciul a returnat o eroare. Unele albume nu pot fi încărcate.'), $response->error->message));
					continue;
				}
				// albums requested by id
				if ($response->id) {
					$data = array($response);
				// albums requested by page
				} else {
					$data = $response->data;
				}
				if (!$data) {
					$output .= Dog_Api_Facebook::empty_message(dog__txt('Nu am găsit albume foto'));
					continue;
				}
				foreach ($data as $album) {
					if (in_array($album->id, $ignore)) {
						continue;
					}
					if (!$album->cover_photo) {
						continue;
					}
					$album_links[$album->id] = $album->link;
					$service2->add($album->cover_photo->id, array(
						'fields' => 'album,images',
					));
				}
			}
			if (!$album_links) {
				return self::wrap($output, $object, $mode);
			}
			$batch_response2 = $service2->get();
			if ($batch_response2) {
				foreach ($batch_response2 as $raw_response2) {
					$response2 = json_decode($raw_response2->body);
					if ($response2->error) {
						$output .= Dog_Api_Facebook::error_message(dog__debug_message(dog__txt('Serviciul a returnat o eroare. Unele albume nu pot fi încărcate.'), $response2->error->message));
						continue;
					}
					$response2->album->link = $album_links[$response2->album->id];
					$response2->album->thumbnail = self::best_image_size(self::config('thumb_width', $object), $response2->images);
					$output .= dog__get_file_output($template, array(
						'type' => $object,
						'mode' => $mode,
						'item' => $response2,
						'attrs' => $attrs,
						'config' => self::config(),
					));
				}
				return self::wrap($output, $object, $mode);
			} else {
				$error = $service2->get_error();
				$error = $error ? $error : dog__txt('Eroare necunoscută');
				return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message(dog__txt('Serviciul a returnat o eroare. Albumele nu pot fi încărcate.'), $error)), $object, $mode);
			}
		} else {
			$error = $service->get_error();
			$error = $error ? $error : dog__txt('Eroare necunoscută');
			return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message(dog__txt('Serviciul a returnat o eroare. Albumele nu pot fi încărcate.'), $error)), $object, $mode);
		}
	}

	private static function process_photo($attrs, $content) {
		$object = self::OBJECT_PHOTO;
		$mode = dog__value_or_default($attrs[self::ATTR_MODE], self::MODE_LIST);
		$album = dog__get_shortcode_attr(self::ATTR_ALBUM, $attrs, $content, true);
		$output = '';
		if (!$album) {
			$public_message = dog__txt('Modulul a returnat o eroare. Imaginile nu pot fi încărcate.');
			$debug_message = dog__txt('Pentru obiectul "' . $object . '" este necesar să se specifice parametrul "' . self::ATTR_ALBUM . '"');
			return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message($public_message, $debug_message)), $object, $mode);
		}
		$service = new Dog_Api_Facebook_Album_Photos_Batch();
		$item_ids = dog__trim_explode($album);
		foreach ($item_ids as $item_id) {
			$service->add($item_id, array(
				'fields' => 'name,link,images',
				'limit' => self::RESULTS_LIMIT,
			));
		}
		$batch_response = $service->get();
		if ($batch_response) {
			$output = '';
			$template = self::config('templates', $object, $mode);
			if (!is_file($template) || !is_readable($template)) {
				$public_message = dog__txt('Modulul a returnat o eroare. Imaginile nu pot fi afișate.');
				$debug_message = dog__txt('Fișierul template "' . $template . '" pentru obiectul "' . $object . '" în modul "' . $mode . '" nu există sau nu poate fi citit');
				return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message($public_message, $debug_message)), $object, $mode);
			}
			foreach ($batch_response as $raw_response) {
				$response = json_decode($raw_response->body);
				if ($response->error) {
					$output .= Dog_Api_Facebook::error_message(dog__debug_message(dog__txt('Serviciul a returnat o eroare. Unele imagini nu pot fi încărcate.'), $response->error->message));
					continue;
				} else if (!$response->data) {
					$output .= Dog_Api_Facebook::empty_message(dog__txt('Nu am găsit imagini'));
					continue;
				}
				foreach ($response->data as $photo) {
					$photo->thumbnail = self::best_image_size(self::config('thumb_width', $object), $photo->images);
					$output .= dog__get_file_output($template, array(
						'type' => $object,
						'item' => $photo,
						'attrs' => $attrs,
						'config' => self::config(),
					));
				}
			}
			return self::wrap($output, $object, $mode);
		} else {
			$error = $service->get_error();
			$error = $error ? $error : dog__txt('Eroare necunoscută');
			return self::wrap(Dog_Api_Facebook::error_message(dog__debug_message(dog__txt('Serviciul a returnat o eroare. Imaginile nu pot fi încărcate.'), $error)), $object, $mode);
		}
	}

	private static function wrap($content, $type = null, $mode = null) {
		$template = self::config('templates', self::KEYWORD_WRAPPER);
		if (!is_file($template) || !is_readable($template)) {
			$public_message = dog__txt('Modulul a returnat o eroare. Resursele nu pot fi afișate.');
			$debug_message = dog__txt('Fișierul template "' . $template . '" pentru obiectul "' . self::KEYWORD_WRAPPER . '" nu există sau nu poate fi citit');
			return Dog_Api_Facebook::error_message(dog__debug_message($public_message, $debug_message));
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
		foreach ($size_list as $n => $item) {
			$sizes[$n] = $item->width;
		}
		$key = dog__closest($target_size, $sizes, true);
		return $size_list[$key]->source;
	}

	public static function get_code($params) {
		return dog__get_shortcode_text(self::TAG, $params);
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__sf_options', array(
			'templates' => array(
				self::OBJECT_ALBUM => array(
					self::MODE_LIST => dog__sibling_path('facebook-album-list.tpl.php', __FILE__),
				),
				self::OBJECT_PHOTO => array(
					self::MODE_LIST => dog__sibling_path('facebook-photo-list.tpl.php', __FILE__),
				),
				self::KEYWORD_WRAPPER => dog__sibling_path('facebook-wrapper.tpl.php', __FILE__),
			),
			'css_class' => array(
				self::OBJECT_ALBUM => null,
				self::OBJECT_PHOTO => null,
				self::KEYWORD_WRAPPER => null,
			),
			'url' => array(
				self::OBJECT_ALBUM => null,
				self::OBJECT_PHOTO => null,
			),
			'thumb_width' => array(
				self::OBJECT_ALBUM => 600,
				self::OBJECT_PHOTO => 600,
			),
			'gallery_rel' => 'dog-md-image-gallery[facebook]',
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