<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

class Dog_Media_Features {

	const PLUGIN_SLUG = 'dog-media-features';
	const DEFAULT_TAXONOMY_NAME = 'dog__media_cat';
	const DEFAULT_TAXONOMY_SLUG = 'media';
	const BULK_ACTION_MEDIA_CATEGORY_PREFIX = 'toggle_category__';
	private static $_initialized = false;
	private static $_dependencies = array();

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
			add_action('admin_head', array(__CLASS__, 'fix_svg_size'));
			add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
			add_filter('post_mime_types', array(__CLASS__, 'filter_mime_types'));
			add_filter('upload_mimes', array(__CLASS__, 'allow_mime_types'));
			add_filter('dog__sh_js_nonces', array(__CLASS__, 'nonces'));
			self::register_media_taxonomy();
		} else {
			add_action('admin_init', array(__CLASS__, 'depends'));
		}
	}

	private static function config() {
		return apply_filters('dog__mf_config', array(
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
				'no_action_selected' => dog__txt('Nu ai selectat nicio acțiune'),
				'update_complete' => dog__txt('Categoriile au fost modificate'),
				'bulk_actions' => dog__txt('Acțiuni în masă'),
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
		wp_enqueue_style('dog_mf_styles', dog__plugin_url('styles.css', self::PLUGIN_SLUG), array('dog_sh_styles_shared'), null);
	    wp_enqueue_script('dog_mf_scripts', dog__plugin_url('scripts.js', self::PLUGIN_SLUG), array('dog_sh_scripts_shared'), null, true);
	    wp_localize_script('dog_mf_scripts', 'dog__mf', $media_features);
	}

	public static function nonces($nonces) {
		return array_merge($nonces, dog__to_nonces(array(
			'Dog_Media_Features::update_categories'
		)));
	}

	public static function fix_svg_size() {
		echo '<style>
			    svg, img[src*=".svg"] {
			    	min-width: 50px !important;
			      	min-height: 50px !important;
			      	max-width: 150px !important;
			      	max-height: 150px !important;
			    }
			</style>';
	}

	public static function bulk_action() {
		if (!isset($_REQUEST['action'])) {
			return;
		}
		check_admin_referer('bulk-media');
		$bulk_action = $_REQUEST['action'] != -1 ? $_REQUEST['action'] : $_REQUEST['action2'];
		self::bulk_action_prepare($bulk_action);
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

	private static function bulk_action_prepare($action) {
		$delimiter = '__';
		$pos = strpos($action, $delimiter);
		if ($pos === false) {
			return false;
		}
		$custom_action = substr($action, 0, $pos);
		if (!$custom_action) {
			return false;
		}
		$action_data = substr($action, $pos + strlen($delimiter));
		return call_user_func(array(__CLASS__, 'bulk_action_' . $custom_action), $action_data);
	}

	public static function bulk_action_toggle_category($cat_id) {
		$config = self::config();
		$cat_id = intval($cat_id);
		$ids = array_map('intval', $_REQUEST['media']);
		if (empty($ids)) {
			return false;
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
		return true;
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

	public static function update_categories() {
		if ($_POST['custom_action'] == -1) {
			return dog__ajax_response_error(array('message' => dog__txt('Nu ai selectat nicio acțiune')));
		} else if (!$_REQUEST['media']) {
			return dog__ajax_response_error(array('message' => dog__txt('Nu ai selectat niciun obiect')));
		} else if (!self::bulk_action_prepare($_POST['custom_action'])) {
			return dog__ajax_response_error(array('message' => dog__txt('Sistemul a întâmpinat o eroare. Categoriile nu pot fi modificate')));
		}
		return dog__ajax_response_ok();
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