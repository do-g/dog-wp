<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Cache_Manager {

	const PLUGIN_SLUG = 'dog-cache-manager';
	const FRAGMENT_CACHE_KEY_PREFIX = 'dog-cm-';
	const FRAGMENT_CACHE_KEY_MAX_LENGTH = 40;
	const SALT_KEY = 'dog-cm-salt';
	const SALT_LENGTH = 4;
	const SALT_EXPIRY = 2592000;
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
			add_action('admin_post_dog_save_cm_options', array(__CLASS__, 'save_options'));
			add_action('admin_menu', array(__CLASS__, 'add_menu'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__cm_options', array(
			'fragment_cache_expiry' => 60 * 60 * 24,
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

	/***** LOGIC *****/

	private static function get_salt() {
		$salt = get_transient(self::SALT_KEY);
		if (!$salt) {
			$salt = self::update_salt();
		}
		return $salt;
	}

	private static function update_salt() {
		$salt = substr(strrev(uniqid(null, true)), 0, self::SALT_LENGTH);
		set_transient(self::SALT_KEY, $salt, self::SALT_EXPIRY);
		return $salt;
	}

	public static function fragment_key($key) {
		return substr(self::FRAGMENT_CACHE_KEY_PREFIX . self::get_salt() . $key, 0, self::FRAGMENT_CACHE_KEY_MAX_LENGTH);
	}

	public static function get_fragment($key) {
		return get_transient(self::fragment_key($key));
	}

	public static function set_fragment($key, $value, $expiry = null) {
		$expiry = $expiry ? $expiry : self::config('fragment_cache_expiry');
		return set_transient(self::fragment_key($key), $value, $expiry);
	}

	public static function clear_fragment($key) {
		return delete_transient(self::fragment_key($key));
	}

	public static function clear_fragments() {
		global $wpdb;
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_" . self::FRAGMENT_CACHE_KEY_PREFIX . "%'");
	}

	/***** OPTIONS PAGE *****/

	public static function add_menu() {
		add_submenu_page(
			Dog_Shared::PLUGIN_SLUG,
	        dog__txt('DOG Cache Manager'),
	        dog__txt('DOG Cache Manager'),
	        'administrator',
	        self::PLUGIN_SLUG,
	        array(__CLASS__, 'options_page')
	    );
	}

	public static function options_page() {
		require_once DOG__CM_PLUGIN_DIR . 'options.php';
	}

	public static function save_options() {
		if (dog__is_post('clear')) {
			Dog_Form::sanitize_post_data();
			Dog_Form::validate_nonce('cm-options');
			Dog_Form::validate_honeypot();
			if (Dog_Form::form_is_valid()) {
				self::clear_fragments();
				$old_salt = self::get_salt();
				$new_salt = self::update_salt();
				if ($new_salt && $new_salt != $old_salt) {
					dog__set_admin_form_message(dog__txt('Memoria cache a fost golită'));
				} else {
					dog__set_admin_form_error(dog__txt('Sistemul a întâmpinat o eroare. Memoria nu poate fi golită'));
				}
			} else {
				$error_message = dog__txt('Sistemul a întâmpinat erori la procesarea formularului:');
				$errs = Dog_Form::get_all_errors();
				if ($errs) {
					foreach ($errs as $field_name => $data) {
						foreach ($data as $type => $message) {
							$error_message .= "<br /><u>{$message}</u>";
						}
					}
				}
				dog__set_admin_form_error($error_message);
			}
		}
   		wp_redirect(admin_url('admin.php?page=' . self::PLUGIN_SLUG));
		exit;
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