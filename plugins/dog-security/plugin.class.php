<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Security {

	const PLUGIN_SLUG = 'dog-security';
	private static $_initialized = false;
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
			add_action('admin_post_dog_save_sc_options', array(__CLASS__, 'save_options'));
			add_action('admin_menu', array(__CLASS__, 'add_menu'));
			add_filter('plugin_action_links_' . dog__get_plugin_name_from_path(__FILE__, true), array(__CLASS__, 'options_link'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	/***** OPTIONS PAGE *****/

	public static function add_menu() {
		add_submenu_page(
			Dog_Shared::PLUGIN_SLUG,
	        dog__txt('DOG Security'),
	        dog__txt('DOG Security'),
	        'administrator',
	        self::PLUGIN_SLUG,
	        array(__CLASS__, 'options_page')
	    );
	}

	public static function options_page() {
		require_once dog__sibling_path('options.php', __FILE__);
	}

	public static function save_options() {
		if (dog__is_post('check')) {
			Dog_Form::sanitize_post_data();
			Dog_Form::validate_nonce('sc-options');
			Dog_Form::validate_honeypot();
			if (Dog_Form::form_is_valid()) {
				$result = self::check_security();
				if (is_wp_error($result)) {
					dog__set_transient_flash_error($result->get_error_message());
				} else {
					set_transient(DOG_ADMIN__TRANSIENT_FORM_RESPONSE, $result, DOG__TRANSIENT_FLASH_EXPIRE);
					dog__set_transient_flash_message(dog__txt('Verificarea s-a finalizat cu succes. Am găsit ${n} fișier(e) nesecurizate', array('n' => count($result))));
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

	private static function check_security() {
		$issues = $all = array();
		$themes = dog__get_dog_theme_names();
		if ($themes) {
			foreach ($themes as $theme) {
				$all = array_merge($all, self::check_path_security(get_theme_root() . "/{$theme}", $issues));
			}
		}
		$plugins = dog__get_dog_plugin_names();
		if ($plugins) {
			foreach ($plugins as $plugin) {
				$all = array_merge($all, self::check_path_security(WP_PLUGIN_DIR . "/{$plugin}", $issues));
			}
		}
		return $issues;
	}

	private static function check_path_security($path, &$issues = array()) {
		$pattern = '/^.+\.php$/i';
		$ignore_files = array('_pll_labels.php');
		$php_files = dog__search_files($path, $pattern);
		if ($php_files) {
			foreach ($php_files as $i => $file) {
				$name = basename($file);
				if (in_array($name, $ignore_files)) {
					unset($php_files[$i]);
					continue;
				}
				$fragment = str_replace(WP_CONTENT_DIR, '', $file);
				$url = WP_CONTENT_URL . $fragment;
				$response = wp_remote_get($url);
				$code = wp_remote_retrieve_response_code($response);
				$body = wp_remote_retrieve_body($response);
				if ($response && (!empty($body) || $code != 404)) {
					array_push($issues, '<a href="' . $url . '" target="_blank">' . $fragment . '</a>');
				}
			}
		}
		return $php_files;
	}

	public static function options_link($links) {
		$url = admin_url('admin.php?page=' . self::PLUGIN_SLUG);
		$settings_link = '<a href="' . $url . '">' . dog__txt('Setări') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
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