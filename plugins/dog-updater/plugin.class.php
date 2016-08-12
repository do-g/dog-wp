<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Updater {

	const UPDATE_URL = 'http://public.dorinoanagurau.ro/wp/${type}/${name}/info.php';
	const TYPE_PLUGINS = 'plugins';
	const TYPE_THEMES = 'themes';
	const OPTION_UPDATE_INFO = 'update_info';
	private static $_initialized = false;
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
			#add_filter('pre_set_site_transient_update_themes', array(__CLASS__, 'check_for_updates'));
			add_filter('site_transient_update_plugins', array(__CLASS__, 'register_plugin_updates'));
			add_filter('site_transient_update_themes', array(__CLASS__, 'register_theme_updates'));
			add_action('delete_site_transient_update_plugins', array(__CLASS__, 'clear_plugin_updates'));
			add_action('delete_site_transient_update_themes', array(__CLASS__, 'clear_theme_updates'));
			add_action('admin_post_dog_save_updater_options', array(__CLASS__, 'save_options'));
			add_action('admin_menu', array(__CLASS__, 'add_to_settings_menu'));
			add_filter('plugin_action_links_' . dog__get_full_plugin_name_from_path(__FILE__), array(__CLASS__, 'settings_link'));
		} else {
			add_action('admin_init', array(__CLASS__, 'requires'));
		}
	}

	private static function get_update_url($type, $name) {
		$url = self::UPDATE_URL;
		$url = str_replace('${type}', $type, $url);
		$url = str_replace('${name}', $name, $url);
		return $url;
	}

	public static function check_for_updates() {
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$do_update = false;
		$plugins = get_plugins();
		if ($plugins) {
			foreach ($plugins as $name => $data) {
				if (in_array(substr($name, 0, 4), array('dog/', 'dog-'))) {
					$plugin_name = explode('/', $name);
					if (self::check_update(self::TYPE_PLUGINS, reset($plugin_name), $data['Version'])) {
						$do_update = true;
					}
				}
			}
		}
		$themes = wp_get_themes();
		if ($themes) {
			foreach ($themes as $name => $data) {
				if (in_array($name, dog__get_dog_theme_names())) {
					if (self::check_update(self::TYPE_THEMES, $name, $data->Version)) {
						$do_update = true;
					}
				}
			}
		}
		return $do_update;
	}

	public static function check_update($type, $name, $current_version) {
		$update_info = dog__get_option(self::OPTION_UPDATE_INFO);
		$update_info = $update_info ? $update_info : array();
		$update_info[$type] = isset($update_info[$type]) ? $update_info[$type] : array();
		$info = wp_remote_get(self::get_update_url($type, $name));
		if (!is_array($info)) {
			$err_message = dog__txt('Sistemul a întâmpinat o eroare. Comunicarea cu serverului de actualizări a eșuat pentru obiectul "${name}"', array('name' => $name));
			self::add_error($err_message);
			add_action('admin_notices', array(__CLASS__, 'check_failed_notice'));
			return false;
		}
		$info = json_decode($info['body']);
		if (json_last_error() != JSON_ERROR_NONE) {
			$err_message = dog__txt('Sistemul a întâmpinat o eroare. Răspunsul serverului de actualizări nu poate fi procesat pentru obiectul "${name}"', array('name' => $name));
			self::add_error($err_message);
			add_action('admin_notices', array(__CLASS__, 'check_failed_notice'));
			return false;
		}
		$do_update = version_compare($info->version, $current_version) == 1;
		if ($do_update) {
			$update_info[$type][$name] = $info;
		} else {
			unset($update_info[$type][$name]);
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
						$key = $type == self::TYPE_THEMES ? $name : "{$name}/{$name}.php";
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

	public static function clear_plugin_updates() {
		return self::clear_updates(self::TYPE_PLUGINS);
	}

	public static function clear_theme_updates() {
		return self::clear_updates(self::TYPE_THEMES);
	}

	public static function clear_updates($type) {
		$data = dog__get_option(self::OPTION_UPDATE_INFO);
		if ($data) {
			unset($data[$type]);
			dog__update_option(self::OPTION_UPDATE_INFO, $data);
		}
	}

	public static function check_failed_notice() {
		?><div class="error"><p><?= self::get_error() ?></p></div><?php
	}

	private static function add_error($message) {
		array_push(self::$_errors, $message);
	}

	private static function get_error() {
		return array_shift(self::$_errors);
	}

	public static function add_to_settings_menu() {
		add_options_page(
	        'DOG Updater',
	        'DOG Updater',
	        'manage_options',
	        'dog-updater',
	        array(__CLASS__, 'settings_page')
	    );
	}

	public static function settings_page() {
		?><div class="wrap">
			<h1>Opțiuni actualizare</h1>
			<?php if (isset($_GET['update'])) { ?>
				<div id='message' class='updated fade is-dismissible'><p><strong><?= $_GET['update'] ? dog__txt('Pentru unele module sau teme sunt disponibile versiuni mai noi') . '. <a href="update-core.php">' . dog__txt('Apasă aici pentru a vedea lista exactă') . '</a>.' : dog__txt('Toate modulele și temele sunt actualizate la zi') ?></strong></p></div>
			<?php } ?>
			<form name="form" method="post" action="admin-post.php">
 		 		<p><?= dog__txt('Apasă pe butonul de mai jos dacă vrei să verifici actualizările disponibile pentru module și teme DOG') ?></p>
 		 		<input type="hidden" name="action" value="dog_save_updater_options" />
         		<?php wp_nonce_field( 'dog__up_check' ); ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?= dog__txt_attr('Verifică actualizări disponibile') ?>"></p>
			</form>
		</div><?php
	}

	public static function save_options() {
   		check_admin_referer('dog__up_check');
   		$res = self::check_for_updates();
		wp_redirect(admin_url('options-general.php?page=dog-updater&update=' . $res));
		exit;
	}

	public static function settings_link($links) {
		$url = get_admin_url() . 'options-general.php?page=dog-updater';
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

	public static function check() {
		return function_exists('dog__txt');
	}

	public static function requires() {
        add_action('admin_notices', array(__CLASS__, 'requires_notice'));
        $plugin_name = basename(dirname(__FILE__));
        $plugin_name = "{$plugin_name}/{$plugin_name}.php";
        deactivate_plugins($plugin_name);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
	}

	public static function requires_notice() {
		$plugin_path = dirname(__FILE__);
		$plugin_name = basename($plugin_path);
		$plugin_file = "{$plugin_path}/{$plugin_name}.php";
		$contents = file_get_contents($plugin_file);
		if (preg_match('/Plugin Name: (.*)/', $contents, $matches)) {
			$name = trim($matches[1]);
			$plugin_name = $name ? $name : $plugin_name;
		}
		?><div class="error"><p>Plugin "<?= $plugin_name ?>" requires the "DOG Shared" plugin to be installed and active</p></div><?php
	}

}