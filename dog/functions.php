<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
require_once(get_template_directory() . '/_config.php');

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

$dog__output_cache_ignore_uri = dog__extend_with('output_cache_ignore_uri', array('/wp-cron.php'));

$dog__form_field_types = dog__extend_with('form_field_types', array(
	DOG__NAMESPACE_CONTACT => array(
		'nume' => DOG__POST_FIELD_TYPE_TEXT,
		'email' => DOG__POST_FIELD_TYPE_EMAIL,
		'mesaj' => DOG__POST_FIELD_TYPE_TEXTAREA
	),
	DOG_ADMIN__NAMESPACE_MINIFY => array(
		DOG__OPTION_MINIFY_STYLES => DOG__POST_FIELD_TYPE_TEXTAREA,
		DOG__OPTION_MINIFY_SCRIPTS => DOG__POST_FIELD_TYPE_TEXTAREA
	)
));

$dog_admin__sections = dog__extend_with('admin_sections', array(
	DOG_ADMIN__SECTION_MINIFY => dog__txt('Comprimare fișiere'),
	DOG_ADMIN__SECTION_GENERATE_LABELS => dog__txt('Etichete'),
	DOG_ADMIN__SECTION_UPDATE => dog__txt('Actualizări temă'),
	DOG_ADMIN__SECTION_SECURITY => dog__txt('Verificări securitate'),
));

$dog_admin__custom_nonces = dog__extend_with('nonces', array(
	DOG_ADMIN__NONCE_UPDATE_CHECK,
	DOG_ADMIN__NONCE_UPDATE_INFO,
	DOG_ADMIN__NONCE_REFRESH_MINIFY,
	DOG_ADMIN__NONCE_DELETE_MINIFY,
));

$dog__alert_messages = dog__extend_with('alert_messages', array(
	DOG__ALERT_KEY_RESPONSE_ERROR => dog__txt('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi procesat. Codul de eroare este ${code}'),
	DOG__ALERT_KEY_SERVER_FAILURE => dog__txt('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi procesat'),
	DOG__ALERT_KEY_CLIENT_FAILURE => dog__txt('Sistemul a întâmpinat o eroare. Cererea nu poate fi trimisă'),
	DOG__ALERT_KEY_FORM_INVALID => dog__txt('Formularul nu poate fi validat. Te rugăm să corectezi erorile'),
	DOG__ALERT_KEY_EMPTY_SELECTION => dog__txt('Trebuie să selectezi cel puțin o înregistrare'),
));

$dog__form_errors = array();
$dog__post_data = array();
$dog__update_check_done = false;

/***** utilitary methods *****/

function dog__is_env_dev() {
	return DOG__ENV == DOG__ENV_DEVELOPMENT;
}

function dog__is_env_pro() {
	return DOG__ENV == DOG__ENV_PRODUCTION;
}

function dog__value_or_empty($value, $condition) {
	return $condition ? $value : '';
}

function dog__safe_url($uri, $display = false) {
	return $display ? esc_url($uri) : esc_url_raw($uri);
}

function dog__debug_queries() {
	global $wpdb;
	echo "<pre>";
   	print_r($wpdb->queries);
   	echo "</pre>";
}

function dog__theme_version($theme_name = null) {
	$theme = wp_get_theme($theme_name);
	return $theme->get('Version');
}

function dog__parent_theme_version() {
	return dog__theme_version(DOG__THEME_NAME);
}

function dog__string_to_html_tag($string, $tag) {
	return "<{$tag}>{$string}</{$tag}>";
}

function dog__search_files($folder, $pattern) {
    $dir = new RecursiveDirectoryIterator($folder);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
    $fileList = array();
    foreach($files as $file) {
        $fileList = array_merge($fileList, $file);
    }
    return $fileList;
}

function dog__array_merge_unique($arr1, $arr1) {
	return array_unique(array_merge($arr1, $arr1));
}

/***** url methods *****/

function dog__theme_url_fragment($file_name) {
	return '/' . ltrim($file_name, '/');
}

function dog__theme_url($file_name, $display = true) {
	return dog__safe_url(get_stylesheet_directory_uri() . dog__theme_url_fragment($file_name), $display);
}

function dog__parent_theme_url($file_name, $display = true) {
	return dog__safe_url(get_template_directory_uri() . dog__theme_url_fragment($file_name), $display);
}

function dog__asset_url($file_name, $display = true) {
	return DOG__STATIC_ASSETS_URL ? dog__safe_url(DOG__STATIC_ASSETS_URL . dog__theme_url_fragment($file_name), $display) : dog__theme_url($file_name, $display);
}

function dog__parent_asset_url($file_name, $display = true) {
	return DOG__STATIC_PARENT_ASSETS_URL ? dog__safe_url(DOG__STATIC_PARENT_ASSETS_URL . dog__theme_url_fragment($file_name), $display) : dog__parent_theme_url($file_name, $display);
}

function dog__img_url_fragment($image_file_name) {
	return dog__theme_url_fragment('images/' . ltrim($image_file_name, '/'));
}

function dog__img_url($image_file_name, $display = true) {
	return dog__asset_url(dog__img_url_fragment($image_file_name), $display);
}

function dog__parent_img_url($image_file_name, $display = true) {
	return dog__parent_asset_url(dog__img_url_fragment($image_file_name), $display);
}

function dog__js_url_fragment($script_file_name) {
	return dog__theme_url_fragment('js/' . ltrim($script_file_name, '/') . '.js');
}

function dog__js_url($script_file_name, $display = true) {
	return dog__asset_url(dog__js_url_fragment($script_file_name), $display);
}

function dog__parent_js_url($script_file_name, $display = true) {
	return dog__parent_asset_url(dog__js_url_fragment($script_file_name), $display);
}

function dog__css_url_fragment($style_file_name) {
	return dog__theme_url_fragment('css/' . ltrim($style_file_name, '/') . '.css');
}

function dog__css_url($style_file_name, $display = true) {
	return dog__asset_url(dog__css_url_fragment($style_file_name), $display);
}

function dog__parent_css_url($style_file_name, $display = true) {
	return dog__parent_asset_url(dog__css_url_fragment($style_file_name), $display);
}

function dog__admin_url_fragment($file_name) {
	return dog__theme_url_fragment(DOG__ADMIN_DIR . '/' . ltrim($file_name, '/'));
}

function dog__admin_url($file_name, $display = true) {
	return dog__theme_url(dog__admin_url_fragment($file_name), $display);
}

function dog__parent_admin_url($file_name, $display = true) {
	return dog__parent_theme_url(dog__admin_url_fragment($file_name), $display);
}

function dog__compressed_asset_url_fragment($asset_name) {
	return dog__theme_url_fragment(DOG__COMPRESSED_ASSET_DIR . '/' . ltrim($asset_name, '/'));
}

function dog__compressed_asset_url($asset_name, $display = true) {
	return dog__asset_url(dog__compressed_asset_url_fragment($asset_name), $display);
}

/***** path methods *****/

function dog__file_path($filename) {
	return get_stylesheet_directory() . dog__theme_url_fragment($filename);
}

function dog__parent_file_path($filename) {
	return get_template_directory() . dog__theme_url_fragment($filename);
}

function dog__admin_file_path($filename) {
	return dog__file_path(dog__admin_url_fragment($filename));
}

function dog__parent_admin_file_path($filename) {
	return dog__parent_file_path(dog__admin_url_fragment($filename));
}

/***** language methods *****/

function dog__lang_url($slug_or_id, $type = 'page', $display = true) {
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
	return dog__safe_url('/' . $post_name, $display);
}

function dog__extract_labels($string) {
	$labels = array();
	preg_match_all("/dog__txt\('(.*?)'/", $string, $matches1);
	$labels = array_merge($labels, $matches1[1]);
	preg_match_all("/dog__txt_attr\('(.*?)'/", $string, $matches2);
	$labels = array_merge($labels, $matches2[1]);
	preg_match_all("/dog__txt_raw\('(.*?)'/", $string, $matches3);
	$labels = array_merge($labels, $matches3[1]);
	return $labels;
}

function dog__extract_file_labels($filepath) {
	return dog__extract_labels(file_get_contents($filepath));
}

function dog__include_template($filename, $tpl_data = null) {
	include(locate_template($filename . '.php'));
}

function dog__get_include_contents($filepath, $tpl_data = null) {
    if (is_file($filepath)) {
        ob_start();
        include $filepath;
        return ob_get_clean();
    }
    return false;
}

function dog__show_content($template) {
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
	dog__include_template('_form-field', array('form_field_data' => $data));
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
	dog__include_template('_form-errors');
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

function dog__get_post_value($key_name, $type = null, $fresh = false) {
	global $dog__post_data;
	return $fresh ? dog__safe_post_value($key_name, $type) : ($dog__post_data[$key_name] ? $dog__post_data[$key_name] : dog__safe_post_value($key_name, $type));
}

function dog__get_post_value_or_default($key_name, $default, $default_for_blank = false, $type = null, $fresh = false) {
	$value = dog__get_post_value($key_name, $type, $fresh);
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
	return get_option(DOG__PREFIX . $name, $default);
}

function dog__update_option($name, $value, $autoload = false) {
	return update_option(DOG__PREFIX . $name, $value, $autoload);
}

function dog__delete_option($name) {
	return delete_option(DOG__PREFIX . $name);
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
	return dog__safe_url($uri, $display);
}

function dog__current_uri($exclude_query_string = false, $trim_end_slash = false, $display = false) {
	return dog__uri($_SERVER['REQUEST_URI'], $exclude_query_string, $trim_end_slash);
}

function dog__contact_url() {
	return dog__lang_url('contact');
}

function dog__contact_success_url() {
	return dog__override_with(__FUNCTION__, dog__contact_url() . '?' . uniqid() . '=' . time());
}

function dog__whitelist_fields($allowed) {
	$default = array('_wp_http_referer', DOG__NC_NAME, DOG__HP_TIMER_NAME, DOG__HP_JAR_NAME);
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
			$valid = is_email(dog__get_post_value($field_name, DOG__POST_FIELD_TYPE_EMAIL, false));
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

function dog__nonce_field($action) {
	wp_nonce_field(dog__string_to_key($action), DOG__NC_NAME);
}

function dog__validate_nonce($action, $redirect_to = null) {
	$nonce = dog__get_post_value(DOG__NC_NAME);
	if (!$nonce || !wp_verify_nonce($nonce, dog__string_to_key($action))) {
		if ($redirect_to) {
			dog__set_flash_error('form', dog__txt('Eroare la validarea formularului'));
			dog__redirect($redirect_to);
		} else {
			dog__set_form_error(dog__txt('Eroare la validarea formularului'));
		}
	}
}

function dog__nonce_var_key($name) {
	return dog__string_to_key($name, DOG__NC_VAR_PREFIX);
}

function dog__validate_honeypot() {
	if (!DOG__HONEYPOT_ENABLED) {
		return true;
	}
	if (dog__get_post_value(DOG__HP_JAR_NAME)) {
		dog__set_form_error(dog__txt('Execuție suspectă sau neautorizată [1]'));
		return;
	}
	$timer = dog__get_post_value(DOG__HP_TIMER_NAME);
	if (!$timer || microtime(true) - $timer < DOG__HONEYPOT_TIMER_SECONDS) {
		dog__set_form_error(dog__txt('Execuție suspectă sau neautorizată [2]'));
	}
}

function dog__honeypot_field() {
	if (DOG__HONEYPOT_ENABLED) {
		dog__include_template('_honeypot');
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
	$result = wp_mail($to, dog__txt('Ai primit un mesaj de contact', null, dog__default_language()), $template, $headers);
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
	return esc_url($id ? reset(wp_get_attachment_image_src($id, 'full')) : dog__img_url(DOG__POST_THUMBNAIL_DEFAULT));
}

function dog__txt_raw($label, $vars = null, $lang = null) {
	$txt = dog__plugin_is_active('polylang') ? ($lang ? pll_translate_string($label, $lang) : pll__($label)) : $label;
	if ($vars && is_array($vars)) {
		$txt = dog__replace_template_vars($txt, $vars);
	}
	return $txt;
}

function dog__txt($label, $vars = null, $lang = null) {
	return esc_html(dog__txt_raw($label, $vars, $lang));
}

function dog__txt_attr($label, $vars = null, $lang = null) {
	return esc_attr(dog__txt_raw($label, $vars, $lang));
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

function dog__compressed_asset_dir() {
	return get_stylesheet_directory() . '/' . DOG__COMPRESSED_ASSET_DIR;
}

function dog__has_cached_assets($type, $version) {
	if (!$version) {
		return false;
	}
	$cache_file_name = $version . '.' . $type;
	$cache_file_path = dog__compressed_asset_dir() . '/' . $cache_file_name;
	if (!is_file($cache_file_path)) {
		return false;
	}
	return dog__compressed_asset_url($cache_file_name);
}

function dog__has_cached_styles() {
	return dog__has_cached_assets('css', dog__get_option(DOG__OPTION_MINIFY_STYLES_VERSION));
}

function dog__has_cached_scripts() {
	return dog__has_cached_assets('js', dog__get_option(DOG__OPTION_MINIFY_SCRIPTS_VERSION));
}

function dog__enqueue_assets_high_priority() {
	wp_deregister_script('jquery');
	wp_deregister_script('wp-embed');
	dog__call_x_function(__FUNCTION__);
}

function dog__enqueue_assets_low_priority() {
	$js_vars = dog__extend_with('js_vars', dog__js_vars());
	$nonces = dog__nonces();

	$cached_styles = dog__has_cached_styles();
	if ($cached_styles !== false) {
		wp_enqueue_style('cache_styles', $cached_styles, null, null);
	} else {
		wp_enqueue_style('base_styles', dog__parent_css_url('shared'), null, null);
	}

	$cached_scripts = dog__has_cached_scripts();
	if ($cached_scripts !== false) {
		wp_enqueue_script('cache_script', $cached_scripts, null, null, true);
		wp_localize_script('cache_script', 'dog__wp', array_merge($js_vars, $nonces));
	} else {
		wp_enqueue_script('base_vendor', dog__parent_js_url('vendor'), null, null, true);
		wp_enqueue_script('base_scripts', dog__parent_js_url('shared'), array('base_vendor'), null, true);
		wp_localize_script('base_scripts', 'dog__wp', array_merge($js_vars, $nonces));
	}

	dog__call_x_function(__FUNCTION__, array('cached_styles' => $cached_styles, 'cached_scripts' => $cached_scripts));
}

function dog__js_vars() {
	return array(
		'theme_url' => dog__theme_url('/'),
		'ajax_url' => admin_url('admin-ajax.php'),
		'DOG__NC_NAME' => DOG__NC_NAME,
		'DOG__NC_VAR_PREFIX' => DOG__NC_VAR_PREFIX,
		'DOG__HP_JAR_NAME' => DOG__HP_JAR_NAME,
		'DOG__HP_TIMER_NAME' => DOG__HP_TIMER_NAME,
		'DOG__WP_ACTION_AJAX_CALLBACK' => DOG__WP_ACTION_AJAX_CALLBACK,
		'DOG__AJAX_RESPONSE_STATUS_SUCCESS' => DOG__AJAX_RESPONSE_STATUS_SUCCESS,
		'DOG__AJAX_RESPONSE_STATUS_ERROR' => DOG__AJAX_RESPONSE_STATUS_ERROR,
		'DOG__ALERT_KEY_SERVER_FAILURE' => dog__alert_message(DOG__ALERT_KEY_SERVER_FAILURE),
		'DOG__ALERT_KEY_CLIENT_FAILURE' => dog__alert_message(DOG__ALERT_KEY_CLIENT_FAILURE),
		'DOG__ALERT_KEY_EMPTY_SELECTION' => dog__alert_message(DOG__ALERT_KEY_EMPTY_SELECTION),
	);
}

function dog__nonces() {
	$list = dog__extend_with('nonces', array());
	$nonces = array();
	if ($list) {
		foreach ($list as $n) {
			$nonces[dog__nonce_var_key($n)] = wp_create_nonce(dog__string_to_key($n));
		}
	}
	return $nonces;
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
	add_editor_style('css/editor-styles.css');
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

function dog__ajax_response_error($info = array(), $data = null) {
	return dog__ajax_response($data, false, $info);
}

function dog__ajax_handler() {
	$nonce_key = DOG__NC_NAME;
	$nonce = dog__get_post_value($nonce_key);
	$method = dog__string_to_key(dog__get_post_value('method'));
	$function = DOG__PREFIX_AJAX . $method;
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

function dog__register_update($updates) {
	$info = dog__get_option(DOG__OPTION_UPDATE_INFO);
	if ($info && $info->update && $info->version && $info->about && $info->download) {
		$updates->response[DOG__THEME_NAME] = array(
			'new_version' => $info->version,
			'url' => $info->about,
			'package' => $info->download,
		);
	}
	return $updates;
}

function dog__reset_update() {
	$info = dog__get_option(DOG__OPTION_UPDATE_INFO);
	if ($info) {
		$info->update = 0;
		dog__update_option(DOG__OPTION_UPDATE_INFO, $info);
	}
}

function dog__minify($value, $url) {
	if ($value) {
	    $postdata = array(
	    	'http' => array(
        		'method'  => 'POST',
        		'header'  => 'Content-type: application/x-www-form-urlencoded',
        		'content' => http_build_query(array('input' => $value))
        	)
        );
		$value = file_get_contents($url, false, stream_context_create($postdata));
	}
	return $value;
}

function dog__minify_script($value) {
	return dog__minify($value, 'https://javascript-minifier.com/raw');
}

function dog__minify_style($value) {
	return dog__minify($value, 'http://cssminifier.com/raw');
}

function dog__clear_page_cache() {
	if (function_exists('wp_cache_clear_cache')) {
		wp_cache_clear_cache();
	}
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
#add_filter('pre_set_site_transient_update_themes', 'dog__check_for_updates');
add_filter('site_transient_update_themes', 'dog__register_update');
add_action('delete_site_transient_update_themes', 'dog__reset_update');
dog__call_x_function('hooks');

if (is_admin()) {
	require_once(dog__parent_admin_file_path('functions.php'));
}

$dog__pll_labels_file = dog__file_path('_pll_labels.php');
if (is_file($dog__pll_labels_file) && dog__plugin_is_active('polylang')) {
	require_once($dog__pll_labels_file);
}