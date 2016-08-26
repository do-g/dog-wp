<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Labels {

	const PLUGIN_SLUG = 'dog-labels';
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
			add_action('admin_post_dog_save_lb_options', array(__CLASS__, 'save_options'));
			add_action('admin_menu', array(__CLASS__, 'add_menu'));
			add_filter('plugin_action_links_' . dog__get_plugin_name_from_path(__FILE__, true), array(__CLASS__, 'options_link'));
		} else {
			add_action('admin_init', array(__CLASS__, 'requires'));
		}
	}

	/***** OPTIONS PAGE *****/

	public static function add_menu() {
		add_submenu_page(
			Dog_Shared::PLUGIN_SLUG,
	        dog__txt('DOG Labels'),
	        dog__txt('DOG Labels'),
	        'administrator',
	        self::PLUGIN_SLUG,
	        array(__CLASS__, 'options_page')
	    );
	}

	public static function options_page() {
		require_once DOG__LB_PLUGIN_DIR . 'options.php';
	}

	public static function save_options() {
		if (dog__is_post('generate')) {
			Dog_Form::sanitize_post_data();
			Dog_Form::validate_nonce('lb-options');
			Dog_Form::validate_honeypot();
			if (Dog_Form::form_is_valid()) {
				$result = self::generate_labels();
				if (is_wp_error($result)) {
					dog__set_admin_form_error($result->get_error_message());
				} else {
					set_transient(DOG_ADMIN__TRANSIENT_FORM_RESPONSE, $result, DOG_ADMIN__TRANSIENT_EXPIRE_FORM_MESSAGE);
					dog__set_admin_form_message(dog__txt('Verificarea s-a finalizat cu succes. Am găsit ${n} etichete', array('n' => count($result))));
					dog__set_admin_form_message('<a href="' . admin_url('options-general.php?page=mlang&tab=strings&group=dogx') . '">' . dog__txt('Apasă aici pentru traducere') . '</a>');
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

	private static function generate_labels() {
		$all_labels = array();
		$themes = array_reverse(dog__get_dog_theme_names());
		if ($themes) {
			$theme_files = array();
			$output = array("<?php\n");
			foreach ($themes as $theme) {
				$labels = self::get_path_labels(get_theme_root(), $theme, $theme_files);
				$all_labels = array_merge($all_labels, $labels);
				$output = array_merge($output, $labels);
			}
			$dest = dog__theme_path('_pll_labels.php');
			file_put_contents($dest, implode('', $output));
		}
		$plugins = dog__get_dog_plugin_names();
		if ($plugins) {
			foreach ($plugins as $plugin) {
				$output = array("<?php\n");
				$labels = self::get_path_labels(WP_PLUGIN_DIR, $plugin);
				$all_labels = array_merge($all_labels, $labels);
				$output = array_merge($output, $labels);
				$dest = dog__plugin_path('_pll_labels.php', $plugin);
				file_put_contents($dest, implode('', $output));
			}
		}
		return $all_labels;
	}

	private static function get_path_labels($path, $name, &$all_translated_files = array()) {
		$path = "{$path}/{$name}";
		$content = $labels = $keys = array();
		if (!is_dir($path)) {
			return $content;
		}
		$ignore_files = array('_pll_labels.php');
		$pattern = "{$path}/*.php";
		$files = glob($pattern, GLOB_NOSORT);
		if ($files) {
			foreach ($files as $file) {
				$file_name = basename($file);
				if (in_array($file_name, $ignore_files) || in_array($file_name, $all_translated_files)) {
					continue;
				}
				array_push($all_translated_files, $file_name);
			    $labels = array_merge($labels, dog__extract_file_labels($file));
			}
		}
		if ($labels) {
			foreach ($labels as $label) {
				$key = sanitize_title($label);
				if (!in_array($key, $keys)) {
					array_push($content, "pll_register_string('{$key}', '{$label}', '{$name}', true);\n");
					array_push($keys, $key);
				}
			}
		}
		return $content;
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