<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Media_Taxonomy {

	const DEFAULT_TAXONOMY_NAME = 'dog__media_cat';
	const DEFAULT_TAXONOMY_SLUG = 'media';
	private static $_initiated = false;

	public static function init() {
		if (self::$_initiated) {
			return;
		}
		add_action('init', array('Dog_Media_Taxonomy', 'register_media_taxonomy'));
		self::$_initiated = true;
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

}