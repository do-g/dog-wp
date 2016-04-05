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
}

function dog_admin__theme_options() {
	global $dog_admin__sections;
	include 'layout.php';
}

function dog_admin__img_uri($image_file_name, $display = true) {
	return dog__safe_uri(dog__admin_uri() . '/images/' . $image_file_name, $display);
}

function dog_admin__js_uri($script_file_name, $display = true) {
	return dog__safe_uri(dog__admin_uri() . '/' . $script_file_name . '.js', $display);
}

function dog_admin__css_uri($style_file_name, $display = true) {
	return dog__safe_uri(dog__admin_uri() . '/' . $style_file_name . '.css', $display);
}

function dog_admin__nonces() {
	global $dog_admin__sections, $dog_admin__custom_nonces;
	$nonces = array();
	if ($dog_admin__sections) {
		foreach ($dog_admin__sections as $s) {
			$nonces[dog__nonce_var_key($s)] = wp_create_nonce($s);
		}
	}
	if ($dog_admin__custom_nonces) {
		foreach ($dog_admin__custom_nonces as $n) {
			$nonces[dog__nonce_var_key($n)] = wp_create_nonce($ns);
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
	$pattern = dog__file_path('*.php');
	$output = $dog__pll_labels_file;
	foreach (glob($pattern) as $file) {
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
	return dog__ajax_response_ok($response, array('message' => dog__alert_message('labels_generated')));
}

function dog_admin__cache_settings() {
	dog__whitelist_fields(array(DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED, DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES));
	dog__get_post_data(DOG_ADMIN__NAMESPACE_CACHE_SETTINGS);
	dog__validate_nonce(DOG_ADMIN__SECTION_ACTION_CACHE_SETTINGS);
	dog__validate_honeypot();
	dog__validate_required_fields(array(DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED, DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES));
	if (dog__form_is_valid()) {
		dog__update_option(DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED, dog__get_post_value(DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED), false);
		dog__update_option(DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES, dog__get_post_value(DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES), false);
		$response = dog__get_include_contents(dog__admin_file_path('form-cache-settings.php'));
		return dog_admin__ajax_response_success($response);
	} else if (!dog__get_form_errors()) {
		$response = dog__get_include_contents(dog__admin_file_path('form-cache-settings.php'));
		return dog_admin__ajax_response(DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_FORM, DOG_ADMIN__AJAX_RESPONSE_KEY_FORM, $response);
	}
}

function _dog_admin__get_expired_transients() {
	return dog_admin__get_transients(null, true);
}

function _dog_admin__list_expired_transients() {
	$expired = _dog_admin__get_expired_transients();
	return '<pre>' . ($expired ? str_replace('{$n}', '<b>' . count($expired) . '</b>', __('Am găsit {$n} înregistrări expirate')) : __('Nu există înregistrări expirate în memoria cache')) . '</pre>';
}

function dog_ajax__expired_transients() {
	$expired = _dog_admin__get_expired_transients();
	dog_admin__delete_transients($expired);
    return dog_admin__ajax_response_success(_dog_admin__list_expired_transients());
}

function _dog_admin__get_output_cache() {
	return dog_admin__get_transients(DOG__TRANSIENT_OUTPUT_CACHE_PREFIX, false, array('url' => 'u'), 'u.option_value');
}

function _dog_admin__list_output_cache() {
	$cache = _dog_admin__get_output_cache();
	$result  = '<pre>';
	$result .= $cache ? str_replace('{$n}', '<b>' . count($cache) . '</b>', __('Am găsit {$n} pagini memorate')) . '<br />' : __('Nu există pagini în memoria cache');
	if ($cache) {
		$result .= '<br /><table><tr>';
		$result .= '<th>&nbsp;</th>';
		$result .= '<th>' . __('Adresă URL') . '</th>';
		$result .= '<th>' . __('Cod identificare') . '</th>';
		$result .= '<th>' . __('Expiră') . '</th>';
		$result .= '</tr>';
		foreach ($cache as $i => $row) {
			$result .= '<tr>';
			$result .= '	<td><input type="checkbox" name="rid[' . $i . ']" value="' . dog__get_output_cache_transient_hash($row->option_name) . '" /></td>';
			$result .= '	<td>' . $row->u_option_value . '</td>';
			$result .= '	<td>' . dog__get_output_cache_transient_hash($row->option_name) . '</td>';
			$result .= '	<td>' . date('Y-m-d H:i:s', $row->option_value) . '</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
	}
	$result .= '</pre>';
	return $result;
}

function dog_admin__cache_output() {
	$cache = _dog_admin__get_output_cache();
	dog_admin__delete_transients($cache, DOG__TRANSIENT_OUTPUT_CACHE_PREFIX);
    return dog_admin__ajax_response_success(_dog_admin__list_output_cache());
}

function dog_admin__cache_output_delete() {
	$hashes = dog__get_post_value('rid', false, DOG__POST_FIELD_TYPE_ARRAY_TEXT);
	if (!$hashes) {
		return dog_admin__ajax_response(DOG__AJAX_RESPONSE_CODE_INVALID_PARAM);
	}
	foreach ($hashes as $hash) {
		$transient_name = DOG__TRANSIENT_OUTPUT_CACHE_PREFIX . $hash;
		dog__delete_transient($transient_name, array('url'));
	}
	return dog_admin__ajax_response_success(_dog_admin__list_output_cache(), DOG_ADMIN__AJAX_RESPONSE_KEY_SUCCESS2);
}

function dog_admin__enqueue_assets($hook) {
	global $dog_admin__sections;
	if ($hook != DOG_ADMIN__MENU_HOOK) {
		return;
	}
	wp_enqueue_style('styles', dog__admin_uri() . '/style.css');
	wp_enqueue_script('cookie', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array('jquery'), null, true);
	wp_enqueue_script('doglib', dog__js_uri('lib'), array('jquery', 'cookie'), null, true);
	if ($dog_admin__sections) {
		$vars = dog__extend_with('admin_js_vars', dog__js_vars());
		$nonces = dog__extend_with('admin_nonces', dog_admin__nonces());
		wp_localize_script('doglib', 'dog__wp', array_merge($vars, $nonces));
	}
	wp_enqueue_script('scripts', dog__admin_uri('scripts.js'), array('doglib'), null, true);
}