<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

date_default_timezone_set(DOG__TIMEZONE);

if (DOG__ENV == 'dev') {
	$error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT;
	if (defined('E_DEPRECATED')) {
		$error_reporting = $error_reporting & ~E_DEPRECATED;
	}
	error_reporting($error_reporting);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
} else {
	error_reporting(0);
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
}

$dog__dependencies = dog__extend_with('dependencies', array('Dog_Shared'));

$dog__schemaorg_page_types = apply_filters('dog__schemaorg_page_types', array(
	'AboutPage' => array('despre', 'about'),
	'ContactPage' => array('contact', 'contact-us'),
	'CollectionPage' => array(),
	'ItemPage' => array(),
	'ProfilePage' => array(),
	'SearchResultsPage' => array(),
));

$dog__slug_template_map = apply_filters('dog__slug_template_map', array(
	'page-acasa' => array('home'),
	'page-contact' => array('contact-us'),
));

$dog__breadcrumbs = array();

$dog__config = array();

function dog__config() {
	global $dog__config;
	if (!$dog__config) {
		$dog__config = dog__load_config();
	}
	$config = $dog__config;
	$args = func_get_args();
	while ($args) {
		$arg = array_shift($args);
		$config = $config[$arg];
	}
	return $config;
}

function dog__load_config() {
	return apply_filters('dog__options', array(
		'sidebar_on_left' => false,
		'article_list_image_on_left' => true,
		'contact_email' => null,
	));
}

function dog__add_breadcrumb($label, $url = null, $options = array()) {
	global $dog__breadcrumbs;
	array_push($dog__breadcrumbs, array(
		'label' => $label,
		'url' => $url,
		'options' => $options,
	));
}

function dog__add_to_breadcrumbs($id, $options = array()) {
	global $dog__breadcrumbs;
	array_push($dog__breadcrumbs, array(
		'id' => $id,
		'options' => $options,
	));
}

function dog__get_breadcrumbs() {
	global $dog__breadcrumbs;
	return $dog__breadcrumbs;
}

function dog__render_custom_breadcrumbs_script() {
	$bc = dog__get_breadcrumbs();
	if ($bc) {
		echo "<script type=\"text/javascript\">\n";
		echo "\tdog__sh.bc = [];\n";
		foreach ($bc as $b) {
			echo "\tdog__sh.bc.push(" . json_encode($b) . ");\n";
		}
		echo '</script>';
	}
}

function dog__set_query_args($query, $args) {
	if ($args) {
		foreach ($args as $k => $v) {
			$query->set($k, $v);
		}
	}
}

function dog__query_exclude_taxonomy_children($query, $taxonomy = null) {
	if ($query->tax_query->queries) {
		foreach ($query->tax_query->queries as $i => $tax_query) {
	      if (!$taxonomy || $tax_query['taxonomy'] === $taxonomy) {
	         $query->tax_query->queries[$i]['include_children'] = false;
	      }
	    }
	}
}

function dog__cancel_query($query) {
	dog__set_query_args($query, array(
		'category__in' => array(-999),
	));
}

function dog__entry_css_class() {
	$obj = get_queried_object();
	if ($obj->term_id) {
		return get_term_meta(get_queried_object_id(), DOG__ENTRY_CSS_CLASS_META_KEY, true);
	} else {
		return get_post_meta(get_queried_object_id(), DOG__ENTRY_CSS_CLASS_META_KEY, true);
	}
}

function dog__include_template($filename, $tpl_data = null) {
	include(locate_template($filename . '.php'));
}

function dog__loop_content($template, $data = array()) {
	dog__include_template('_content-loop', array_merge(array('template' => $template), $data));
}

function dog__body_class($user_classes = array()) {
	$classes = $user_classes ? $user_classes : array();
	$uri = esc_url_raw($_SERVER['REQUEST_URI']);
	$parts = explode('?', trim($uri, '/'));
	$uri = $parts[0];
	$uri = explode('/', $uri);
	$possible_category = $uri[0];
	$parts = array();
	foreach ($uri as $p) {
		if ($p) {
			array_push($classes, 'uri--' . $p);
		}
	}
	$classes = $classes ? $classes : array('uri--acasa');
	$obj = get_queried_object();
	if ($obj->ID) {
		$tags = get_the_tags($obj->ID);
		if ($tags) {
			foreach ($tags as $t) {
				array_push($classes, "tag--{$t->slug}");
			}
		}
	}
	$entry_css_class = dog__entry_css_class();
	if ($entry_css_class) {
		$classes = array_merge($classes, explode(' ', $entry_css_class));
	}
	if (dog__lang_plugin_is_active()) {
		array_push($classes, 'lang--' . dog__active_language());
		if (!dog__is_default_language()) {
			if ($obj->cat_ID) {
				$translated_id = pll_get_term($obj->cat_ID, dog__default_language());
				$translated = get_category($translated_id);
				array_push($classes, "uri--{$translated->slug}");
				array_push($classes, "trans--{$translated->slug}");
			} else {
				if ($obj->post_type == 'post') {
					$category = get_category_by_slug($possible_category);
					$translated_id = pll_get_term($category->cat_ID, dog__default_language());
					$translated = get_category($translated_id);
					array_push($classes, "uri--{$translated->slug}");
					array_push($classes, "trans--{$translated->slug}");
				}
				$translated_id = pll_get_post($obj->ID, dog__default_language());
				$translated = get_post($translated_id);
				array_push($classes, "uri--{$translated->post_name}");
				array_push($classes, "trans--{$translated->post_name}");
			}
		} else {
			array_push($classes, 'lang--default');
		}
	}
	$classes = array_map('sanitize_html_class', $classes);
	return esc_attr(implode(' ', array_merge(get_body_class(), $classes)));
}

function dog__contact_url() {
	return dog__lang_url('contact');
}

function dog__contact_success_url() {
	return dog__override_with(__FUNCTION__, dog__timestamp_url(dog__contact_url()));
}

/**
 * 	$params = array(
 *		'post' => array(),
 *		'template_vars' => array(),
 *		'headers' => array(),
 *		'email' => '',
 *		'cc' => array(),
 *		'subject' => '',
 *		'template' => '',
 *		'language' => '',
 *		'confirmation' => array(
 *			'template_vars' => array(),
 *			'headers' => array(),
 *			'email_field' => '',
 *			'cc' => array(),
 *			'subject' => '',
 *			'template' => '',
 *			'language' => '',
 *		),
 *	);
 */
function dog__send_form_mail_standard($params = null) {
	$main_domain = dog__site_domain(true);
	$full_domain = dog__site_domain();
	$site_title = get_bloginfo('name');
	$site_title_safe = str_replace(',', '', $site_title);

	$post_data = $params['post'] ? $params['post'] : Dog_Form::get_post_data();
	$default_template_vars = array_merge($post_data, array(
		'website_domain' => $full_domain,
		'website_url' 	 => site_url(),
		'website_title'  => $site_title,
	));

	$confirmation = $params['confirmation'];
	$confirmation_email_field = dog__value_or_default($confirmation['email_field'], 'contact_email');
	$confirmation_email = $post_data[$confirmation_email_field];

	$custom_template_vars = dog__value_or_default($params['template_vars'], array());
	$template_vars = array_merge($default_template_vars, $custom_template_vars);

	$custom_headers = dog__value_or_default($params['headers'], array());
	$headers = array_merge(array(
		'sender' => 'noreply@' . $main_domain,
		'from' => array(
			'name' => $site_title_safe,
			'email' => 'noreply@' . $main_domain,
		),
		'reply' => array(
			'email' => $confirmation_email,
		),
	), $custom_headers);

	if (!$confirmation_email) {
		unset($headers['reply']);
	}

	$recipient = dog__value_or_default($params['email'], dog__config('contact_email') ?: get_option('admin_email'));
	$cc = isset($params['cc']) && is_array($params['cc']) ? $params['cc'] : array($params['cc']);
	$recipients = array_merge(array($recipient), $cc);

	$template_name = $params['template'] ? $params['template'] : 'contact-receiver';
	$language = dog__value_or_default($params['language'], dog__default_language());
	if (class_exists('Dog_Email_Templates')) {
		$tpl = Dog_Email_Templates::get($template_name, $template_vars, $language);
		if (!$tpl) {
			return array('read_template' => dog__txt("Șablonul ({$template_name}) pentru limba ({$language}) nu a fost găsit"));
		}
		$subject = $tpl->subject;
		$template = $tpl->message;
	} else {
		$subject = dog__value_or_default($params['subject'], dog__txt('Ai primit un mesaj de contact', null, $language));
		$template = dog__get_theme_email_template($template_name, $template_vars, $language);
		if ($template === false) {
			return array('read_template' => dog__txt("Șablonul ({$template_name}) pentru limba ({$language}) nu există sau nu poate fi citit"));
		}
	}

	$result = dog__send_mail($recipients, $subject, $template, $headers, $params['attachments']);
	if (is_array($result)) {
		return $result;
	}

	if ((!$params || $confirmation) && $confirmation_email) {
		$custom_template_vars = dog__value_or_default($confirmation['template_vars'], array());
		$template_vars = array_merge($default_template_vars, $custom_template_vars);

		$custom_headers = dog__value_or_default($confirmation['headers'], array());
		$headers['reply'] = array(
			'name' => $site_title_safe,
			'email' => $recipient,
		);
		$headers = array_merge($headers, $custom_headers);

		$template_name = $confirmation['template'] ? $confirmation['template'] : 'contact-sender';
		$language = dog__value_or_default($confirmation['language'], dog__active_language());
		if (class_exists('Dog_Email_Templates')) {
			$tpl = Dog_Email_Templates::get($template_name, $template_vars, $language);
			if (!$tpl) {
				return array('read_template' => dog__txt("Șablonul ({$template_name}) pentru limba ({$language}) nu a fost găsit"));
			}
			$subject = $tpl->subject;
			$template = $tpl->message;
		} else {
			$subject = dog__value_or_default($confirmation['subject'], dog__txt('Ai trimis un mesaj de contact', null, $language));
			$template = dog__get_theme_email_template($template_name, $template_vars, $language);
			if ($template === false) {
				return array('read_template' => dog__txt("Șablonul ({$template_name}) pentru limba ({$language}) nu există sau nu poate fi citit"));
			}
		}

		$cc = isset($confirmation['cc']) && is_array($confirmation['cc']) ? $confirmation['cc'] : array($confirmation['cc']);
		$recipients = array_merge(array($confirmation_email), $cc);

		$result = dog__send_mail($recipients, $subject, $template, $headers, $confirmation['attachments']);
		if (is_array($result)) {
			return $result;
		}
	}
	return true;
}

function dog__schema_page_type() {
	global $dog__schemaorg_page_types;
	$current = get_queried_object();
	if ($current->term_id) {
		$page_type = 'CollectionPage';
	} else if ($current->post_type == 'post') {
		$page_type = 'ItemPage';
	} else {
		$page_type = 'WebPage';
	}
    $slug = $current->slug ? $current->slug : $current->post_name;
    foreach ($dog__schemaorg_page_types as $type => $slugs) {
    	if (in_array($slug, $slugs)) {
    		$page_type = $type;
    		break;
    	}
    }
	return $page_type;
}

$dog__active_template;
function dog__override_template($template) {
	global $dog__slug_template_map, $dog__active_template;
	$override = apply_filters('dog__override_template', $template);
	if ($override && $override != $template) {
		$new_template = locate_template(array($override . '.php'));
		$template = $new_template ? $new_template : $template;
	} else if ($dog__slug_template_map) {
		$current = get_queried_object();
		$slug = $current->slug ? $current->slug : $current->post_name;
		foreach ($dog__slug_template_map as $override => $slugs) {
			if (in_array($slug, $slugs)) {
				$new_template = locate_template(array($override . '.php'));
				$template = $new_template ? $new_template : $template;
				break;
			}
		}
	}
	$dog__active_template = $template;
	return $template;
}

function dog__get_active_template() {
	global $dog__active_template;
	return $dog__active_template;
}

function dog__enqueue_assets_high_priority() {
	wp_deregister_script('wp-embed');
	wp_deregister_script('jquery');
	wp_register_script('jquery', includes_url('js/jquery/jquery.js'), null, null, true);
	dog__call_x_function(__FUNCTION__);
}

function dog__enqueue_assets_low_priority() {
	$dog_af_plugin_active = class_exists('Dog_Asset_Optimiser');
	$cached_styles = $dog_af_plugin_active ? Dog_Asset_Optimiser::has_cached_styles() : false;
	if ($cached_styles !== false) {
		wp_enqueue_style('dog_min_styles', $cached_styles, null, null);
	}
	$cached_scripts = $dog_af_plugin_active ? Dog_Asset_Optimiser::has_cached_scripts() : false;
	if ($cached_scripts !== false) {
		wp_enqueue_script('dog_min_scripts', $cached_scripts, null, null, true);
		wp_localize_script('dog_min_scripts', 'dog__non', Dog_Shared::get_nonces());
	}
	dog__call_x_function(__FUNCTION__);
}

function dog__dequeue_styles() {
	wp_dequeue_style('yoast-seo-adminbar');
	dog__call_x_function(__FUNCTION__);
}

function dog__min_styles_active() {
	return wp_style_is('dog_min_styles');
}

function dog__js_vars($vars) {
	return apply_filters(__FUNCTION__, $vars);
}

function dog__nonces($nonces) {
	$list = apply_filters(__FUNCTION__, array());
	return array_merge($nonces, dog__to_nonces($list));
}

function dog__async_defer($url) {
	if (strpos($url, '#async') !== false) {
		return str_replace('#async', '', $url) . "' async defer='";
	}
	return $url;
}

function dog__alter_main_query($wp_query) {
	if ($wp_query->get('tag')) {
		$wp_query->set('post_type', 'any');
	}
	dog__call_x_function(__FUNCTION__, $wp_query);
}

function dog__widgets_init() {
	$sidebars = apply_filters(__FUNCTION__, array(
		array(
			'name'          => dog__txt('Sidebar'),
			'id'            => 'sidebar',
			'before_widget' => '<div>',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		),
	));
	foreach ($sidebars as $sidebar) {
		register_sidebar($sidebar);
	}
	dog__call_x_function(__FUNCTION__);
}

function dog__image_sizes() {
	$sizes = apply_filters(__FUNCTION__, array(
		array('small',  600,  9999),
		array('xlarge', 1200, 9999),
	));
	foreach ($sizes as $size) {
		add_image_size($size[0], $size[1], $size[2]);
	}
}

function dog__menus() {
	$menus = apply_filters(__FUNCTION__, array(
		array('location-main-menu', dog__txt('Locație Meniu Principal')),
	));
	foreach ($menus as $menu) {
		register_nav_menu($menu[0], $menu[1]);
	}
}

function dog__theme_setup() {
	add_theme_support('html5', array('search-form', 'gallery', 'caption'));
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('custom-header');
	add_editor_style(array('css/editor-style.css'));
	dog__image_sizes();
	add_filter('image_size_names_choose', 'dog__custom_image_sizes');
	dog__menus();
	dog__call_x_function(__FUNCTION__);
}

function dog__init() {
	add_post_type_support('page', 'excerpt');
	register_taxonomy_for_object_type('post_tag', 'page');
	register_taxonomy_for_object_type('post_tag', 'attachment');
	dog__call_x_function(__FUNCTION__);
}

function dog__custom_image_sizes($sizes) {
    global $_wp_additional_image_sizes;
	if (empty($_wp_additional_image_sizes)) {
		return $sizes;
	}
	foreach ($_wp_additional_image_sizes as $id => $data) {
		if (!isset($sizes[$id])) {
			$sizes[$id] = dog__txt(ucfirst(str_replace(array('_', '-'), ' ', $id)));
		}
	}
	return $sizes;
}

function dog__handle_post() {
	if (dog__is_post()) {
		$action_hook = $_POST[Dog_Form::POST_KEY_HANDLER];
		$callable = "dog__handle_post_{$action_hook}";
		$callable_x = "dogx__handle_post_{$action_hook}";
		if (is_callable($callable_x)) {
			call_user_func($callable_x);
		} else if (is_callable($callable)) {
			call_user_func($callable);
		}
	}
}

function dog__handle_post_contact() {
	if (dog__is_post('contact_submit')) {
		Dog_Form::whitelist_keys(array(
			'contact_name',
			'contact_email',
			'contact_phone',
			'contact_message',
		));
		Dog_Form::sanitize_post_data(array(
			'contact_name'  	=> Dog_Form::POST_VALUE_TYPE_TEXT,
			'contact_email'    	=> Dog_Form::POST_VALUE_TYPE_EMAIL,
			'contact_phone' 	=> Dog_Form::POST_VALUE_TYPE_TEXT,
			'contact_message'	=> Dog_Form::POST_VALUE_TYPE_TEXTAREA,
		));
		Dog_Form::validate_nonce('contact');
		Dog_Form::validate_honeypot();
		Dog_Form::validate_required_fields(array(
			'contact_name',
			'contact_email',
			'contact_message',
		));
		Dog_Form::validate_email_fields(array(
			'contact_email',
		));
		Dog_Form::validate_regex_fields(array(
			'contact_name',
			'contact_phone',
		), array(
			Dog_Form::REGEX_VALID_NAME,
			Dog_Form::REGEX_VALID_PHONE,
		), array(
			dog__txt('Numele introdus este invalid'),
			dog__txt('Numărul de telefon este invalid'),
		));
		if (Dog_Form::form_is_valid()) {
			$result = dog__send_form_mail_standard();
			if ($result === true) {
				dog__set_flash_success('form', dog__txt('Solicitarea a fost trimisă'));
				dog__safe_redirect(dog__contact_success_url());
			} else {
				Dog_Form::set_form_error(dog__txt('Formularul nu poate fi trimis sau a fost trimis cu erori'));
				foreach ($result as $n => $e) {
					Dog_Form::set_form_error($e, 'mail_' . $n);
				}
			}
		} else if (!Dog_Form::get_form_errors()) {
			Dog_Form::set_form_error(dog__txt('Te rugăm corectează erorile'));
		}
	}
}

function dog__call_x_function($function_name, $params = null) {
	$function_name = str_replace(DOG__PREFIX_X, '', $function_name);
	$function_name = str_replace(DOG__PREFIX_ADMIN, '', $function_name);
	$function_name = str_replace(DOG__PREFIX, '', $function_name);
	$function_name = DOG__PREFIX_X . $function_name;
	if (function_exists($function_name)) {
		return call_user_func($function_name, $params);
	}
}

function dog__extend_with($function_name, $default = null, $params = null) {
	$local_value = dog__call_x_function($function_name, $params);
	if ($local_value) {
		return is_array($default) ? array_merge($default, $local_value) : $local_value;
	}
	return $default;
}

function dog__override_with($function_name, $default = null, $params = null) {
	$local_value = dog__call_x_function($function_name, $params);
	return $local_value ? $local_value : $default;
}

function dog__requires() {
	global $dog__dependencies;
	if ($dog__dependencies) {
		foreach ($dog__dependencies as $d) {
			if (!class_exists($d)) {
				add_action('admin_notices', 'dog__requires_notice');
				dog__theme_switch();
				return;
			}
		}
	}
}

function dog__theme_switch() {
	$dog__themes = array_map('trim', explode(',', DOG__THEMES));
	$themes = wp_get_themes();
	if ($themes) {
		foreach ($themes as $name => $data) {
			if (!in_array($name, $dog__themes)) {
				switch_theme($name);
				return;
			}
		}
	}
}

function dog__requires_notice() {
	global $dog__dependencies;
	?><div class="error"><p>This theme requires the following plugins to be installed and active: <b><?= str_replace('_', ' ', implode('</b>, <b>', $dog__dependencies)) ?></b></p></div><?php
}

if (!is_admin()) {
	add_filter('json_enabled', '__return_false');
	add_filter('json_jsonp_enabled', '__return_false');
	add_filter('use_default_gallery_style', '__return_false');
	add_filter('template_include', 'dog__override_template', 99);
	add_filter('clean_url', 'dog__async_defer', 11, 1);
	add_filter('show_admin_bar', '__return_false');
	add_action('wp_enqueue_scripts', 'dog__enqueue_assets_high_priority', 0);
	add_action('wp_enqueue_scripts', 'dog__enqueue_assets_low_priority', 99990);
	add_action('pre_get_posts', 'dog__alter_main_query');
	add_action('wp_print_styles', 'dog__dequeue_styles', 99999);
	add_action('wp', 'dog__handle_post');
	add_action('wp_footer', 'dog__render_custom_breadcrumbs_script', 990);
	remove_action('wp_head', 'rsd_link'); // remove really simple discovery link
	remove_action('wp_head', 'wp_generator'); // remove wordpress version
	remove_action('wp_head', 'wlwmanifest_link'); // remove wlwmanifest.xml (needed to support windows live writer)
	remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
	remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
	remove_action('wp_head', 'rest_output_link_wp_head', 10);
	remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', 'dog__init');
add_action('widgets_init', 'dog__widgets_init');
add_action('after_setup_theme', 'dog__theme_setup');
add_action('after_switch_theme', 'dog__requires');
add_filter('dog__sh_js_vars', 'dog__js_vars');
add_filter('dog__sh_js_nonces', 'dog__nonces');
dog__call_x_function('hooks');

if (is_admin()) {
	require_once(get_template_directory() . '/admin/functions.php');
}

$dog__theme_labels_file = get_stylesheet_directory() . '/_pll_labels.php';
if (is_file($dog__theme_labels_file) && function_exists('pll_register_string')) {
	require_once($dog__theme_labels_file);
}