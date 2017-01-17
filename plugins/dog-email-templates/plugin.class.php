<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Email_Templates {

	const PLUGIN_SLUG = 'dog-email-templates';
	const DEFAULT_POST_NAME = 'dog__email';
	const NAME_META_KEY = 'dog_name';
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
			self::register_post_type();
			add_filter('enter_title_here', array(__CLASS__, 'alter_title_label'));
			add_action('add_meta_boxes_' . self::config('name'), array(__CLASS__, 'alter_slug_label'));
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	/***** LOGIC *****/

	private static function register_post_type() {
		register_post_type(self::config('name'), self::config('post'));
	}

	public static function alter_title_label($title) {
		$screen = get_current_screen();
	    if ($screen->post_type == self::config('name')) {
	        $title = dog__txt('Subiectul mesajului');
	    }
	    return $title;
	}

	public static function alter_slug_label() {
		remove_meta_box('slugdiv', self::config('name'), 'normal');
    	add_meta_box('slugdiv', dog__txt('Nume unic'), 'post_slug_meta_box', self::config('name'), 'normal', 'high');
	}

	public static function get($name, $template_vars = array(), $language = null) {
		$path = $language ? "{$name}-{$language}" : $name;
		$post = get_page_by_path($path, null, self::config('name'));
		if (!$post) {
			$post = get_page_by_path($name, null, self::config('name'));
			if ($post) {
				$trans_id = pll_get_post($post->ID, $language);
				$post = get_post($trans_id);
			}
		}
		if ($post) {
			$post->post_content = apply_filters('the_content', $post->post_content);
			$template = new stdClass();
			$template->subject = dog__replace_template_vars($post->post_title, $template_vars);
			$template->message = dog__replace_template_vars($post->post_content, $template_vars);
			return $template;
		}
	}

	/***** CONFIG *****/

	private static function load_config() {
		return apply_filters('dog__et_options', array(
			'name' => self::DEFAULT_POST_NAME,
			'post' => array(
				'labels' => array(
		        	'name' => __('Mesaje email'),
		        	'singular_name' => __('Mesaj email'),
		        	'add_new' => __('Adaugă mesaj email'),
		        	'add_new_item' => __('Adaugă mesaj email nou'),
		        	'edit_item' => __('Editare mesaj email'),
		        	'new_item' => __('Mesaj email nou'),
		        	'view_item' => __('Vezi mesajul email'),
		        	'search_items' => __('Caută mesaje email'),
		        	'not_found' => __('Niciun mesaj email găsit'),
		        	'not_found_in_trash' => __('Nu sunt mesaje email în coșul de gunoi'),
		        	'all_items' => __('Toate mesajele email'),
		        	'archives' => __('Arhivă mesaje email'),
		        	'insert_into_item' => __('Inserează în mesaj email'),
					'uploaded_to_this_item' => __('Încărcat în acest mesaj email'),
					'filter_items_list' => __('Filtrează lista de mesaje email'),
					'items_list_navigation' => __('Navigare în lista de mesaje email'),
					'items_list' => __('Listă de mesaje email'),
		      	),
		      	'description' => __('Șablon pentru mesaje template trimise din website'),
		      	'public' => true,
		      	'exclude_from_search' => true,
		      	'publicly_queryable' => false,
		      	'show_ui' => true,
		      	'show_in_nav_menus' => false,
		      	'menu_position' => 10,
		      	'menu_icon' => 'dashicons-email',
		      	'supports' => array('title', 'editor'),
		      	'rewrite' => false,
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