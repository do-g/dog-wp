<?php

require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');

class Dog_Media_Taxonomy {

	const DEFAULT_TAXONOMY_NAME = 'dog__media_cat';
	const DEFAULT_TAXONOMY_SLUG = 'media';
	private static $_initialized = false;

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		add_action('init', array(__CLASS__, 'setup'));
		self::$_initialized = true;
	}

	public static function setup() {
		if (self::check()) {
			self::register_media_taxonomy();
		} else {
			add_action('admin_init', array(__CLASS__, 'requires'));
		}
	}

	private static function config() {
		return apply_filters('dog__media_taxonomy_config', array(
			'name' => self::DEFAULT_TAXONOMY_NAME,
			'args' => array(
				'labels' => array(
		        	'name' => __('Categorii Media'),
		        	'singular_name' => __('Categorie Media'),
		        	'all_items' => __('Toate categoriile media'),
		        	'edit_item' => __('Editare categorie media'),
		        	'view_item' => __('Vezi categoria media'),
		        	'update_item' => __('Actualizare categorie media'),
					'add_new_item' => __('Adaugă o noua categorie media'),
					'new_item_name' => __('Numele noii categorii media'),
					'parent_item' => __('Categorie media părinte'),
					'parent_item_colon' => __('Categorie media părinte:'),
					'search_items' => __('Caută categorii media'),
					'popular_items' => __('Categorii media populare'),
					'separate_items_with_commas' => __('Separă categoriile media prin virgulă'),
					'add_or_remove_items' => __('Adaugă sau elimină categorii media'),
					'choose_from_most_used' => __('Alege din cele mai folosite categorii media'),
		        	'not_found' => __('Nicio categorie media găsită'),
		      	),
		      	'hierarchical' => true,
		        'show_admin_column' => true,
		        'rewrite' => array('slug' => self::DEFAULT_TAXONOMY_SLUG),
			),
		));
	}

	public static function register_media_taxonomy() {
		$config = self::config();
		register_taxonomy($config['name'], 'attachment', $config['args']);
	}

	/***** REQUIRE DEPENDENCIES *****/

	public static function check() {
		return function_exists('dog__get_option');
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