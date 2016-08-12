<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

/***** environment *****/

function dog__is_env_dev() {
	return DOG__ENV == 'dev';
}

function dog__is_env_pro() {
	return DOG__ENV == 'pro';
}

/***** utils *****/

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
	return array_map('trim', explode(',', DOG__THEMES));
}

function dog__get_plugin_name_from_path($plugin_file_path) {
	$plugin_dir = dirname($plugin_file_path);
	return basename($plugin_dir);
}

function dog__get_full_plugin_name_from_path($plugin_file_path) {
	$plugin_name = dog__get_plugin_name_from_path($plugin_file_path);
	return "{$plugin_name}/{$plugin_name}.php";
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
			$template = str_replace('${' . $key . '}', $value, $template);
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

function dog__strip_shortcode($code, $content) {
    global $shortcode_tags;
    $stack = $shortcode_tags;
    $shortcode_tags = array($code => 1);
    $content = strip_shortcodes($content);
    $shortcode_tags = $stack;
    return $content;
}

function dog__value_or_empty($value, $condition) {
	return $condition ? $value : '';
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

function dog__array_merge_unique($arr1, $arr2) {
	return array_unique(array_merge($arr1, $arr2));
}

/***** urls *****/

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

function dog__safe_url($uri, $display = false) {
	return $display ? esc_url($uri) : esc_url_raw($uri);
}

function dog__plugin_url($plugin_name_or_file, $url_path) {
	$pos = strpos($plugin_name_or_file, '/');
	if ($pos === false) {
		$plugin_name_or_file = WP_PLUGIN_DIR . "/{$plugin_name_or_file}/{$plugin_name_or_file}.php";
	}
	$plugin_dir = dirname($plugin_name_or_file);
	$plugin_name = basename($plugin_dir);
	return plugins_url($url_path, "{$plugin_dir}/{$plugin_name}.php");
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

function dog__redirect($location, $status = null) {
	wp_redirect($location, $status);
	exit;
}

function dog__safe_redirect($location, $status = null) {
	wp_safe_redirect($location, $status);
	exit;
}

/***** paths *****/

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

/***** templates *****/

function dog__get_file_output($filepath, $tpl_data = null) {
    if (is_file($filepath)) {
        ob_start();
        include $filepath;
        return ob_get_clean();
    }
    return false;
}

/***** forms *****/

function dog__get_boolean_attr_value($key, $condition) {
	return $condition ? $key : false;
}

function dog__get_checked_attr_value($condition) {
	return dog__get_special_attr_value('checked', $condition);
}

function dog__get_selected_attr_value($condition) {
	return dog__get_special_attr_value('selected', $condition);
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

function dog__is_post($key = null) {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && ($key ? isset($_POST[$key]) : 1);
}

function dog__is_get($key = null) {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && ($key ? sanitize_text_field($key) : 1);
}

/***** compatibility *****/

function dog__get_include_contents($filepath, $tpl_data = null) {
	trigger_error(__FUNCTION__ . '() is deprecated. Use dog__get_file_output() instead', E_USER_NOTICE);
	debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	return dog__get_file_output($filepath, $tpl_data);
}

function dog__get_special_attr_value($key, $condition) {
	trigger_error(__FUNCTION__ . '() is deprecated. Use dog__get_boolean_attr_value() instead', E_USER_NOTICE);
	debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	dog__get_boolean_attr_value($key, $condition);
}