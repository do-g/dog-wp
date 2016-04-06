<?php

require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');

function dog_admin__add_menu(){
	$page_title = __('Opțiuni temă');
	$menu_title = $page_title;
	$capability = 'administrator';
	$menu_slug = DOG_ADMIN__MENU_SLUG;
	$function = 'dog_admin__theme_options';
	$icon = 'dashicons-layout';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon);
    $admin_sections = dog_admin__get_sections();
    if ($admin_sections) {
    	foreach ($admin_sections as $name => $title) {
    		add_submenu_page($menu_slug, $title, $title, $capability, $name);
    	}
    }
}

function dog_admin__theme_options() {
	include 'layout.php';
}

function dog_admin__get_sections() {
	global $dog_admin__sections;
	return $dog_admin__sections;
}

function dog_admin__get_custom_nonces() {
	global $dog_admin__custom_nonces;
	return $dog_admin__custom_nonces;
}

function dog_admin__nonces() {
	$nonces = array();
	$admin_sections = dog_admin__get_sections();
	if ($admin_sections) {
		foreach ($admin_sections as $name => $title) {
			$nonces[dog__nonce_var_key($name)] = wp_create_nonce(dog__string_to_key($name));
		}
	}
	$admin_nonces = dog_admin__get_custom_nonces();
	if ($admin_nonces) {
		foreach ($admin_nonces as $n) {
			$nonces[dog__nonce_var_key($n)] = wp_create_nonce(dog__string_to_key($n));
		}
	}
	return $nonces;
}

function dog_admin__get_transients($prefix = null, $expired = false, $extra_data = array(), $order_by = null) {
	global $wpdb;
	$prefix  = DOG_ADMIN__TRANSIENT_TIMEOUT_DB_PREFIX . ($prefix ? $prefix : '');
	$join = '';
	$fields = '';
	$params = array();
	if ($extra_data) {
		foreach ($extra_data as $key => $alias) {
			$key = substr($key, 0, 7);
			$key = $wpdb->esc_like($key);
			$alias = $wpdb->esc_like($alias);
			$fields .= ", {$alias}.option_value AS {$alias}_option_value";
			$join .= " INNER JOIN {$wpdb->options} {$alias} ON {$alias}.option_name = REPLACE(o.option_name, '_timeout_', '_{$key}_')";
		}
	}
	$sql = "SELECT o.*{$fields} FROM {$wpdb->options} o{$join} WHERE o.option_name LIKE %s";
	array_push($params, $wpdb->esc_like($prefix) . '%');
	if ($expired) {
		$time = time();
		$sql .= " AND option_value < %d";
		array_push($params, $time);
	}
	if ($order_by) {
		$sql .= " order by " . esc_sql($order_by);
	}
	$sql = $wpdb->prepare($sql, $params);
    return $wpdb->get_results($sql);
}

function dog_admin__delete_transients($list) {
	foreach($list as $transient) {
        $key = str_replace(DOG_ADMIN__TRANSIENT_TIMEOUT_DB_PREFIX, '', $transient->option_name);
        dog__delete_transient($key, array('url'));
    }
}

function dog_ajax__generate_labels() {
	global $dog__pll_labels_file;
	$labels = $keys = array();
	$output = $dog__pll_labels_file;
	$child_pattern = dog__file_path('*.php');
	foreach (glob($child_pattern) as $file) {
	    preg_match_all("/dog__txt\('(.*?)'\)/", file_get_contents($file), $matches);
	    $labels = array_merge($labels, $matches[1]);
	}
	$parent_pattern = dog__parent_file_path('*.php');
	foreach (glob($parent_pattern) as $file) {
	    preg_match_all("/dog__txt\('(.*?)'\)/", file_get_contents($file), $matches);
	    $labels = array_merge($labels, $matches[1]);
	}
	$content = array("<?php\n", "require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');\n");
	foreach ($labels as $l) {
		$key = sanitize_title($l);
		if (!in_array($key, $keys)) {
			array_push($content, "pll_register_string('{$key}', '{$l}', 'theme', true);\n");
			array_push($keys, $key);
		}
	}
	file_put_contents($output, implode('', $content));
	$response = '<p>' . str_replace('{$n}', count($labels), __('Am găsit următoarele etichete ({$n}):')) . '</p><pre>' . implode('<br />', $labels) . '</pre>';
	return dog__ajax_response_ok($response);
}

function dog_ajax__cache_settings() {
	dog__whitelist_fields(array(DOG__OPTION_OUTPUT_CACHE_ENABLED, DOG__OPTION_OUTPUT_CACHE_EXPIRES));
	dog__get_post_data(DOG_ADMIN__NAMESPACE_CACHE_SETTINGS);
	dog__validate_nonce(DOG_ADMIN__SECTION_CACHE_SETTINGS);
	dog__validate_honeypot();
	dog__validate_required_fields(array(DOG__OPTION_OUTPUT_CACHE_ENABLED, DOG__OPTION_OUTPUT_CACHE_EXPIRES));
	if (dog__form_is_valid()) {
		dog__update_option(DOG__OPTION_OUTPUT_CACHE_ENABLED, dog__get_post_value(DOG__OPTION_OUTPUT_CACHE_ENABLED), false);
		dog__update_option(DOG__OPTION_OUTPUT_CACHE_EXPIRES, dog__get_post_value(DOG__OPTION_OUTPUT_CACHE_EXPIRES), false);
		return dog_ajax__refresh_cache_settings(array('message' => __('Configurarea memoriei cache salvată cu succes')));
	} else {
		$response = dog_admin__cache_settings_form();
		return dog__ajax_response_error(array('message' => dog__alert_message(DOG__ALERT_KEY_FORM_INVALID)), $response);
	}
}

function dog_admin__get_include_contents($filename) {
	$filepath = dog__parent_admin_file_path($filename);
	return dog__get_include_contents($filepath);
}

function dog_admin__cache_settings_form() {
	return dog_admin__get_include_contents('form-cache-settings.php');
}

function dog_ajax__refresh_cache_settings($extra = null) {
	$response = dog_admin__cache_settings_form();
	return dog__ajax_response_ok($response, $extra);
}

function dog_admin__get_expired_transients() {
	return dog_admin__get_transients(null, true);
}

function dog_admin__list_expired_transients() {
	$expired = dog_admin__get_expired_transients();
	$response = $expired ? dog__replace_template_vars(__('Am găsit ${n} înregistrări expirate'), array('n' => count($expired))) : __('Nu există înregistrări expirate în memoria cache');
	return dog__string_to_html_tag($response, 'pre');
}

function dog_ajax__refresh_expired_transients($extra = null) {
	$response = dog_admin__list_expired_transients();
	return dog__ajax_response_ok($response, $extra);
}

function dog_ajax__expired_transients() {
	$expired = dog_admin__get_expired_transients();
	dog_admin__delete_transients($expired);
    return dog_ajax__refresh_expired_transients(array('message' => __('Memoria a fost curățată de înregistrări expirate')));
}

function dog_admin__get_output_cache() {
	return dog_admin__get_transients(DOG__TRANSIENT_OUTPUT_CACHE_PREFIX, false, array('url' => 'u'), 'u.option_value');
}

function dog_admin__cache_output_form() {
	return dog_admin__get_include_contents('form-cache-output.php');
}

function dog_ajax__refresh_cache_output($extra = null) {
	$response = dog_admin__cache_output_form();
	return dog__ajax_response_ok($response, $extra);
}

function dog_ajax__cache_output() {
	$cache = dog_admin__get_output_cache();
	dog_admin__delete_transients($cache, DOG__TRANSIENT_OUTPUT_CACHE_PREFIX);
    return dog_ajax__refresh_cache_output(array('message' => __('Memoria paginilor a fost golită')));
}

function dog_ajax__cache_output_delete() {
	$hashes = dog__get_post_value('rid', false, DOG__POST_FIELD_TYPE_ARRAY_TEXT);
	if (!$hashes) {
		return dog__ajax_response_error(array('message' => dog__alert_message_code(DOG__ALERT_KEY_RESPONSE_ERROR, DOG__AJAX_RESPONSE_CODE_INVALID_PARAM)), $response);
	}
	foreach ($hashes as $hash) {
		$transient_name = DOG__TRANSIENT_OUTPUT_CACHE_PREFIX . $hash;
		dog__delete_transient($transient_name, array('url'));
	}
	return dog_ajax__refresh_cache_output(array('message' => __('Ștergerea din memorie s-a finalizat cu succes')));
}

function dog_admin__update_info() {
	$info = dog__get_option(DOG__OPTION_UPDATE_INFO);
	$last_check = $info && $info->last_check ? $info->last_check : __('nu există');
	$response = __('Ultima verificare: ') . $last_check . '<br />';
	$response .= __('Versiunea instalată este: ') . dog__parent_theme_version();
	return dog__string_to_html_tag($response, 'pre');
}

function dog_ajax__update_info() {
	return dog__ajax_response_ok(dog_admin__update_info());
}

function dog_ajax__update_check() {
	$info = wp_remote_get(DOG__UPDATE_URL);
	if (!is_array($info)) {
		return dog__ajax_response_error(array('message' => __('Sistemul a întâmpinat o eroare. Comunicarea cu serverului de actualizări a eșuat')));
	}
	$info = json_decode($info['body']);
	if (json_last_error() != JSON_ERROR_NONE) {
		return dog__ajax_response_error(array('message' => __('Sistemul a întâmpinat o eroare. Răspunsul serverului de actualizări nu poate fi procesat')));
	}
	$current_version = dog__parent_theme_version();
	$is_newer = false;
	if (version_compare($info->version, $current_version) == 1) {
		$response  = __('Este disponibilă versiunea: ') . $info->version . '<br />';
		$response .= __('Versiunea instalată este: ') . $current_version;
		$is_newer = true;
	} else {
		$response = dog__replace_template_vars(__('Versiunea instalată ${v} este cea mai recentă'), array('v' => $current_version));
	}
	$response = dog__string_to_html_tag($response, 'pre');
	$info->last_check = date('Y-m-d H:i:s');
	$info->update = 0;
	dog__update_option(DOG__OPTION_UPDATE_INFO, $info);
	return dog__ajax_response_ok($response, array('updates' => $is_newer));
}

function dog_ajax__update() {
	$info = dog__get_option(DOG__OPTION_UPDATE_INFO);
	if ($info && $info->version && $info->about && $info->download) {
		$info->update = 1;
		dog__update_option(DOG__OPTION_UPDATE_INFO, $info);
		return dog__ajax_response_ok(null, array('message' => __('Noua versiune este disponibilă în <a href="/wp-admin/update-core.php">pagina de actualizări. Apasă aici</a> pentru a porni actualizarea')));
	} else {
		return dog__ajax_response_error(array('message' => __('Sistemul a întâmpinat o eroare. Informațiile necesare actualizării nu sunt complete. Te rugăm inițiază o nouă verificare')));
	}
}

function dog_ajax__security() {
	$pattern = '/^.+\.php$/i';
	$parent_php_files = dog__search_files(get_template_directory(), $pattern);
	$child_php_files = dog__search_files(get_stylesheet_directory(), $pattern);
	$php_files = array_merge($parent_php_files, $child_php_files);
	$issues = array();
	if ($php_files) {
		foreach ($php_files as $f) {
			$fragment = str_replace(get_theme_root(), '', $f);
			$url = get_theme_root_uri() . $fragment;
			$response = wp_remote_get($url);
			if ($response && (!empty($response['body']) || $response['response']['code'] != 404)) {
				array_push($issues, '<a href="' . $url . '" target="_blank">' . $fragment . '</a>');
			}
		}
	}
	$response = __('Am găsit ${n} fișiere nesecurizate din totalul de ${t}');
	$response = dog__replace_template_vars($response, array('n' => count($issues), 't' => count($php_files)));
	if ($issues) {
		$response = dog__string_to_html_tag($response, 'p');
		$response .= implode('<br />', $issues);
	}
	$response = dog__string_to_html_tag($response, 'pre');
	return dog__ajax_response_ok($response);
}

function dog_admin__enqueue_assets($hook) {
	if ($hook != DOG_ADMIN__MENU_HOOK) {
		return;
	}
	wp_enqueue_style('admin_styles', dog__parent_admin_url('styles.css'), null, null);
	wp_enqueue_script('jquery_cookie', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array('jquery'), null, true);
	wp_enqueue_script('base_scripts', dog__parent_js_url('shared'), array('jquery_cookie'), null, true);
	$vars = dog__extend_with('admin_js_vars', dog__js_vars());
	$nonces = dog__extend_with('admin_nonces', dog_admin__nonces());
	wp_localize_script('base_scripts', 'dog__wp', array_merge($vars, $nonces));
	wp_enqueue_script('admin_scripts', dog__parent_admin_url('scripts.js'), array('base_scripts'), null, true);
}

add_action('admin_menu', 'dog_admin__add_menu');
add_action('admin_enqueue_scripts', 'dog_admin__enqueue_assets', 99999);
add_action('wp_ajax_' . DOG_ADMIN__WP_ACTION_AJAX_CALLBACK, 'dog__ajax_handler');
dog__call_x_function('admin_hooks');