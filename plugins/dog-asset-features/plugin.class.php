<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Asset_Features {

	const PLUGIN_SLUG = 'dog-asset-features';
	const OPTION_CSS = 'assets_css';
	const OPTION_JS = 'assets_js';
	const OPTION_VERSION_CSS = 'assets_css_version';
	const OPTION_VERSION_JS = 'assets_js_version';
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
			add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
			add_action('admin_post_dog_save_af_options', array(__CLASS__, 'save_options'));
			add_action('admin_menu', array(__CLASS__, 'add_menu'));
			add_filter('plugin_action_links_' . dog__get_plugin_name_from_path(__FILE__, true), array(__CLASS__, 'options_link'));
		} else {
			add_action('admin_init', array(__CLASS__, 'requires'));
		}
	}

	public static function enqueue_assets($hook) {
		if ($hook != Dog_Shared::MENU_HOOK_PREFIX . '_page_' . self::PLUGIN_SLUG) {
	        return;
	    }
	    wp_enqueue_style('dog_af_styles', dog__plugin_url('styles.css', self::PLUGIN_SLUG), array('dog_sh_styles_shared'), null);
	    wp_enqueue_script('dog_af_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('dog_sh_scripts_shared'), null, true);
	}

	/***** OPTIONS PAGE *****/

	public static function add_menu() {
		add_submenu_page(
			Dog_Shared::PLUGIN_SLUG,
	        dog__txt('DOG Asset Features'),
	        dog__txt('DOG Asset Features'),
	        'administrator',
	        self::PLUGIN_SLUG,
	        array(__CLASS__, 'options_page')
	    );
	}

	public static function options_page() {
		require_once DOG__AF_PLUGIN_DIR . 'options.php';
	}

	public static function save_options() {
		if (dog__is_post('optimize')) {
			Dog_Form::whitelist_keys(array(self::OPTION_CSS, self::OPTION_JS));
			Dog_Form::sanitize_post_data(array(
				self::OPTION_CSS => DOG__POST_FIELD_TYPE_TEXTAREA,
				self::OPTION_JS  => DOG__POST_FIELD_TYPE_TEXTAREA,
			));
			Dog_Form::validate_nonce('af-options');
			Dog_Form::validate_honeypot();
			if (Dog_Form::form_is_valid()) {
				$result = self::process_assets();
				if (is_wp_error($result)) {
					dog__set_admin_form_error($result->get_error_message());
				} else {
					dog__set_admin_form_message(dog__txt('Resursele statice au fost optimizate'));
				}
			} else {
				self::set_form_errors();
			}
		} else if (dog__is_post('reset')) {
			Dog_Form::sanitize_post_data();
			Dog_Form::validate_nonce('af-options');
			Dog_Form::validate_honeypot();
			if (Dog_Form::form_is_valid()) {
				dog__delete_option(Dog_Asset_Features::OPTION_VERSION_CSS);
				dog__delete_option(Dog_Asset_Features::OPTION_VERSION_JS);
				if (self::delete_assets()) {
					dog__set_admin_form_message(dog__txt('Optimizarea resurselor a fost revocată'));
				} else {
					dog__set_admin_form_error(dog__txt('Sistemul a întâmpinat o eroare. Unele fișiere nu pot fi șterse'));
				}
			} else {
				self::set_form_errors();
			}
		}
   		wp_redirect(admin_url('admin.php?page=' . self::PLUGIN_SLUG));
		exit;
	}

	private static function set_form_errors() {
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

	private static function delete_assets() {
		$error = false;
		$dest_dir = dog__compressed_asset_dir();
		$files = glob("{$dest_dir}/*");
		if ($files) {
			foreach ($files as $f) {
				@unlink($f);
				if (is_file($f)) {
					$error = true;
				}
			}
		}
		return !$error;
	}

	private static function process_assets() {
		$errors = array();
		$dest_dir = dog__compressed_asset_dir();
		if (!is_dir($dest_dir)) {
			if (mkdir($dest_dir, 0755, true) === false) {
				return new WP_Error(1, dog__txt('Sistemul a întâmpinat o eroare la crearea directorului în cache'));
			}
		}

		/***** styles *****/
		$old_styles_version = dog__get_option(self::OPTION_VERSION_CSS);
		@unlink("{$dest_dir}/{$old_styles_version}.css");
		$styles = Dog_Form::get_post_value(self::OPTION_CSS);
		$styles_content = '';
		if ($styles) {
			$styles_list = explode("\n", $styles);
			foreach ($styles_list as $s) {
				$s = trim($s);
				$info = wp_remote_get($s);
				if (!is_array($info) || !$info['response'] || !$info['response']['code'] || $info['response']['code'] != 200) {
					array_push($errors, $s);
					continue;
				}
				$styles_content .= $info['body'] ? "{$info['body']}\n\n" : '';
			}
			$styles_content = trim($styles_content);
			$styles_content = self::minify_style($styles_content);
		}
		$new_styles_version = md5($styles_content);
		$dest_file = "{$dest_dir}/{$new_styles_version}.css";
		$handle = fopen($dest_file, 'w');
		if ($handle === false) {
			return new WP_Error(2, dog__txt('Sistemul a întâmpinat o eroare la crearea fișierului CSS'));
		}
		if (fwrite($handle, $styles_content) === false) {
			return new WP_Error(3, dog__txt('Sistemul a întâmpinat o eroare la scrierea fișierului CSS'));
		}
		dog__update_option(self::OPTION_VERSION_CSS, $new_styles_version, false);

		/***** scripts *****/
		$old_scripts_version = dog__get_option(self::OPTION_VERSION_JS);
		@unlink("{$dest_dir}/{$old_scripts_version}.js");
		$scripts = Dog_Form::get_post_value(self::OPTION_JS);
		$scripts_content = '';
		if ($scripts) {
			$scripts_list = explode("\n", $scripts);
			foreach ($scripts_list as $s) {
				$s = trim($s);
				$info = wp_remote_get($s);
				if (!is_array($info) || !$info['response'] || !$info['response']['code'] || $info['response']['code'] != 200) {
					array_push($errors, $s);
					continue;
				}
				$scripts_content .= $info['body'] ? "{$info['body']}\n\n" : '';
			}
			$scripts_content = trim($scripts_content);
			$scripts_content = self::minify_script($scripts_content);
		}
		$new_scripts_version = md5($scripts_content);
		$dest_file = "{$dest_dir}/{$new_scripts_version}.js";
		$handle = fopen($dest_file, 'w');
		if ($handle === false) {
			return new WP_Error(4, dog__txt('Sistemul a întâmpinat o eroare la crearea fișierului JS'));
		}
		if (fwrite($handle, $scripts_content) === false) {
			return new WP_Error(5, dog__txt('Sistemul a întâmpinat o eroare la scrierea fișierului JS'));
		}
		dog__update_option(self::OPTION_VERSION_JS, $new_scripts_version, false);

		dog__clear_page_cache();

		if ($errors) {
			return new WP_Error(6, dog__txt('Fișierele au fost comprimate cu următoarele erori: ') . '<br /><u>' . implode('</u><br /><u>', $errors) . '</u>');
		}
		return true;
	}

	public static function minify($value, $url) {
		if ($value) {
		    $postdata = array(
		    	'http' => array(
	        		'method'  => 'POST',
	        		'header'  => 'Content-type: application/x-www-form-urlencoded',
	        		'content' => http_build_query(array('input' => $value)),
	        		'timeout' => 60,
	        	)
	        );
			$value = file_get_contents($url, false, stream_context_create($postdata));
		}
		return $value;
	}

	public static function minify_style($value) {
		return self::minify($value, 'http://cssminifier.com/raw');
	}

	public static function minify_script($value) {
		return self::minify($value, 'https://javascript-minifier.com/raw');
	}

	public static function options_link($links) {
		$url = admin_url('admin.php?page=' . self::PLUGIN_SLUG);
		$settings_link = '<a href="' . $url . '">' . dog__txt('Setări') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	private static function has_cached_assets($type, $version) {
		if (!$version) {
			return false;
		}
		$cache_file_name = $version . '.' . $type;
		$cache_file_path = dog__compressed_asset_dir() . dog__url_fragment($cache_file_name);
		if (!is_file($cache_file_path)) {
			return false;
		}
		return dog__compressed_asset_url($cache_file_name);
	}

	public static function has_cached_styles() {
		return self::has_cached_assets('css', dog__get_option(self::OPTION_VERSION_CSS));
	}

	public static function has_cached_scripts() {
		return self::has_cached_assets('js', dog__get_option(self::OPTION_VERSION_JS));
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