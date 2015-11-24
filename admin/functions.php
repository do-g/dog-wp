<?php

require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');

function dog_admin__img_uri($image_file_name, $display = true) {
	return dog__safe_uri(dog__admin_uri() . '/images/' . $image_file_name, $display);
}

function dog_admin__js_uri($script_file_name, $display = true) {
	return dog__safe_uri(dog__admin_uri() . '/' . $script_file_name . '.js', $display);
}

function dog_admin__css_uri($style_file_name, $display = true) {
	return dog__safe_uri(dog__admin_uri() . '/' . $style_file_name . '.css', $display);
}

function dog_admin__include($file, $data = null) {
	include dog__admin_file_path($file);
}

function dog_admin__ajax_response($code = null, $key = null, $data = null, $is_error = true) {
	$key = $key ? $key : DOG_ADMIN__AJAX_RESPONSE_KEY_FAILURE;
	$code = $code ? $code : DOG_ADMIN__AJAX_RESPONSE_CODE_FAILURE;
	$response = new stdClass();
	$response->status = $is_error ? DOG_ADMIN__AJAX_RESPONSE_STATUS_ERROR : DOG_ADMIN__AJAX_RESPONSE_STATUS_SUCCESS;
	$response->key = $key;
	$response->code = $code;
	$response->data = $data;
	return $response;
}

function dog_admin__ajax_response_success($data = null, $key = null) {
	$key = $key ? $key : DOG_ADMIN__AJAX_RESPONSE_KEY_SUCCESS;
	return dog_admin__ajax_response(DOG_ADMIN__AJAX_RESPONSE_CODE_SUCCESS, $key, $data, false);
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