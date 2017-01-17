<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Events {

	const PLUGIN_SLUG = 'dog-events';
	const PAGE_SLUG_BREAKS = 'dog-event-breaks';
	const DB_TBL_NAME = 'dog_events';
	const DB_VERSION = '1.0.4';
	const OPTION_DB_VERSION = 'dog_events_db_version';
	const RECURRENT_UNIT_DAY = 1;
	const RECURRENT_UNIT_WEEK = 2;
	const RECURRENT_UNIT_MONTH = 3;
	private static $_initialized = false;
	private static $_config = array();
	private static $_dependencies = array();
	private static $_list_table;

	/***** INIT *****/

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		add_action('init', array(__CLASS__, 'setup'));
		add_action('plugins_loaded', array(__CLASS__, 'register_labels'));
		add_action('plugins_loaded', array(__CLASS__, 'install'));
		register_uninstall_hook(basename(dirname(__FILE__)) . '/plugin.php', array(__CLASS__, 'uninstall'));
		self::$_initialized = true;
	}

	public static function setup() {
		if (self::check()) {
			ob_start();
			require_once(dog__sibling_path('admin/dog-db-table.class.php', __FILE__));
			add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
			add_filter('set-screen-option', array(__CLASS__, 'set_screen'), 10, 3);
			add_action('admin_menu', array(__CLASS__, 'add_menu'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	public static function enqueue_assets($hook) {
		if ($hook != 'toplevel_page_dog-events') {
	        return;
	    }
		wp_enqueue_style('dog_ev_styles', dog__plugin_url('admin/styles.css', self::PLUGIN_SLUG), array('dog_sh_admin_styles'), null);
	}

	/***** ADMIN PAGE *****/

	public static function admin_url($section = null, $query = null) {
		return admin_url('admin.php?page=' . self::PLUGIN_SLUG . ($section ? '&section=' . $section : '') . ($query ? '&' . $query : ''));
	}

	public static function add_menu() {
	    $hook = add_menu_page(
	    	dog__txt('Evenimente'),
	    	dog__txt('Evenimente'),
	    	'administrator',
	    	self::PLUGIN_SLUG,
	    	array(__CLASS__, 'admin_page'),
	    	'dashicons-calendar',
	    	3
	    );
	    add_submenu_page(
	    	self::PLUGIN_SLUG,
	    	dog__txt('Toate evenimentele'),
	    	dog__txt('Toate evenimentele'),
	    	'administrator',
	    	self::PLUGIN_SLUG,
	    	array(__CLASS__, 'admin_page')
	    );
	    add_submenu_page(
	    	self::PLUGIN_SLUG,
	    	dog__txt('Întreruperi'),
	    	dog__txt('Întreruperi'),
	    	'administrator',
	    	self::PAGE_SLUG_BREAKS,
	    	array(__CLASS__, 'admin_page_breaks')
	    );
	    add_action("load-{$hook}", array(__CLASS__, 'screen_options'));
	}

	public static function admin_page() {
		$section = sanitize_title($_GET['section']);
		switch ($section) {
			default:
				$page = 'list';
				break;
		}
		require_once(dog__sibling_path('admin/' . $page . '.php', __FILE__));
	}

	public static function screen_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => dog__txt('Număr de evenimente per pagină:'),
			'default' => 20,
			'option'  => 'dog_events_per_page',
		);
		add_screen_option($option, $args);
		self::$_list_table = new Dog_Events_Table();
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public static function get_list_table() {
		return self::$_list_table;
	}

	/***** DATABASE *****/

	public static function tbl_name() {
		global $wpdb;
   		return $wpdb->prefix . self::DB_TBL_NAME;
	}

	public static function install() {
		global $wpdb;
		$installed_db_version = get_option(self::OPTION_DB_VERSION);
		if ($installed_db_version != self::DB_VERSION) {
			$charset_collate = $wpdb->get_charset_collate();
			$tbl = self::tbl_name();
			$sql = "CREATE TABLE $tbl (
			  id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
			  title varchar(255) NOT NULL,
			  description text NULL,
			  url varchar(255) NULL,
			  recurrent_unit tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			  recurrent_step tinyint(3) UNSIGNED NULL,
			  recurrent_week_day_start tinyint(1) UNSIGNED NULL,
			  recurrent_month_unit tinyint(1) UNSIGNED NULL,
			  recurrent_month_day_start tinyint(2) NULL,
			  recurrent_month_week_day_start tinyint(1) UNSIGNED NULL,
			  recurrent_month_week_day_instance tinyint(1) UNSIGNED NULL,
			  date_start date NOT NULL,
			  date_end date NULL,
			  days_count tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
			  time_start time NULL,
			  time_end time NULL,
			  replaces mediumint(9) UNSIGNED NULL,
			  active tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			  break tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			  deleted tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			  KEY replaces (replaces),
			  KEY active (active),
			  KEY break (break),
			  KEY deleted (deleted),
			  PRIMARY KEY  (id)
			) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			update_option(self::OPTION_DB_VERSION, self::DB_VERSION);
		}
	}

	public static function uninstall() {

	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__ev_options', array(
			'date_format' => 'l, j F, Y',
			'time_format' => 'H:i:s',
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