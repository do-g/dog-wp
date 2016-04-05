<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__ENV_DEVELOPMENT', 'development');
define('DOG__ENV_PRODUCTION', 'production');

require_once(realpath(dirname(__FILE__)) . '/_functions.php');
require_once(realpath(dirname(__FILE__)) . '/_config.php');

date_default_timezone_set(DOG__TIMEZONE);

if (DOG__ENV == DOG__ENV_DEVELOPMENT) {
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

$dog__form_errors = array();
$dog__post_data = array();

function dog__value_or_empty($value, $condition) {
	return $condition ? $value : '';
}

function dog__hash($value = null) {
	return wp_hash(hash('sha256', $value ? $value : uniqid(rand(), true)));
}

function dog__safe_uri($uri, $display = false) {
	return $display ? esc_url($uri) : esc_url_raw($uri);
}

function dog__img_uri($image_file_name, $display = true) {
	return dog__safe_uri(get_stylesheet_directory_uri() . '/images/' . $image_file_name, $display);
}

function dog__js_uri($script_file_name, $display = true) {
	return dog__safe_uri(get_stylesheet_directory_uri() . '/js/' . $script_file_name . '.js', $display);
}

function dog__css_uri($style_file_name, $display = true) {
	return dog__safe_uri(get_stylesheet_directory_uri() . '/css/' . $style_file_name . '.css', $display);
}

function dog__admin_uri($path = null) {
	$path = $path ? '/' . ltrim($path, '/') : '';
	return get_stylesheet_directory_uri() . '/' . DOG__ADMIN_DIR . $path;
}

function dog__lang_uri($slug_or_id, $type = 'page', $display = true) {
	$post_name = trim($slug_or_id, '/');
	$post_id = (int) $post_name;
	if (!dog__is_default_language()) {
		if (!$post_id) {
			$post = get_page_by_path($post_name, OBJECT, $type);
			$post_id = $post->ID;
		}
		if ($post_id) {
			$trans_id = pll_get_post($post_id, dog__active_language());
			$trans = get_post($trans_id);
			$post_name = $trans->post_name;
		}
	}
	return dog__safe_uri('/' . $post_name, $display);
}

function dog__file_path($filename) {
	return get_stylesheet_directory() . '/' . ltrim($filename, '/');
}

function dog__admin_file_path($filename) {
	return dog__file_path(DOG__ADMIN_DIR . '/' . ltrim($filename, '/'));
}

function dog__get_include_contents($filepath, $data = null) {
    if (is_file($filepath)) {
        ob_start();
        include $filepath;
        return ob_get_clean();
    }
    return false;
}

function dog__debug_queries() {
	global $wpdb;
	echo "<pre>";
   	print_r($wpdb->queries);
   	echo "</pre>";
}

function dog__showContent($template) {
	set_query_var('included_template', $template);
	get_template_part('_content');
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
	if (dog__plugin_is_active('polylang')) {
		array_push($classes, 'lang--' . dog__active_language());
		if (!dog__is_default_language()) {
			$obj = get_queried_object();
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

function dog__website_language() {
	return strtolower(reset(explode('-', get_bloginfo('language'))));
}

function dog__default_language() {
	return dog__plugin_is_active('polylang') ? pll_default_language() : dog__website_language();
}

function dog__active_language() {
	return dog__plugin_is_active('polylang') ? pll_current_language() : dog__website_language();
}

function dog__is_active_language($lang) {
	return $lang == dog__active_language();
}

function dog__is_default_language($lang = null) {
	$lang = $lang ? $lang : dog__active_language();
	return $lang == dog__default_language();
}

function dog__plugin_is_active($plugin) {
	switch (strtolower($plugin)) {
		case 'polylang':
			return function_exists('pll_register_string');
	}
	return false;
}

function dog__strip_shortcode($code, $content) {
    global $shortcode_tags;
    $stack = $shortcode_tags;
    $shortcode_tags = array($code => 1);
    $content = strip_shortcodes($content);
    $shortcode_tags = $stack;
    return $content;
}

function dog__show_form_field($data) {
	set_query_var('form_field_data', $data);
	get_template_part('_form-field');
}

function dog__attributes_array_to_html($list) {
	$html = array();
	if ($list) {
		$special = array('checked', 'selected');
		foreach ($list as $key => $value) {
			if (in_array($key, $special) && !$value) {
				continue;
			}
			$key = sanitize_key($key);
			$value = esc_attr($value);
			array_push($html, "{$key}=\"{$value}\"");
		}
	}
	return ' ' . implode(" ", $html);
}

function dog__get_field_errors($field_name, $type = null) {
	global $dog__form_errors;
	return $type ? $dog__form_errors[$field_name][$type] : $dog__form_errors[$field_name];
}

function dog__get_form_errors($type = null) {
	return dog__get_field_errors(DOG__FIELD_NAME_FORM, $type);
}

function dog__set_field_error($field_name, $message, $type = 'generic') {
	global $dog__form_errors;
	$dog__form_errors[$field_name][$type] = $message;
}

function dog__set_form_error($message, $type = 'generic') {
	dog__set_field_error(DOG__FIELD_NAME_FORM, $message, $type);
}

function dog__render_form_errors() {
	get_template_part('_form-errors');
}

function dog__form_is_valid() {
	global $dog__form_errors;
	return !$dog__form_errors;
}

function dog__set_flash_message($section, $key, $value) {
	$_SESSION[DOG__SESSION_KEY_FLASH][$section][$key] = $value;
}

function dog__get_flash_message($section, $key = null) {
	$data = $_SESSION[DOG__SESSION_KEY_FLASH][$section];
	if ($key) {
		$data = sanitize_text_field($data[$key]);
		unset($_SESSION[DOG__SESSION_KEY_FLASH][$section][$key]);
	} else {
		unset($_SESSION[DOG__SESSION_KEY_FLASH][$section]);
	}
	return $data;
}

function dog__set_flash_success($key, $message) {
	dog__set_flash_message(DOG__SESSION_KEY_SUCCESS, $key, $message);
}

function dog__get_flash_success($key) {
	return dog__get_flash_message(DOG__SESSION_KEY_SUCCESS, $key);
}

function dog__set_flash_error($key, $message) {
	dog__set_flash_message(DOG__SESSION_KEY_ERROR, $key, $message);
}

function dog__get_flash_error($key) {
	return dog__get_flash_message(DOG__SESSION_KEY_ERROR, $key);
}

function dog__get_post_data($namespace) {
	global $dog__post_data, $dog__form_field_types;
	if (!$dog__post_data) {
		foreach ($_POST as $key => $value) {
			$dog__post_data[$key] = dog__safe_post_value($key, $dog__form_field_types[$namespace][$key]);
		}
	}
	return $dog__post_data;
}

function dog__safe_post_value($key_name, $type = null) {
	$value = isset($_POST[$key_name]) ? $_POST[$key_name] : null;
	$value = $value && !is_array($value) ? trim($value) : $value;
	if ($value) {
		switch ($type) {
			case DOG__POST_FIELD_TYPE_EMAIL:
				$value = sanitize_email($value);
				break;
			case DOG__POST_FIELD_TYPE_TEXTAREA:
				$value = wp_filter_nohtml_kses($value);
				break;
			case DOG__POST_FIELD_TYPE_NATURAL:
				$value = absint($value);
				break;
			case DOG__POST_FIELD_TYPE_ARRAY_NATURAL:
				foreach ($value as &$v) {
					$v = absint($v);
				}
				break;
			case DOG__POST_FIELD_TYPE_INTEGER:
				$value = intval($value);
				break;
			case DOG__POST_FIELD_TYPE_ARRAY_INTEGER:
				foreach ($value as &$v) {
					$v = intval($v);
				}
				break;
			case DOG__POST_FIELD_TYPE_ARRAY_TEXT:
				foreach ($value as &$v) {
					$v = sanitize_text_field($v);
				}
				break;
			default:
				$value = sanitize_text_field($value);
				break;
		}
	}
	return $value;
}

function dog__get_post_value($key_name, $fresh = false, $type = null) {
	global $dog__post_data;
	return $fresh ? dog__safe_post_value($key_name, $type) : ($dog__post_data[$key_name] ? $dog__post_data[$key_name] : dog__safe_post_value($key_name, $type));
}

function dog__get_post_value_or_default($key_name, $default, $default_for_blank = false, $fresh = false, $type = null) {
	$value = dog__get_post_value($key_name, $fresh, $type);
	$condition = $default_for_blank ? $value : isset($value);
	return $condition ? $value : $default;
}

function dog__field_has_value_and_valid($field_name) {
	return dog__get_post_value($field_name) && !dog__get_field_errors($field_name);
}

function dog__is_post($key = null) {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && ($key ? isset($_POST[$key]) : 1);
}

function dog__is_get($key = null) {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && ($key ? sanitize_text_field($key) : 1);
}

function dog__get_option($name, $default = null) {
	return get_option(DOG__PREFIX_ADMIN . $name, $default);
}

function dog__update_option($name, $value, $autoload = false) {
	return update_option(DOG__PREFIX_ADMIN . $name, $value, $autoload);
}

function dog__redirect($location, $status = null) {
	wp_redirect($location, $status);
	exit;
}

function dog__safe_redirect($location, $status = null) {
	wp_safe_redirect($location, $status);
	exit;
}

function dog__uri($uri, $exclude_query_string = false, $trim_end_slash = false, $display = false) {
	if ($exclude_query_string) {
		$parts = explode('?', $uri);
		$uri = $parts[0];
	}
	$uri = $trim_end_slash ? ($uri != '/' ? rtrim($uri, '/') : $uri) : $uri;
	return dog__safe_uri($uri, $display);
}

function dog__current_uri($exclude_query_string = false, $trim_end_slash = false, $display = false) {
	return dog__uri($_SERVER['REQUEST_URI'], $exclude_query_string, $trim_end_slash);
}

function dog__contact_uri($lang = null) {
	global $dog__contact_slugs;
	$lang = $lang ? $lang : dog__active_language();
	return dog__replace_template_vars(DOG__URI_TEMPLATE_CONTACT, array('slug' => $dog__contact_slugs[$lang]));
}

function dog__whitelist_fields($allowed) {
	$default = array('_wp_http_referer', DOG__NONCE_NAME, DOG__HONEYPOT_TIMER_NAME, DOG__HONEYPOT_JAR_NAME);
	$allowed = array_merge($allowed, $default);
	$_POST = array_intersect_key($_POST, array_flip($allowed));
}

function dog__validate_required_fields($required) {
	foreach ($required as $field_name) {
		if (dog__get_post_value($field_name) == '') {
			dog__set_field_error($field_name, dog__txt('Acest câmp este obligatoriu'), DOG__FIELD_ERROR_REQUIRED);
		}
	}
}

function dog__validate_regex_field($field_name, $regex, $error_message = null) {
	if (dog__field_has_value_and_valid($field_name)) {
		$error_message = $error_message ? $error_message : dog__txt('Valoarea introdusa este invalida');
		if ($regex == DOG__REGEX_KEY_EMAIL) {
			$valid = is_email(dog__get_post_value($field_name, false, DOG__POST_FIELD_TYPE_EMAIL));
		} else {
			$valid = preg_match($regex, dog__get_post_value($field_name));
		}
		if (!$valid) {
			dog__set_field_error($field_name, $error_message, DOG__FIELD_ERROR_REGEX);
		}
	}
}

function dog__get_special_attr_value($key, $condition) {
	return $condition ? $key : false;
}

function dog__get_checked_attr_value($condition) {
	return dog__get_special_attr_value('checked', $condition);
}

function dog__get_selected_attr_value($condition) {
	return dog__get_special_attr_value('selected', $condition);
}

function dog__get_nonce_action($action) {
	return DOG__NONCE_ACTION_BASE . dog__hash($action);
}

function dog__nonce_field($action) {
	wp_nonce_field(dog__get_nonce_action($action), DOG__NONCE_NAME);
}

function dog__validate_nonce($action, $redirect_to = null) {
	$nonce = dog__get_post_value(DOG__NONCE_NAME);
	if (!$nonce || !wp_verify_nonce($nonce, dog__get_nonce_action($action))) {
		if ($redirect_to) {
			dog__set_flash_error('form', dog__txt('Eroare la validarea formularului'));
			dog__redirect($redirect_to);
		} else {
			dog__set_form_error(dog__txt('Eroare la validarea formularului'));
		}
	}
}

function dog__nonce_var_key($name) {
	return dog__string_to_key($name, DOG__NONCE_VAR_PREFIX);
}

function dog__validate_honeypot() {
	if (!DOG__HONEYPOT_ENABLED) {
		return true;
	}
	if (dog__get_post_value(DOG__HONEYPOT_JAR_NAME)) {
		dog__set_form_error(dog__txt('Execuție suspectă sau neautorizată [1]'));
		return;
	}
	$timer = dog__get_post_value(DOG__HONEYPOT_TIMER_NAME);
	if (!$timer || microtime(true) - $timer < 2) {
		dog__set_form_error(dog__txt('Execuție suspectă sau neautorizată [2]'));
	}
}

function dog__honeypot_field() {
	if (DOG__HONEYPOT_ENABLED) {
		get_template_part('_honeypot');
	}
}

function dog__string_to_key($value, $prefix = null, $suffix = null) {
	$value = strtolower($value);
	$value = preg_replace('/[-]+/', '_', $value);
	$value = preg_replace('/\s+/', '_', $value);
	$value = preg_replace('/[^a-z0-9_]/', '', $value);
  	return ($prefix ? $prefix : '') . $value . ($suffix ? $suffix : '');
}

function dog__get_email_template($name, $lang = null) {
	$tpl_name = $name . ($lang ? "--{$lang}" : '') . '.tpl';
	return file_get_contents(get_stylesheet_directory_uri() . "/tpl/{$tpl_name}");
}

function dog__replace_template_vars($template, $data) {
	if ($template && $data) {
		foreach ($data as $key => $value) {
			$template = str_replace('${' . $key . '}', $value, $template);
		}
	}
	return $template;
}

function dog__site_domain($strict = false) {
	$parts = explode('//', trim(site_url(), '/'));
	if ($strict) {
		$parts = explode('.', $parts[1]);
		$parts = array_slice($parts, -2);
		return implode('.', $parts);
	} else {
		return $parts[1];
	}
}

function dog__get_mail_errors() {
	global $ts_mail_errors, $phpmailer;
	if (!isset($ts_mail_errors)) {
		$ts_mail_errors = array();
	}
	if (isset($phpmailer)) {
		$ts_mail_errors[] = $phpmailer->ErrorInfo;
	}
	return $ts_mail_errors;
}

function dog__send_form_email($namespace, &$errors = array()) {
	$domain = dog__site_domain(true);
	$template_data = dog__get_post_data($namespace);
	$template_data['website_domain'] = dog__site_domain();
	$template_data['website_url'] = site_url();
	$template_data['website_title'] = get_bloginfo('name');
	$template = dog__get_email_template('contact-email-receiver');
	$template = dog__replace_template_vars($template, $template_data);
	$to = get_option('admin_email');
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Sender: noreply@' . $domain,
		'From: ' . $template_data['website_title'] . ' <noreply@' . $domain . '>',
		'Reply-To: ' . $template_data['nume'] . ' <' . $template_data['email'] . '>',
	);
	$result = wp_mail($to, dog__txt('Ai primit un mesaj de contact', dog__default_language()), $template, $headers);
	if (!$result) {
		$errors = dog__get_mail_errors();
		return false;
	}
	if ($template_data['email']) {
		$template = dog__get_email_template('contact-email-sender', dog__active_language());
		$template = dog__replace_template_vars($template, $template_data);
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'Sender: noreply@' . $domain,
			'From: ' . $template_data['website_title'] . ' <noreply@' . $domain . '>',
		);
		$result = wp_mail($template_data['email'], dog__txt('Ai trimis un mesaj de contact'), $template, $headers);
		if (!$result) {
			$errors = dog__get_mail_errors();
			return false;
		}
	}
	return true;
}

function dog__merge_form_field_classes($default_class, $user_class) {
	if ($user_class && !is_array($user_class)) {
		$user_class = explode(' ', $user_class);
	}
	$user_class = $user_class ? $user_class : array();
	$class = array_merge($default_class, $user_class);
	$class = array_map('sanitize_html_class', $class);
	$class = implode(' ', $class);
	return $class;
}

function dog__post_thumbnail() {
	$id = get_post_thumbnail_id();
	return esc_url($id ? reset(wp_get_attachment_image_src($id, 'full')) : dog__img_uri(DOG__POST_THUMBNAIL_DEFAULT));
}

function dog__txt($label, $lang = null) {
	return dog__plugin_is_active('polylang') ? ($lang ? pll_translate_string($label, $lang) : pll__($label)) : $label;
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

function dog__log($data, $file = null) {
	if (is_array($data) || is_object($data)) {
		$data = var_export($data, true);
	}
	if ($file) {
		$file = get_theme_root() . '/../' . $file . '.log';
		return @fwrite(@fopen($file, 'a'), $data . "\n");
	} else {
		return error_log($data);
	}
}

function dog__set_transient($name, $value, $expiration, $extra_data = array()) {
	set_transient($name, $value, $expiration);
	foreach ($extra_data as $key => $value) {
		$key = substr($key, 0, 7) . '_' . $name;
		update_option(DOG_ADMIN__TRANSIENT_DB_PREFIX . $key, $value, false);
	}
}

function dog__delete_transient($name, $extra_data = array()) {
	delete_transient($name);
	foreach ($extra_data as $key) {
		$key = substr($key, 0, 7) . '_' . $name;
		delete_option(DOG_ADMIN__TRANSIENT_DB_PREFIX . $key);
	}
}

function dog__get_output_cache_transient_hash($transient) {
	$transient = str_replace(DOG_ADMIN__TRANSIENT_TIMEOUT_DB_PREFIX, '', $transient);
	$transient = str_replace(DOG__TRANSIENT_OUTPUT_CACHE_PREFIX, '', $transient);
	return $transient;
}

function dog__get_uri_cache_key() {
	$key = DOG__TRANSIENT_OUTPUT_CACHE_PREFIX . md5(dog__current_uri(false, true));
	return $key;
}

function dog__save_output_cache($buffer) {
	global $dog__current_template;
	if ($dog__current_template && !is_404()) {
		dog__set_transient(dog__get_uri_cache_key(), $buffer, HOUR_IN_SECONDS * dog__get_option(DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES), array('url' => dog__current_uri(false, true)));
	}
	return false;
}

function dog__check_output_cache() {
	global $dog__output_cache_ignore_uri;
	if (dog__is_get() && dog__get_option(DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED)) {
		$cache_key = dog__get_uri_cache_key();
		if (false === ($cache = get_transient($cache_key))) {
			$uri = dog__current_uri(false, true);
			foreach ($dog__output_cache_ignore_uri as $i) {
				if (stripos($uri, $i) !== false) {
					return;
				}
			}
			ob_start('dog__save_output_cache');
		} else {
			header('DOG output cache hash: ' . dog__get_output_cache_transient_hash($cache_key));
			echo $cache;
			exit;
		}
	}
}

function dog__enqueue_assets_high_priority() {
	wp_deregister_script('jquery');
	wp_deregister_script('wp-embed');
	dog__call_local_function(__FUNCTION__);
}

function dog__enqueue_assets_low_priority() {
	wp_enqueue_style('vendor', dog__css_uri('vendor'));
	wp_enqueue_style('styles', dog__css_uri('styles'), array('vendor'));
	wp_enqueue_script('vendor', dog__js_uri('vendor'), null, null, true);
	wp_enqueue_script('doglib', dog__js_uri('lib'), array('vendor'), null, true);
	$js_vars = dog__extend_with('js_vars', dog__js_vars());
	wp_localize_script('doglib', 'dog__wp', $js_vars);
	wp_enqueue_script('scripts', dog__js_uri('scripts'), array('doglib'), null, true);
	dog__call_local_function(__FUNCTION__);
}

function dog__js_vars() {
	return array(
		'theme_url' => get_bloginfo('template_url'),
		'ajax_url' => admin_url('admin-ajax.php'),
		'DOG__NONCE_NAME' => DOG__NONCE_NAME,
		'DOG__NONCE_VAR_PREFIX' => DOG__NONCE_VAR_PREFIX,
		'DOG__WP_ACTION_AJAX_CALLBACK' => DOG__WP_ACTION_AJAX_CALLBACK,
		'DOG__AJAX_RESPONSE_STATUS_SUCCESS' => DOG__AJAX_RESPONSE_STATUS_SUCCESS,
		'DOG__AJAX_RESPONSE_STATUS_ERROR' => DOG__AJAX_RESPONSE_STATUS_ERROR,
		'DOG__ALERT_KEY_SERVER_FAILURE' => dog__alert_message(DOG__ALERT_KEY_SERVER_FAILURE),
		'DOG__ALERT_KEY_CLIENT_FAILURE' => dog__alert_message(DOG__ALERT_KEY_CLIENT_FAILURE),
	);
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
	dog__call_local_function('register_sidebar');
}

function dog__theme_setup() {
	add_theme_support('html5', array('search-form', 'gallery', 'caption'));
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('custom-header');
	add_editor_style('css/editor-styles.css');
	register_nav_menu('location-main-menu', __('Locație Meniu Principal'));
	dog__call_local_function('add_custom_image_sizes');
	add_filter('image_size_names_choose', 'dog__custom_image_sizes');
	dog__call_local_function(__FUNCTION__);
}

function dog__init() {
	add_post_type_support('page', 'excerpt');
	register_taxonomy_for_object_type('post_tag', 'page');
	dog__call_local_function(__FUNCTION__);
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
			$sizes[$id] = ucfirst(str_replace(array('_', '-'), ' ', $id));
		}
	}
	return $sizes;
}

function dog__alert_message($key, $params = null) {
	global $dog__alert_messages;
	return dog__replace_template_vars($dog__alert_messages[$key], $params);
}

function dog__alert_message_code($key, $code) {
	return dog__alert_message($key, array('code' => $code));
}

function dog__ajax_response($data, $success = true, $extra = array()) {
	$response = new stdClass();
	$response->status = $success ? DOG__AJAX_RESPONSE_STATUS_SUCCESS : DOG__AJAX_RESPONSE_STATUS_ERROR;
	$response->data = $data;
	if ($extra) {
		foreach ($extra as $key => $val) {
			$response->$key = $val;
		}
	}
	return $response;
}

function dog__ajax_response_ok($data, $info = array()) {
	return dog__ajax_response($data, true, $info);
}

function dog__ajax_response_error($info = array()) {
	return dog__ajax_response(null, false, $info);
}

function dog__ajax_handler() {
	$nonce_key = DOG__NONCE_NAME;
	$nonce = dog__get_post_value($nonce_key);
	$method = dog__get_post_value('method');
	$function = DOG__PREFIX_AJAX . dog__string_to_key($method);
	if (!check_ajax_referer($method, $nonce_key, false)) {
		$response = dog__ajax_response_error(array('message' => dog__alert_message_code(DOG__ALERT_KEY_RESPONSE_ERROR, DOG__AJAX_RESPONSE_CODE_INVALID_NONCE)));
	} else if (!$method || !function_exists($function)) {
		$response = dog__ajax_response_error(array('message' => dog__alert_message_code(DOG__ALERT_KEY_RESPONSE_ERROR, DOG__AJAX_RESPONSE_CODE_INVALID_METHOD)));
	} else {
		$response = call_user_func($function);
	}
	$response->$nonce_key = $nonce;
	wp_send_json($response);
}

function dog__call_local_function($function_name, $params = null) {
	$function_name = str_replace(DOG__PREFIX_LOCAL, '', $function_name);
	$function_name = str_replace(DOG__PREFIX_ADMIN, '', $function_name);
	$function_name = str_replace(DOG__PREFIX, '', $function_name);
	$function_name = DOG__PREFIX_LOCAL . $function_name;
	if (function_exists($function_name)) {
		return call_user_func($function_name, $params);
	}
}

function dog__extend_with($function_name, $default = null, $params = null) {
	$local_value = dog__call_local_function($function_name, $params);
	if ($local_value) {
		return is_array($default) ? array_merge($default, $local_value) : $local_value;
	}
	return $default;
}

if (!is_admin()) {
	add_filter('json_enabled', '__return_false');
	add_filter('json_jsonp_enabled', '__return_false');
	add_filter('use_default_gallery_style', '__return_false');
	add_filter('template_include', 'dog__override_template', 99);
	add_filter('clean_url', 'dog__async_defer', 11, 1);
	add_action('after_setup_theme', 'dog__check_output_cache');
	add_action('wp_enqueue_scripts', 'dog__enqueue_assets_high_priority', 0);
	add_action('wp_enqueue_scripts', 'dog__enqueue_assets_low_priority', 99990);
	add_action('pre_get_posts', 'dog__enable_query_tags');
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
add_action('wp_ajax_nopriv_' . DOG__WP_ACTION_AJAX_CALLBACK, 'dog__ajax_handler');
add_action('wp_ajax_' . DOG__WP_ACTION_AJAX_CALLBACK, 'dog__ajax_handler');
dog__call_local_function('hooks');

if (is_admin()) {
	require_once(realpath(dirname(__FILE__)) . '/admin/theme-options.php');
}

$dog__pll_labels_file = realpath(dirname(__FILE__)) . '/_pll_labels.php';
if (is_file($dog__pll_labels_file) && dog__plugin_is_active('polylang')) {
	require_once($dog__pll_labels_file);
}