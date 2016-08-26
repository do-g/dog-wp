<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Form {

	const PLUGIN_SLUG = 'dog-form';
	const POST_VALUE_TYPE_TEXT = 'text';
	const POST_VALUE_TYPE_ARRAY_TEXT = 'array_text';
	const POST_VALUE_TYPE_EMAIL = 'email';
	const POST_VALUE_TYPE_TEXTAREA = 'textarea';
	const POST_VALUE_TYPE_NATURAL = 'natural';
	const POST_VALUE_TYPE_ARRAY_NATURAL = 'array_natural';
	const POST_VALUE_TYPE_INTEGER = 'integer';
	const POST_VALUE_TYPE_ARRAY_INTEGER = 'array_integer';
	const ERROR_TYPE_REQUIRED = 'required';
	const ERROR_TYPE_EMAIL = 'email';
	const ERROR_TYPE_REGEX = 'regex';

	private static $_initialized = false;
	private static $_dependencies = array();
	private static $_post_data = array();
	private static $_form_errors = array();

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

	public static function whitelist_keys($allowed) {
		$default = array('_wp_http_referer', DOG__NC_NAME, DOG__HP_TIMER_NAME, DOG__HP_JAR_NAME);
		$allowed = array_merge($allowed, $default);
		$_POST = array_intersect_key($_POST, array_flip($allowed));
	}

	public static function sanitize_post_data($key_rules = array()) {
		if ($_POST) {
			foreach ($_POST as $key => $value) {
				self::$_post_data[$key] = self::get_safe_post_value($key, $key_rules[$key]);
			}
		}
	}

	private static function get_safe_post_value($key, $type = null) {
		$value = isset($_POST[$key]) ? $_POST[$key] : null;
		if ($value) {
			$value = is_array($value) ? array_map('trim', $value) : trim($value);
			switch ($type) {
				case self::POST_VALUE_TYPE_EMAIL:
					$value = sanitize_email($value);
					break;
				case self::POST_VALUE_TYPE_TEXTAREA:
					$value = wp_filter_nohtml_kses($value);
					break;
				case self::POST_VALUE_TYPE_NATURAL:
					$value = absint($value);
					break;
				case self::POST_VALUE_TYPE_ARRAY_NATURAL:
					$value = array_map('absint', $value);
					break;
				case self::POST_VALUE_TYPE_INTEGER:
					$value = intval($value);
					break;
				case self::POST_VALUE_TYPE_ARRAY_INTEGER:
					$value = array_map('intval', $value);
					break;
				case self::POST_VALUE_TYPE_ARRAY_TEXT:
					$value = array_map('sanitize_text_field', $value);
					break;
				default:
					$value = sanitize_text_field($value);
					break;
			}
			$value = is_array($value) ? stripslashes_deep($value) : stripslashes($value);
		}
		return $value;
	}

	public static function get_post_value($key) {
		return self::$_post_data[$key];
	}

	public static function get_post_value_or_default($key, $default, $use_default_if_empty = false) {
		$value = self::get_post_value($key);
		$condition = $use_default_if_empty ? $value : isset($value);
		return $condition ? $value : $default;
	}

	public static function get_post_data() {
		return self::$_post_data;
	}

	public static function validate_nonce($nonce_action, $redirect_to = null) {
		$nonce = self::get_post_value(DOG__NC_NAME);
		if (!$nonce || !wp_verify_nonce($nonce, dog__string_to_key($nonce_action))) {
			if ($redirect_to) {
				dog__set_flash_error('form', dog__txt('Eroare la validarea formularului'));
				dog__redirect($redirect_to);
			} else {
				self::set_form_error(dog__txt('Eroare la validarea formularului'));
			}
		}
	}

	public static function validate_honeypot() {
		if (!DOG__HONEYPOT_ENABLED) {
			return true;
		}
		if (self::get_post_value(DOG__HP_JAR_NAME)) {
			self::set_form_error(dog__txt('Execuție suspectă sau neautorizată [1]'));
			return false;
		}
		$timer = self::get_post_value(DOG__HP_TIMER_NAME);
		if (!$timer || microtime(true) - $timer < DOG__HP_TIMER_SECONDS) {
			self::set_form_error(dog__txt('Execuție suspectă sau neautorizată [2]'));
			return false;
		}
	}

	public static function validate_required_fields($field_list, $error_messages = null) {
		$field_list = is_array($field_list) ? $field_list : array($field_list);
		$error_messages = is_array($error_messages) ? $error_messages : array($error_messages);
		foreach ($field_list as $n => $field_name) {
			if (self::get_post_value($field_name) == '') {
				$error_message = $error_messages[$n] ? $error_messages[$n] : dog__txt('Acest câmp este obligatoriu');
				self::set_field_error($field_name, $error_message, self::ERROR_TYPE_REQUIRED);
			}
		}
	}

	public static function validate_email_fields($field_list, $error_messages = null) {
		$field_list = is_array($field_list) ? $field_list : array($field_list);
		$error_messages = is_array($error_messages) ? $error_messages : array($error_messages);
		foreach ($field_list as $n => $field_name) {
			$field_value = self::get_post_value($field_name);
			if ($field_value && self::field_is_valid($field_name) && !is_email($field_value)) {
				$error_message = $error_messages[$n] ? $error_messages[$n] : dog__txt('Adresa email este invalidă');
				self::set_field_error($field_name, $error_message, self::ERROR_TYPE_EMAIL);
			}
		}
	}

	public static function validate_regex_fields($field_list, $regex_patterns, $error_messages = null) {
		$field_list = is_array($field_list) ? $field_list : array($field_list);
		$regex_patterns = is_array($regex_patterns) ? $regex_patterns : array($regex_patterns);
		$error_messages = is_array($error_messages) ? $error_messages : array($error_messages);
		foreach ($field_list as $n => $field_name) {
			$field_value = self::get_post_value($field_name);
			if ($field_value && self::field_is_valid($field_name)) {
				$error_message = $error_messages[$n] ? $error_messages[$n] : dog__txt('Valoarea introdusă nu respectă formatul acceptat');
				$regex_pattern = $regex_patterns[$n];
				if (!preg_match($regex_pattern, $field_value)) {
					self::set_field_error($field_name, $error_message, self::ERROR_TYPE_REGEX);
				}
			}
		}
	}

	public static function field_is_valid($field_name) {
		return !self::get_field_errors($field_name);
	}

	public static function set_field_error($field_name, $message, $type = 'generic') {
		self::$_form_errors[$field_name][$type] = $message;
	}

	public static function get_field_errors($field_name, $type = null) {
		return $type ? self::$_form_errors[$field_name][$type] : self::$_form_errors[$field_name];
	}

	public static function form_is_valid() {
		return !self::get_all_errors();
	}

	public static function set_form_error($message, $type = 'generic') {
		self::set_field_error('form', $message, $type);
	}

	public static function get_form_errors($type = null) {
		return self::get_field_errors('form', $type);
	}

	public static function render_form_errors() {
		include dog__plugin_path('templates/form-errors.php', self::PLUGIN_SLUG);
	}

	public static function get_all_errors() {
		return self::$_form_errors;
	}

	public static function render_form_field($field_info) {
		include dog__plugin_path('templates/form-field.php', self::PLUGIN_SLUG);
	}

	public static function render_nonce_field($action) {
		wp_nonce_field(dog__string_to_key($action), DOG__NC_NAME);
	}

	public static function render_honeypot_field() {
		if (DOG__HONEYPOT_ENABLED) {
			include dog__plugin_path('templates/honeypot.php', self::PLUGIN_SLUG);
		}
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