<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Updater {

	const PLUGIN_SLUG = 'dog-updater';
	const UPDATE_URL = 'http://public.dorinoanagurau.ro/wp/${type}/${name}/update.info.php';
	const TYPE_PLUGINS = 'plugins';
	const TYPE_THEMES = 'themes';
	const OPTION_UPDATE_INFO = 'update_info';
	private static $_initialized = false;
	private static $_config = array();
	private static $_dependencies = array();

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
			#add_filter('pre_set_site_transient_update_themes', array(__CLASS__, 'check_for_updates'));
			#add_action('delete_site_transient_update_plugins', array(__CLASS__, 'clear_plugin_updates'));
			#add_action('delete_site_transient_update_themes', array(__CLASS__, 'clear_theme_updates'));
			add_action('upgrader_process_complete', array(__CLASS__, 'update_complete'), 10, 2);
			add_action('admin_post_dog_save_up_options', array(__CLASS__, 'save_options'));
			add_action('admin_menu', array(__CLASS__, 'add_menu'));
			add_filter('site_transient_update_plugins', array(__CLASS__, 'register_plugin_updates'));
			add_filter('site_transient_update_themes', array(__CLASS__, 'register_theme_updates'));
			add_filter('plugin_action_links_' . dog__get_plugin_name_from_path(__FILE__, true), array(__CLASS__, 'options_link'));
			add_filter('update_bulk_plugins_complete_actions', array(__CLASS__, 'back_to_updates_link'), 10, 2);
			add_filter('update_bulk_theme_complete_actions', array(__CLASS__, 'back_to_updates_link'), 10, 2);
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	private static function get_update_url($type, $name) {
		$url = self::config('source', $type, $name);
		if (!$url) {
			$url = self::UPDATE_URL;
			$url = str_replace('${type}', $type, $url);
			$url = str_replace('${name}', $name, $url);
		}
		return $url;
	}

	public static function check_for_updates() {
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$updates = array(
			'plugins' => array(),
			'themes' => array(),
			'errors' => array(),
		);
		$plugins = get_plugins();
		if ($plugins) {
			foreach ($plugins as $name => $data) {
				$plugin_name_parts = explode('/', $name);
				$plugin_name = reset($plugin_name_parts);
				if (dog__is_dog_plugin($plugin_name) || self::config('source', 'plugins', $plugin_name)) {
					if ($result = self::check_update(self::TYPE_PLUGINS, $plugin_name, $data['Version'], $data['Name'])) {
						if (is_wp_error($result)) {
							array_push($updates['errors'], $result->get_error_message());
						} else {
							array_push($updates['plugins'], $data['Name']);
						}
					}
				}
			}
		}
		$themes = wp_get_themes();
		if ($themes) {
			foreach ($themes as $name => $data) {
				if (in_array($name, dog__get_dog_theme_names())) {
					if ($name == DOG__THEME_NAME) {
						continue;
					}
					if ($result = self::check_update(self::TYPE_THEMES, $name, $data->Version, $data->Name)) {
						if (is_wp_error($result)) {
							array_push($updates['errors'], $result->get_error_message());
						} else {
							array_push($updates['themes'], $data->Name);
						}
					}
				}
			}
		}
		return $updates;
	}

	public static function check_update($type, $slug, $current_version, $name) {
		$update_info = dog__get_option(self::OPTION_UPDATE_INFO);
		$update_info = $update_info ? $update_info : array();
		$update_info[$type] = isset($update_info[$type]) ? $update_info[$type] : array();
		$info = wp_remote_get(self::get_update_url($type, $slug));
		if (!is_array($info)) {
			return new WP_Error(1, dog__txt('Sistemul a întâmpinat o eroare. Comunicarea cu serverului de actualizări a eșuat pentru obiectul "${name}"', array('name' => $name)));
		}
		$info = json_decode(wp_remote_retrieve_body($info));
		if (json_last_error() != JSON_ERROR_NONE) {
			return new WP_Error(2, dog__txt('Sistemul a întâmpinat o eroare. Răspunsul serverului de actualizări nu poate fi procesat pentru obiectul "${name}"', array('name' => $name)));
		}
		$do_update = version_compare($info->version, $current_version) == 1;
		if ($do_update) {
			$update_info[$type][$slug] = $info;
		} else {
			unset($update_info[$type][$slug]);
		}
		dog__update_option(self::OPTION_UPDATE_INFO, $update_info);
		return $do_update;
	}

	public static function register_plugin_updates($updates) {
		return self::register_updates($updates, self::TYPE_PLUGINS);
	}

	public static function register_theme_updates($updates) {
		return self::register_updates($updates, self::TYPE_THEMES);
	}

	public static function register_updates($updates, $type) {
		$data = dog__get_option(self::OPTION_UPDATE_INFO);
		if ($data) {
			if (isset($data[$type]) && $data[$type]) {
				foreach ($data[$type] as $name => $info) {
					if ($info && $info->version && $info->about && $info->download) {
						$key = $type == self::TYPE_THEMES ? $name : "{$name}/plugin.php";
						$fields = array(
							'new_version' => $info->version,
							'url' => $info->about,
							'package' => $info->download,
						);
						if ($type == self::TYPE_PLUGINS) {
							$fields = (object) $fields;
                    		$fields->slug = $name;
                    		$fields->plugin = $key;
                		}
						$updates->response[$key] = $fields;
					}
				}
			}
		}
		return $updates;
	}

	private static function clear_plugin_updates($plugins = array()) {
		return self::clear_updates(self::TYPE_PLUGINS, $plugins);
	}

	private static function clear_theme_updates($themes = array()) {
		return self::clear_updates(self::TYPE_THEMES, $themes);
	}

	private static function clear_updates($type, $items = array()) {
		$data = dog__get_option(self::OPTION_UPDATE_INFO);
		if ($data) {
			if ($items) {
				foreach ($items as $i) {
					unset($data[$type][$i]);
				}
			} else {
				unset($data[$type]);
			}
			dog__update_option(self::OPTION_UPDATE_INFO, $data);
		}
	}

	public static function update_complete($upgrader, $info) {
		if ($info['action'] == 'update') {
			if ($info['plugins']) {
				self::clear_plugin_updates(array_map('dirname', $info['plugins']));
			}
			if ($info['themes']) {
				self::clear_theme_updates($info['themes']);
			}
		}
	}

	/***** OPTIONS PAGE *****/

	public static function add_menu() {
		add_submenu_page(
			Dog_Shared::PLUGIN_SLUG,
	        dog__txt('DOG Updater'),
	        dog__txt('DOG Updater'),
	        'administrator',
	        self::PLUGIN_SLUG,
	        array(__CLASS__, 'options_page')
	    );
	}

	public static function options_page() {
		require_once DOG__UP_PLUGIN_DIR . 'options.php';
	}

	public static function save_options() {
		if (dog__is_post('check')) {
			Dog_Form::sanitize_post_data();
			Dog_Form::validate_nonce('up-options');
			Dog_Form::validate_honeypot();
			if (Dog_Form::form_is_valid()) {
				$updates = self::check_for_updates();
				$has = false;
				if ($updates['plugins']) {
					dog__set_transient_flash_message(dog__txt('Sunt disponibile versiuni mai noi pentru următoarele module: ${list}', array('list' => implode(', ', $updates['plugins']))));
					$has = true;
				}
				if ($updates['themes']) {
					dog__set_transient_flash_message(dog__txt('Sunt disponibile versiuni mai noi pentru următoarele teme: ${list}', array('list' => implode(', ', $updates['themes']))));
					$has = true;
				}
				if ($has) {
					dog__set_transient_flash_message('<a href="update-core.php">' . dog__txt('Apasă aici pentru actualizare') . '</a>');
				} else {
					dog__set_transient_flash_message(dog__txt('Toate modulele și temele sunt actualizate la zi'));
				}
				if ($updates['errors']) {
					foreach ($updates['errors'] as $e) {
						dog__set_transient_flash_error($e);
					}
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
				dog__set_transient_flash_error($error_message);
			}
		}
   		wp_redirect(admin_url('admin.php?page=' . self::PLUGIN_SLUG));
		exit;
	}

	public static function options_link($links) {
		$url = admin_url('admin.php?page=' . self::PLUGIN_SLUG);
		$settings_link = '<a href="' . $url . '">' . dog__txt('Setări') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	public static function back_to_updates_link($update_actions, $info) {
		return array_merge($update_actions, array(
			'dog_updates' => '<a href="' . admin_url('admin.php?page=' . self::PLUGIN_SLUG) . '" target="_parent">' . dog__txt('Return to DOG Updater') . '</a>',
		));
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__up_options', array(
			'source' => array(
				'plugins' => array(),
				'themes' => array(),
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