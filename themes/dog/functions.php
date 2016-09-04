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

$dog__schemaorg_page_types = dog__extend_with('schemaorg_page_types', array(
	'AboutPage' => array('despre', 'about'),
	'ContactPage' => array('contact', 'contact-us'),
	'CollectionPage' => array(),
	'ItemPage' => array(),
	'ProfilePage' => array(),
	'SearchResultsPage' => array()
));

$dog__template_override = dog__extend_with('template_override', array(
	'page-contact' => array('contact-us')
));

function dog__include_template($filename, $tpl_data = null) {
	include(locate_template($filename . '.php'));
}

function dog__loop_content($template) {
	dog__include_template('_content-loop', array('template' => $template));
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
	return dog__override_with(__FUNCTION__, dog__contact_url() . '?' . uniqid() . '=' . time());
}

/**
 * 	$custom_data = array(
 *	 	'receiver' => array(
 *			'template_vars' => array(),
 *			'headers' => array(),
 *			'email' => '',
 *			'subject' => '',
 *			'template' => '',
 *			'template_language' => '',
 *		),
 *		'sender'    => array(
 *			'email_key' => '',
 *			'ignore' => false,
 *			'template_vars' => array(),
 *			'headers' => array(),
 *			'subject' => '',
 *			'template' => '',
 *			'template_language' => '',
 *		),
 *	);
 */
function dog__send_form_mail_standard($custom_data = null) {
	$main_domain = dog__site_domain(true);
	$full_domain = dog__site_domain();
	$site_title = get_bloginfo('name');
	$site_title_safe = str_replace(',', '', $site_title);

	$default_template_vars = array_merge(Dog_Form::get_post_data(), array(
		'website_domain' => $full_domain,
		'website_url' 	 => site_url(),
		'website_title'  => $site_title,
	));

	$custom_template_vars = dog__value_or_default($custom_data['receiver']['template_vars'], array());
	$template_vars = array_merge($default_template_vars, $custom_template_vars);

	$custom_headers = dog__value_or_default($custom_data['receiver']['headers'], array());
	$headers = array_merge(array(
		'sender' => 'noreply@' . $main_domain,
		'from' => array(
			'name' => $site_title_safe,
			'email' => 'noreply@' . $main_domain,
		),
		'reply' => array(
			'name' => $template_vars['contact_name'],
			'email' => $template_vars['contact_email'],
		),
	), $custom_headers);

	$custom_email_key = dog__value_or_default($custom_data['sender']['email_key'], 'contact_email');
	if (!$template_vars[$custom_email_key]) {
		unset($headers['reply']);
	}

	$recipient = dog__value_or_default($custom_data['receiver']['email'], (defined('DOG__EMAIL_CONTACT') && DOG__EMAIL_CONTACT ? DOG__EMAIL_CONTACT : get_option('admin_email')));

	$subject = dog__value_or_default($custom_data['receiver']['subject'], dog__txt('Ai primit un mesaj de contact', null, dog__default_language()));

	$template = dog__theme_email_path(dog__value_or_default($custom_data['receiver']['template'], 'contact-email-receiver'));

	$result = dog__send_mail($recipient, $subject, $headers, $template, $template_vars, $custom_data['receiver']['template_language'], $custom_data['receiver']['attachments']);
	if (is_array($result)) {
		return $result;
	}

	if ($template_vars[$custom_email_key] && !$custom_data['sender']['ignore']) {
		$custom_template_vars = dog__value_or_default($custom_data['sender']['template_vars'], array());
		$template_vars = array_merge($default_template_vars, $custom_template_vars);

		$custom_headers = dog__value_or_default($custom_data['sender']['headers'], array());
		$headers['reply'] = array(
			'name' => $site_title_safe,
			'email' => $recipient,
		);
		$headers = array_merge($headers, $custom_headers);

		$subject = dog__value_or_default($custom_data['sender']['subject'], dog__txt('Ai trimis un mesaj de contact'));

		$template = dog__theme_email_path(dog__value_or_default($custom_data['sender']['template'], 'contact-email-sender'));

		$template_language = dog__value_or_default($custom_data['sender']['template_language'], dog__active_language());

		$result = dog__send_mail($template_vars[$custom_email_key], $subject, $headers, $template, $template_vars, $template_language, $custom_data['sender']['attachments']);
		if (is_array($result)) {
			return $result;
		}
	}
	return true;
}

function dog__schema_page_type() {
	global $dog__schemaorg_page_types, $post;
	$current = get_queried_object();
	if ($current->cat_ID) {
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

$dog__current_template;
function dog__override_template($template) {
	global $dog__template_override, $dog__current_template;
	if ($dog__template_override) {
		$current = get_queried_object();
		$slug = $current->slug ? $current->slug : $current->post_name;
		foreach ($dog__template_override as $override => $slugs) {
			if (in_array($slug, $slugs)) {
				$new_template = locate_template(array($override . '.php'));
				if ($new_template) {
					$dog__current_template = $new_template;
					return $new_template;
				}
				break;
			}
		}
	}
	$dog__current_template = $template;
	return $template;
}

function dog__enqueue_assets_high_priority() {
	wp_deregister_script('jquery');
	wp_deregister_script('wp-embed');
	dog__call_x_function(__FUNCTION__);
}

function dog__enqueue_assets_low_priority() {
	$dog_af_plugin_active = class_exists('Dog_Asset_Features');
	$cached_styles = $dog_af_plugin_active && Dog_Asset_Features::has_cached_styles();
	if ($cached_styles !== false) {
		wp_enqueue_style('cache_styles', $cached_styles, null, null);
	}

	$cached_scripts = $dog_af_plugin_active && Dog_Asset_Features::has_cached_scripts();
	if ($cached_scripts !== false) {
		wp_enqueue_script('cache_script', $cached_scripts, null, null, true);
		wp_localize_script('cache_script', 'dog__sh', Dog_Shared::get_js_vars());
	}

	dog__call_x_function(__FUNCTION__, array('cached_styles' => $cached_styles, 'cached_scripts' => $cached_scripts));
}

function dog__dequeue_styles() {
	wp_dequeue_style('yoast-seo-adminbar');
	dog__call_x_function(__FUNCTION__);
}

function dog__js_vars($vars) {
	return array_merge($vars, dog__extend_with('js_vars', array()));
}

function dog__nonces($nonces) {
	$list = dog__extend_with('nonces', array());
	return array_merge($nonces, dog__to_nonces($list));
}

function dog__async_defer($url) {
	if (strpos($url, '#async') !== false) {
		return str_replace('#async', '', $url) . "' async defer='";
	}
	return $url;
}

function dog__enable_query_tags($wp_query) {
	if ($wp_query->get('tag')) {
		$wp_query->set('post_type', 'any');
	}
}

function dog__widgets_init() {
	register_sidebar(array(
		'name'          => 'Sidebar',
		'id'            => 'sidebar',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	));
	dog__call_x_function('register_sidebar');
}

function dog__theme_setup() {
	add_theme_support('html5', array('search-form', 'gallery', 'caption'));
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('custom-header');
	add_editor_style('css/editor.css');
	add_filter('image_size_names_choose', 'dog__custom_image_sizes');
	dog__call_x_function(__FUNCTION__);
}

function dog__init() {
	add_post_type_support('page', 'excerpt');
	register_taxonomy_for_object_type('post_tag', 'page');
	register_taxonomy_for_object_type('post_tag', 'attachment');
	dog__call_x_function(__FUNCTION__);
	if(!session_id()) {
        session_start();
    }
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
	add_action('wp_enqueue_scripts', 'dog__enqueue_assets_high_priority', 0);
	add_action('wp_enqueue_scripts', 'dog__enqueue_assets_low_priority', 99990);
	add_action('pre_get_posts', 'dog__enable_query_tags');
	add_action('wp_print_styles', 'dog__dequeue_styles', 99999);
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
add_action('widgets_init', 'dog__widgets_init');
add_action('after_setup_theme', 'dog__theme_setup');
add_action('init', 'dog__init');
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