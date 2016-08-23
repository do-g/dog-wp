<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Media_Features {

	const DEFAULT_TAXONOMY_NAME = 'dog__media_cat';
	const DEFAULT_TAXONOMY_SLUG = 'media';
	const BULK_ACTION_MEDIA_CATEGORY_PREFIX = 'toggle_category__';
	private static $_initialized = false;

	public static function init() {
		if (self::$_initialized) {
			return;
		}
		add_action('init', array(__CLASS__, 'setup'));
		add_action('plugins_loaded', array(__CLASS__, 'register_labels'));
		add_action('load-upload.php', array(__CLASS__, 'bulk_action'));
		self::$_initialized = true;
	}

	public static function setup() {
		if (self::check()) {
			add_action('wp_ajax_dog_mf', array(__CLASS__, 'ajax_handler'));
			add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
			add_filter('post_mime_types', array(__CLASS__, 'filter_mime_types'));
			add_filter('upload_mimes', array(__CLASS__, 'allow_mime_types'));
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
		        	'name' => dog__txt('Categorii Media'),
		        	'singular_name' => dog__txt('Categorie Media'),
		        	'all_items' => dog__txt('Toate categoriile media'),
		        	'edit_item' => dog__txt('Editare categorie media'),
		        	'view_item' => dog__txt('Vezi categoria media'),
		        	'update_item' => dog__txt('Actualizare categorie media'),
					'add_new_item' => dog__txt('Adaugă o noua categorie media'),
					'new_item_name' => dog__txt('Numele noii categorii media'),
					'parent_item' => dog__txt('Categorie media părinte'),
					'parent_item_colon' => dog__txt('Categorie media părinte:'),
					'search_items' => dog__txt('Caută categorii media'),
					'popular_items' => dog__txt('Categorii media populare'),
					'separate_items_with_commas' => dog__txt('Separă categoriile media prin virgulă'),
					'add_or_remove_items' => dog__txt('Adaugă sau elimină categorii media'),
					'choose_from_most_used' => dog__txt('Alege din cele mai folosite categorii media'),
		        	'not_found' => dog__txt('Nicio categorie media găsită'),
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

	public static function enqueue_assets($hook) {
		if ($hook != 'upload.php') {
	        return;
	    }
	    $config = self::config();
		$cats = get_terms(array(
		    'taxonomy' => $config['name'],
		    'hide_empty' => false,
		    'orderby' => 'title',
		    'order' => 'desc',
		));
		$nonces = array();
		$nonces[dog__nonce_var_key('bulk-category-switch')] = wp_create_nonce(dog__string_to_key('bulk-category-switch'));
		$media_features = array(
			'labels' => array(
				'switch_category' => dog__txt('Comută categoria: ${cat}'),
				'apply_switch_category' => dog__txt('Aplică'),
				'no_item_selected' => dog__txt('Nu ai selectat niciun obiect'),
			),
			'nonces' => $nonces,
			'switch_category_action_prefix' => self::BULK_ACTION_MEDIA_CATEGORY_PREFIX,
			'categories' => array(),
		);
		if ($cats) {
			foreach ($cats as $c) {
				$media_features['categories'][$c->term_id] = $c->name;
			}
			$media_features['categories'][0] = dog__txt('Elimină toate categoriile');
		}
		wp_enqueue_style('dog_mf_styles', dog__plugin_url(__FILE__, 'styles.css'), null, null);
	    wp_enqueue_script('dog_mf_bulk_actions', dog__plugin_url(__FILE__, 'scripts.js'), array('jquery'), null, true);
	    wp_localize_script('dog_mf_bulk_actions', 'dog__media_features', $media_features);
	}

	public static function bulk_action() {
		if (!isset($_REQUEST['action'])) {
			return;
		}
		$bulk_action = $_REQUEST['action'] != -1 ? $_REQUEST['action'] : $_REQUEST['action2'];
		$delimiter = '__';
		$pos = strpos($bulk_action, $delimiter);
		if ($pos === false) {
			return;
		}
		$custom_action = substr($bulk_action, 0, $pos);
		if (!$custom_action) {
			return;
		}
		$action_data = substr($bulk_action, $pos + strlen($delimiter));
		check_admin_referer('bulk-media');
		call_user_func(array(__CLASS__, 'bulk_action_' . $custom_action), $action_data);
		$sendback = admin_url('upload.php');
		if (isset($_REQUEST['paged'])) {
			$pagenum = absint($_REQUEST['paged']);
			$sendback = esc_url(add_query_arg('paged', $pagenum, $sendback));
		}
		if (isset($_REQUEST['orderby'])) {
			$orderby = $_REQUEST['orderby'];
			$sendback = esc_url(add_query_arg('orderby', $orderby, $sendback));
		}
		if (isset($_REQUEST['order'])) {
			$order = $_REQUEST['order'];
			$sendback = esc_url(add_query_arg('order', $order, $sendback));
		}
		wp_redirect($sendback);
		exit;
	}

	public static function bulk_action_toggle_category($cat_id) {
		$config = self::config();
		$cat_id = intval($cat_id);
		$ids = array_map('intval', $_REQUEST['media']);
		if (empty($ids)) {
			return;
		}
		foreach ($ids as $id) {
			if (!$cat_id) {
				wp_delete_object_term_relationships($id, $config['name']);
			} else if (has_term($cat_id, $config['name'], $id)) {
				wp_remove_object_terms($id, $cat_id, $config['name']);
			} else {
				wp_add_object_terms($id, array($cat_id), $config['name']);
			}
		}
	}

	public static function filter_mime_types($mime_types) {
		return apply_filters('dog__filter_mime_types', array_merge($mime_types, array(
			'application/pdf' => array(dog__txt('PDFs'), dog__txt('Manage PDFs'), _n_noop('PDF <span class="count">(%s)</span>', 'PDFs <span class="count">(%s)</span>')),
		)));
	}

	public static function allow_mime_types($mime_types) {
  		return apply_filters('dog__allow_mime_types', array_merge($mime_types, array(
  			'svg' => 'image/svg+xml'
  		)));
	}

	public static function ajax_handler() {

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
        $plugin_dir = basename(dirname(__FILE__));
        $plugin_name = "{$plugin_dir}/plugin.php";
        deactivate_plugins($plugin_name);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
	}

	public static function requires_notice() {
		$plugin_path = dirname(__FILE__);
		$plugin_file = "{$plugin_path}/plugin.php";
		$plugin_data = get_plugin_data($plugin_file, false, false);
		?><div class="error"><p>Plugin "<?= $plugin_data['Name'] ?>" requires the "DOG Shared" plugin to be installed and active</p></div><?php
	}

}