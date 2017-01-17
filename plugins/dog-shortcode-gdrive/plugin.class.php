<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Shortcode_GoogleDrive {

	const PLUGIN_SLUG = 'dog-shortcode-gdrive';
	const TAG = 'dog-gdrive';
	const ATTR_OBJECT = 'object';
	const ATTR_ID = 'id';
	const ATTR_MODE = 'mode';
	const ATTR_SHEET = 'sheet';
	const ATTR_START = 'start';
	const ATTR_LOOP = 'loop';
	const ATTR_DELAY = 'delay';
	const ATTR_RATIO = 'ratio';
	const ATTR_CLASS = 'class';
	const ATTR_WIDTH = 'width';
	const ATTR_HEIGHT = 'height';
	const OBJECT_VIEWER = 'viewer';
	const OBJECT_DOC = 'doc';
	const OBJECT_SHEET = 'sheet';
	const OBJECT_SLIDE = 'slide';
	const OBJECT_FORM = 'form';
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
		switch ($attrs[self::ATTR_OBJECT]) {
			case self::OBJECT_DOC:
			case self::OBJECT_SHEET:
			case self::OBJECT_SLIDE:
			case self::OBJECT_FORM:
			case self::OBJECT_VIEWER:
				return self::process_file($attrs, $content, $attrs[self::ATTR_OBJECT]);
			default:
				$public_message = dog__txt('Modulul a returnat o eroare. Conținutul nu poate fi încărcat.');
				if (!isset($attrs[self::ATTR_OBJECT])) {
					$debug_message = dog__txt('Lipsește parametrul "' . self::ATTR_OBJECT . '"');
				} else {
					$debug_message = dog__txt('Obiectul "' . $attrs[self::ATTR_OBJECT] . '" nu este recunoscut');
				}
				return self::wrap(Dog_Api_GoogleDrive::error_message(dog__debug_message($public_message, $debug_message)));
		}
	}

	private static function process_file($attrs, $content, $object = null) {
		$ids = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_ID, $attrs, $content, true));
		$mode = dog__value_or_default($attrs[self::ATTR_MODE], self::MODE_EMBED);
		$object = $object ? $object : self::OBJECT_VIEWER;
		if (!$ids) {
			$public_message = dog__txt('Modulul a returnat o eroare. Fișierul nu poate fi încărcat.');
			$debug_message = dog__txt('Pentru obiectul "' . $object . '" este necesar să se specifice parametrul "' . self::ATTR_ID . '"');
			return self::wrap(Dog_Api_GoogleDrive::error_message(dog__debug_message($public_message, $debug_message)), $object, $mode);
		}
		$template = self::config('templates', $object, $mode);
		if (!is_file($template) || !is_readable($template)) {
			$public_message = dog__txt('Modulul a returnat o eroare. Fișierul nu poate fi afișat.');
			$debug_message = dog__txt('Fișierul template "' . $template . '" pentru obiectul "' . $object . '" în modul "' . $mode . '" nu există sau nu poate fi citit');
			return self::wrap(Dog_Api_GoogleDrive::error_message(dog__debug_message($public_message, $debug_message)), $object, $mode);
		}
		self::filter_attrs($attrs, $content, $object);
		$output = '';
		foreach ($ids as $index => $id) {
			$item = new stdClass();
			$item->id = $id;
			$output .= dog__get_file_output($template, array(
				'index' => $index,
				'type' => $object,
				'mode' => $mode,
				'item' => $item,
				'attrs' => $attrs,
				'params' => $params,
				'config' => self::config(),
			));
		}
		return self::wrap($output);
	}

	private static function filter_attrs(&$attrs, $content, $object) {
		switch ($object) {
			case self::OBJECT_SHEET:
				if ($attrs[self::ATTR_SHEET]) {
					$attrs[self::ATTR_SHEET] = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_SHEET, $attrs));
				}
				break;
			case self::OBJECT_SLIDE:
				$attrs[self::ATTR_START] = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_START, $attrs));
				$attrs[self::ATTR_LOOP] = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_LOOP, $attrs));
				$attrs[self::ATTR_DELAY] = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_DELAY, $attrs));
				break;
			default:
				$attrs[self::ATTR_RATIO] = array_map('strtolower', dog__trim_explode(dog__get_shortcode_attr(self::ATTR_RATIO, $attrs)));
				$attrs[self::ATTR_CLASS] = array_map('sanitize_html_class', dog__trim_explode(dog__get_shortcode_attr(self::ATTR_CLASS, $attrs)));
				$attrs[self::ATTR_WIDTH] = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_WIDTH, $attrs));
				$attrs[self::ATTR_HEIGHT] = dog__trim_explode(dog__get_shortcode_attr(self::ATTR_HEIGHT, $attrs));
				break;
		}
	}

	private static function wrap($content, $type = null, $mode = null) {
		$template = self::config('templates', self::KEYWORD_WRAPPER);
		if (!is_file($template) || !is_readable($template)) {
			$public_message = dog__txt('Modulul a returnat o eroare. Resursele nu pot fi afișate.');
			$debug_message = dog__txt('Fișierul template "' . $template . '" nu există sau nu poate fi citit');
			return Dog_Api_GoogleDrive::error_message(dog__debug_message($public_message, $debug_message));
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

	public static function get_code($params) {
		return dog__get_shortcode_text(self::TAG, $params);
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__sg_options', array(
			'templates' => array(
				self::OBJECT_DOC => array(
					self::MODE_EMBED => dog__sibling_path('gdrive-viewer-embed.tpl.php', __FILE__),
				),
				self::OBJECT_SHEET => array(
					self::MODE_EMBED => dog__sibling_path('gdrive-viewer-embed.tpl.php', __FILE__),
				),
				self::OBJECT_SLIDE => array(
					self::MODE_EMBED => dog__sibling_path('gdrive-viewer-embed.tpl.php', __FILE__),
				),
				self::OBJECT_FORM => array(
					self::MODE_EMBED => dog__sibling_path('gdrive-viewer-embed.tpl.php', __FILE__),
				),
				self::OBJECT_VIEWER => array(
					self::MODE_EMBED => dog__sibling_path('gdrive-viewer-embed.tpl.php', __FILE__),
				),
				self::KEYWORD_WRAPPER => dog__sibling_path('gdrive-wrapper.tpl.php', __FILE__),
			),
			'css_class' => array(
				self::OBJECT_DOC => null,
				self::OBJECT_SHEET => null,
				self::OBJECT_SLIDE => null,
				self::OBJECT_FORM => null,
				self::OBJECT_VIEWER => null,
				self::KEYWORD_WRAPPER => null,
			),
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