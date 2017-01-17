<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

/***** environment *****/

function dog__is_env_dev() {
	return DOG__ENV == 'dev';
}

function dog__is_env_pro() {
	return DOG__ENV == 'pro';
}

/***** urls *****/

function dog__safe_url($uri, $display = false) {
	return $display ? esc_url($uri) : esc_url_raw($uri);
}

function dog__url_fragment($path) {
	return '/' . ltrim($path, '/');
}

function dog__plugin_url($file, $plugin_name, $display = true) {
	return dog__safe_url(plugins_url($file, "{$plugin_name}/plugin.php"), $display);
}

function dog__theme_url($file, $display = true) {
	return dog__safe_url(get_stylesheet_directory_uri() . dog__url_fragment($file), $display);
}

function dog__base_theme_url($file, $display = true) {
	return dog__safe_url(get_template_directory_uri() . dog__url_fragment($file), $display);
}

function dog__asset_url($file, $display = true) {
	return DOG__STATIC_ASSETS_URL ? dog__safe_url(DOG__STATIC_ASSETS_URL . dog__url_fragment($file), $display) : dog__theme_url($file, $display);
}

function dog__base_asset_url($file, $display = true) {
	return DOG__STATIC_BASE_ASSETS_URL ? dog__safe_url(DOG__STATIC_BASE_ASSETS_URL . dog__url_fragment($file), $display) : dog__base_theme_url($file, $display);
}

function dog__img_url_fragment($file) {
	return dog__url_fragment('images/' . ltrim($file, '/'));
}

function dog__img_url($file, $display = true) {
	return dog__asset_url(dog__img_url_fragment($file), $display);
}

function dog__base_img_url($file, $display = true) {
	return dog__base_asset_url(dog__img_url_fragment($file), $display);
}

function dog__js_url_fragment($file) {
	return dog__url_fragment('js/' . ltrim($file, '/') . '.js');
}

function dog__js_url($file, $display = true) {
	return dog__asset_url(dog__js_url_fragment($file), $display);
}

function dog__base_js_url($file, $display = true) {
	return dog__base_asset_url(dog__js_url_fragment($file), $display);
}

function dog__css_url_fragment($file) {
	return dog__url_fragment('css/' . ltrim($file, '/') . '.css');
}

function dog__css_url($file, $display = true) {
	return dog__asset_url(dog__css_url_fragment($file), $display);
}

function dog__base_css_url($file, $display = true) {
	return dog__base_asset_url(dog__css_url_fragment($file), $display);
}

function dog__email_url_fragment($file) {
	return dog__url_fragment('email/' . ltrim($file, '/') . '.txt');
}

function dog__compressed_asset_url_fragment($asset_name) {
	return dog__url_fragment(DOG__COMPRESSED_ASSET_DIR . '/' . ltrim($asset_name, '/'));
}

function dog__compressed_asset_url($asset_name, $display = true) {
	return dog__asset_url(dog__compressed_asset_url_fragment($asset_name), $display);
}

function dog__current_url($exclude_query_string = false, $trim_end_slash = false, $display = false) {
	return dog__url_format($_SERVER['REQUEST_URI'], $exclude_query_string, $trim_end_slash);
}

function dog__url_format($uri, $exclude_query_string = false, $trim_end_slash = false, $display = false) {
	if ($exclude_query_string) {
		$parts = explode('?', $uri);
		$uri = $parts[0];
	}
	$uri = $trim_end_slash ? ($uri != '/' ? rtrim($uri, '/') : $uri) : $uri;
	return dog__safe_url($uri, $display);
}

function dog__redirect($location, $status = null) {
	wp_redirect($location, $status);
	exit;
}

function dog__safe_redirect($location, $status = null) {
	wp_safe_redirect($location, $status);
	exit;
}

function dog__timestamp_url($url, $key_name = null) {
	$timestamp = time();
	return $url . (strpos($url, '?') !== false ? '&' : '?') . ($key_name ? "{$key_name}={$timestamp}" : $timestamp);
}

function dog__home_url() {
	return dog__lang_plugin_is_active() ? pll_home_url() : home_url();
}

function dog__http_build_query($params, $base_url = null) {
	$base = '';
	if ($base_url) {
		$separator = strpos($base_url, '?') ? '&' : '?';
		$base = $base_url . $separator;
	}
	return $base . urldecode(http_build_query($params));
}

/***** paths *****/

function dog__theme_path($file) {
	return get_stylesheet_directory() . dog__url_fragment($file);
}

function dog__theme_email_path($file) {
	return dog__theme_path(dog__email_url_fragment($file));
}

function dog__base_theme_path($file) {
	return get_template_directory() . dog__url_fragment($file);
}

function dog__plugin_path($file, $plugin_name) {
	return WP_PLUGIN_DIR . "/{$plugin_name}" . dog__url_fragment($file);
}

function dog__sibling_path($sibling, $reference) {
	return dirname($reference) . dog__url_fragment($sibling);
}

function dog__compressed_asset_dir() {
	return dog__theme_path(DOG__COMPRESSED_ASSET_DIR);
}

/***** theme & plugins *****/

function dog__get_dog_plugin_names($full = false) {
	$dog_plugins = array();
	$plugins = get_plugins();
	if ($plugins) {
		foreach ($plugins as $name => $info) {
			if ($info['TextDomain'] == DOG__TEXT_DOMAIN) {
				array_push($dog_plugins, $full ? $name : dirname($name));
			}
		}
	}
	return $dog_plugins;
}

function dog__get_dog_theme_names() {
	return dog__trim_explode(DOG__THEMES);
}

function dog__get_plugin_name_from_path($plugin_file_path, $full = false) {
	return basename(dirname($plugin_file_path)) . ($full ? dog__url_fragment('plugin.php') : '');
}

function dog__switch_theme() {
	$dog__themes = dog__get_dog_theme_names();
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

function dog__theme_version($theme_name = null) {
	$theme = wp_get_theme($theme_name);
	return $theme->get('Version');
}

function dog__base_theme_version() {
	return dog__theme_version(DOG__BASE_THEME_NAME);
}

/***** options *****/

function dog__get_option($name, $default = null) {
	return get_option(DOG__OPTION_PREFIX . $name, $default);
}

function dog__update_option($name, $value, $autoload = false) {
	return update_option(DOG__OPTION_PREFIX . $name, $value, $autoload);
}

function dog__delete_option($name) {
	return delete_option(DOG__OPTION_PREFIX . $name);
}

/***** language & translations *****/

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

function dog__website_language() {
	return strtolower(reset(explode('-', get_bloginfo('language'))));
}

function dog__default_language() {
	return dog__lang_plugin_is_active() ? pll_default_language() : dog__website_language();
}

function dog__active_language() {
	return dog__lang_plugin_is_active() ? pll_current_language() : dog__website_language();
}

function dog__is_active_language($lang) {
	return $lang == dog__active_language();
}

function dog__is_default_language($lang = null) {
	$lang = $lang ? $lang : dog__active_language();
	return $lang == dog__default_language();
}

function dog__lang_plugin_is_active() {
	return function_exists('pll_register_string');
}

function dog__txt_raw($label, $vars = null, $lang = null) {
	$txt = dog__lang_plugin_is_active() ? ($lang ? pll_translate_string($label, $lang) : pll__($label)) : $label;
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

/***** forms *****/

function dog__form_action_url() {
	return esc_url(admin_url('admin-post.php'));
}

function dog__get_boolean_attr_value($key, $condition) {
	return $condition ? $key : false;
}

function dog__get_checked_attr_value($condition) {
	return dog__get_special_attr_value('checked', $condition);
}

function dog__get_selected_attr_value($condition) {
	return dog__get_special_attr_value('selected', $condition);
}

function dog__attributes_array_to_html($list, $boolean = array()) {
	$html = array();
	if ($list) {
		$boolean = array_merge(array('checked', 'selected', 'required', 'readonly', 'disabled'), $boolean);
		foreach ($list as $key => $value) {
			$key = sanitize_key($key);
			$value = esc_attr($value);
			if (in_array($key, $boolean)) {
				if ($value) {
					array_push($html, $key);
				}
			} else {
				array_push($html, "{$key}=\"{$value}\"");
			}
		}
	}
	return implode(" ", $html);
}

function dog__is_post($key = null) {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && ($key ? isset($_POST[$key]) : 1);
}

function dog__is_get($key = null) {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && ($key ? sanitize_text_field($key) : 1);
}

/***** ajax *****/

function dog__ajax_response($data = null, $success = true, $extra = array()) {
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

function dog__ajax_response_ok($data = null, $info = array()) {
	return dog__ajax_response($data, true, $info);
}

function dog__ajax_response_error($info = array(), $data = null) {
	return dog__ajax_response($data, false, $info);
}

/***** email *****/

function dog__get_text_template($file_path, $template_vars = array()) {
	return is_file($file_path) && is_readable($file_path) ? dog__replace_template_vars(file_get_contents($file_path), $template_vars) : false;
}

function dog__get_theme_email_template($name, $template_vars = array(), $lang = null) {
	$name .= $lang ? "-{$lang}" : '';
	$path = dog__theme_email_path($name);
	return dog__get_text_template($path, $template_vars);
}

function dog__get_mail_errors() {
	global $ts_mail_errors, $phpmailer;
	if (!isset($ts_mail_errors)) {
		$ts_mail_errors = array();
	}
	if (isset($phpmailer)) {
		array_push($ts_mail_errors, $phpmailer->ErrorInfo);
	}
	return $ts_mail_errors;
}

function dog__send_mail($recipients, $subject, $message, $headers, $attachments = array()) {
	$content_type = $headers['content_type'] ? $headers['content_type'] : 'text/html';
	$charset = $headers['charset'] ? $headers['charset'] : 'UTF-8';
	$full_headers = array(
		'Content-Type: ' . $content_type . '; charset=' . $charset,
		'Sender: ' . $headers['sender'],
		'From: ' . $headers['from']['name'] . ' <' . $headers['from']['email'] . '>',
		'Reply-To: ' . $headers['reply']['name'] . ' <' . $headers['reply']['email'] . '>',
	);
	if (!wp_mail($recipients, $subject, $message, $full_headers, $attachments)) {
		return dog__get_mail_errors();
	}
	return true;
}

/***** notices *****/

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

function dog__set_transient_flash_message($message, $type = null) {
	$key = DOG__TRANSIENT_FLASH_MESSAGES;
	$type = $type ? $type : DOG__TRANSIENT_FLASH_KEY_SUCCESS;
	$entries = get_transient($key);
	$entries = $entries ? $entries : array();
	$entries[$type] = $entries[$type] ? $entries[$type] : array();
	array_push($entries[$type], $message);
	set_transient($key, $entries, DOG__TRANSIENT_FLASH_EXPIRE);
}

function dog__set_transient_flash_error($message) {
	return dog__set_transient_flash_message($message, DOG__TRANSIENT_FLASH_KEY_ERROR);
}

function dog__set_transient_flash_warning($message) {
	return dog__set_transient_flash_message($message, DOG__TRANSIENT_FLASH_KEY_WARNING);
}

function dog__get_transient_flash_entries($type = null) {
	$key = DOG__TRANSIENT_FLASH_MESSAGES;
	$entries = get_transient($key);
	if ($type) {
		$messages = $entries[$type];
		unset($entries[$type]);
	} else {
		$messages = $entries;
		$entries = null;
	}
	set_transient($key, $entries, DOG__TRANSIENT_FLASH_EXPIRE);
	return $messages;
}

function dog__get_transient_flash_messages() {
	return dog__get_transient_flash_entries(DOG__TRANSIENT_FLASH_KEY_SUCCESS);
}

function dog__get_transient_flash_errors() {
	return dog__get_transient_flash_entries(DOG__TRANSIENT_FLASH_KEY_ERROR);
}

function dog__get_transient_flash_warnings() {
	return dog__get_transient_flash_entries(DOG__TRANSIENT_FLASH_KEY_WARNING);
}

function dog__prepare_transient_flash_messages() {
	$entries = dog__get_transient_flash_entries();
	$output = array();
	if ($entries) {
		foreach ($entries as $type => $messages) {
			foreach ($messages as $m) {
				array_push($output, '<div class="notice notice-' . $type . ' is-dismissible"><p><strong>' . $m . '</strong></p></div>');
			}
		}
	}
	return implode('', $output);
}

/***** shortcodes *****/

function dog__strip_shortcode($code, $content) {
    global $shortcode_tags;
    $stack = $shortcode_tags;
    $shortcode_tags = array($code => 1);
    $content = strip_shortcodes($content);
    $shortcode_tags = $stack;
    return $content;
}

function dog__get_shortcode_text($tag, $params = null, $content = null) {
	$output = '[' . $tag;
	if ($params) {
		$output .= ' ' . dog__attributes_array_to_html($params);
	}
	$output .=  ']';
	if ($content) {
		$output .= $content;
		$output .= '[/' . $tag . ']';
	}
	return $output;
}

function dog__clean_shortcode_content($content, $skip = array()) {
	if (!$skip['entities']) {
		$content = html_entity_decode($content);
	}
	if (!$skip['tags']) {
		$content = strip_tags($content);
	}
	if (!$skip['nbsp']) {
		$content = preg_replace('/\x{00a0}/u', '', $content);
	}
	if (!$skip['comments']) {
		$content = preg_replace('/' . DOG__SHORTCODE_COMMENT_REGEX . '/', '', $content);
	}
	if (!$skip['blank_lines']) {
		$content = preg_replace('/^\s*/m', '', $content);
	}
	if (!$skip['line_breaks']) {
		$content = str_replace(array("\r", "\n"), '', $content);
	}
	if (!$skip['trim']) {
		$content = trim($content);
	}
	return $content;
}

function dog__get_shortcode_attr($attr_name, $attr_list, $shortcode_content = null, $clean_content = false, $clean_skip = array()) {
	$attr_value = $attr_list[$attr_name];
	if ($attr_value == DOG__SHORTCODE_KEYWORD_CONTENT) {
		$attr_value = $shortcode_content;
		if ($clean_content) {
			$attr_value = dog__clean_shortcode_content($attr_value, $clean_skip);
		}
	}
	return $attr_value;
}

/***** utils *****/

function dog__closest($search, $list, $return_key = false, $prefer_lower = false) {
	$closest_value = null;
	$closest_value_key = null;
   	foreach ($list as $key => $value) {
   		$diff_closest = abs($search - $closest_value);
      	$diff_current = abs($search - $value);
		if (
				$closest_value === null ||
				$diff_closest > $diff_current ||
				(
					$diff_closest == $diff_current &&
		            (
		              ($prefer_lower && $value < $closest_value) ||
		              (!$prefer_lower && $value > $closest_value)
		            )
				)
			) {
        	$closest_value = $value;
        	$closest_value_key = $key;
      	}
   	}
   	return $return_key ? $closest_value_key : $closest_value;
}

function dog__trim_explode($data, $separator = ',') {
	$parts = array();
	if ($data) {
		$data = trim($data, $separator);
		$parts = explode($separator, $data);
		$parts = array_map('trim', $parts);
	}
	return $parts;
}

function dog__to_css_class($list) {
	$class = array_filter($list);
	$class = array_map('sanitize_html_class', $class);
	$class = implode(' ', $class);
	return $class;
}

function dog__merge_css_classes($default_class, $user_class) {
	if ($user_class && !is_array($user_class)) {
		$user_class = explode(' ', $user_class);
	}
	$user_class = $user_class ? $user_class : array();
	$class = array_merge($default_class, $user_class);
	return dog__to_css_class($class);
}

function dog__get_var_from_css_class($css_class, $var_prefix) {
	$classes = explode(' ', $css_class);
	foreach ($classes as $c) {
		if (strpos($c, $var_prefix) !== false) {
			return substr($c, strlen($var_prefix));
		}
	}
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

function dog__replace_template_vars($template, $data) {
	if ($template && $data) {
		foreach ($data as $key => $value) {
			if (is_array($value) || is_object($value)) {
				foreach ($value as $k => $v) {
					$template = str_replace('${' . $key . '.' . $k . '}', $v, $template);
				}
			} else {
				$template = str_replace('${' . $key . '}', $value, $template);
			}
		}
	}
	return $template;
}

function dog__string_to_key($value, $prefix = null, $suffix = null) {
	$value = strtolower($value);
	$value = preg_replace('/[-]+/', '_', $value);
	$value = preg_replace('/\s+/', '_', $value);
	$value = preg_replace('/[^a-z0-9_]/', '', $value);
  	return ($prefix ? $prefix : '') . $value . ($suffix ? $suffix : '');
}

function dog__nonce_var_key($name) {
	return dog__string_to_key($name, DOG__NC_VAR_PREFIX);
}

function dog__value_if_true($value, $condition) {
	return $condition ? $value : '';
}

function dog__value_or_default($value, $default) {
	return $value ? $value : $default;
}

function dog__debug_queries() {
	global $wpdb;
	echo "<pre>";
   	print_r($wpdb->queries);
   	echo "</pre>";
}

function dog__is_debug() {
	return defined('WP_DEBUG') && WP_DEBUG;
}

function dog__debug_message($public_message, $debug_message, $force_enable_debug = false) {
	if ($force_enable_debug || dog__is_debug()) {
		return "{$public_message} ({$debug_message})";
	} else {
		return $public_message;
	}
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

function dog__array_merge_unique($arr1, $arr2) {
	return array_unique(array_merge($arr1, $arr2));
}

function dog__to_nonces($list) {
	$nonces = array();
	if ($list) {
		foreach ($list as $item) {
			$nonces[dog__nonce_var_key($item)] = wp_create_nonce(dog__string_to_key($item));
		}
	}
	return $nonces;
}

function dog__clear_page_cache() {
	if (function_exists('wp_cache_clear_cache')) {
		wp_cache_clear_cache();
	}
}

function dogx__skip_cache() {
	if (!defined('DONOTCACHEPAGE')) {
		define('DONOTCACHEPAGE', true);
	}
}

function dog__include_file($file_path, $tpl_data = null) {
	include($file_path);
}

function dog__get_file_output($filepath, $tpl_data = null) {
    if (is_file($filepath)) {
        ob_start();
        include $filepath;
        return ob_get_clean();
    }
    return false;
}

function dog__get_featured_image_url($size = 'full', $post_id = null) {
	$id = get_post_thumbnail_id($post_id);
	return esc_url($id ? dog__get_attachment_image_url($size, $id) : null);
}

function dog__get_attachment_url($id = null) {
	$id = $id ? $id : get_the_id();
	return wp_get_attachment_url($id);
}

function dog__get_attachment_image_url($size = 'full', $attachment_id = null) {
	$id = $attachment_id ? $attachment_id : get_the_id();
	return reset(wp_get_attachment_image_src($id, $size));
}

function dog__curl($options, &$err = null, &$status = null) {
	$default_options = array(
		CURLOPT_HEADER => 0,
		CURLOPT_POST => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYPEER => 0,
	);
	$options = array_replace($default_options, $options);
	$ch = curl_init();
    foreach ($options as $key => $value) {
        curl_setopt($ch, $key, $value);
	}
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return $response;
}