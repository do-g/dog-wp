<?php

require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
require_once(realpath(dirname(__FILE__)) . '/functions.php');

function dog_admin__add_menu(){
	$page_title = __('Opțiuni temă');
	$menu_title = $page_title;
	$capability = 'administrator';
	$menu_slug = DOG_ADMIN__MENU_SLUG;
	$function = 'dog_admin__theme_options';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}

function dog_admin__theme_options() {
	global $dog_admin__sections; ?>
	<div class="wrap">
		<h1><?= __('Opțiuni temă') ?></h1>
		<?php
		if ($dog_admin__sections) {
			foreach ($dog_admin__sections as $section => $nonce) {
				$section_file = str_replace('_', '-', str_replace(DOG__PREFIX_ADMIN, '', $section));
				dog_admin__include(DOG_ADMIN__SECTION_FILE_PREFIX . $section_file . '.php');
			}
		}
		?>
	</div>
<?php }

function dog_admin__generate_labels() {
	global $dog__pll_labels_file;
	$labels = $keys = array();
	$pattern = get_stylesheet_directory() . '/*.php';
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
	return dog_admin__ajax_response_success($response);
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

function dog_admin__expired_transients() {
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
		return dog_admin__ajax_response(DOG_ADMIN__AJAX_RESPONSE_CODE_MISSING_PARAM);
	}
	foreach ($hashes as $hash) {
		$transient_name = DOG__TRANSIENT_OUTPUT_CACHE_PREFIX . $hash;
		dog__delete_transient($transient_name, array('url'));
	}
	return dog_admin__ajax_response_success(_dog_admin__list_output_cache(), DOG_ADMIN__AJAX_RESPONSE_KEY_SUCCESS2);
}

function dog_admin__ajax() {
	$method = dog__get_post_value('method');
	$ajax_nonce = dog__get_post_value(DOG_ADMIN__AJAX_NONCE_FIELD);
	if (!check_ajax_referer(dog__hash($method), false, false)) {
		$response = dog_admin__ajax_response(DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_NONCE);
	} else if ($method && substr($method, 0, 11) == DOG__PREFIX_ADMIN) {
		if (function_exists($method)) {
			$response = call_user_func($method);
		} else {
			$response = dog_admin__ajax_response(DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_METHOD);
		}
	} else {
		$response = dog_admin__ajax_response(DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_METHOD);
	}
	$response->_ajax_nonce = $ajax_nonce;
	wp_send_json($response);
}

function dog_admin__enqueue_assets($hook) {
	global $dog_admin__sections;
	if ($hook != 'settings_page_' . DOG_ADMIN__MENU_SLUG) {
		return;
	}
	wp_enqueue_style('styles', dog__admin_uri() . '/style.css');
	wp_enqueue_script('cookie', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array('jquery'), false, true);
	wp_enqueue_script('scripts', dog__admin_uri() . '/scripts.js', array('jquery', 'cookie'), false, true);
	if ($dog_admin__sections) {
		$vars = array(
			'DOG__ENV' => DOG__ENV,
			'DOG_ADMIN__WP_ACTION_AJAX_CALLBACK' => DOG_ADMIN__WP_ACTION_AJAX_CALLBACK,
			'DOG_ADMIN__AJAX_NONCE_FIELD' => DOG_ADMIN__AJAX_NONCE_FIELD,
			'DOG_ADMIN__AJAX_NONCE_VAR_PREFIX' => DOG_ADMIN__AJAX_NONCE_VAR_PREFIX,
			'DOG_ADMIN__MESSAGE_CODE_PLACEHOLDER' => DOG_ADMIN__MESSAGE_CODE_PLACEHOLDER,
			'DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH' => DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH,
			'DOG_ADMIN__AJAX_RESPONSE_STATUS_SUCCESS' => DOG_ADMIN__AJAX_RESPONSE_STATUS_SUCCESS,
			'DOG_ADMIN__AJAX_RESPONSE_STATUS_ERROR' => DOG_ADMIN__AJAX_RESPONSE_STATUS_ERROR,
			'DOG_ADMIN__AJAX_RESPONSE_KEY_FAILURE' => DOG_ADMIN__AJAX_RESPONSE_KEY_FAILURE,
			'DOG_ADMIN__AJAX_RESPONSE_KEY_AJAX' => DOG_ADMIN__AJAX_RESPONSE_KEY_AJAX,
			'DOG_ADMIN__AJAX_RESPONSE_CODE_AJAX' => DOG_ADMIN__AJAX_RESPONSE_CODE_AJAX,
			'DOG_ADMIN__AJAX_RESPONSE_CODE_MISMATCH_NONCE' => DOG_ADMIN__AJAX_RESPONSE_CODE_MISMATCH_NONCE
		);
		foreach ($dog_admin__sections as $action => $nonce) {
			if (is_array($nonce)) {
				foreach ($nonce as $a => $n) {
					$vars[DOG_ADMIN__AJAX_NONCE_VAR_PREFIX . $a] = wp_create_nonce(dog__hash($n ? $n : $a));
				}
			} else {
				$vars[DOG_ADMIN__AJAX_NONCE_VAR_PREFIX . $action] = wp_create_nonce(dog__hash($nonce ? $nonce : $action));
			}
		}
		wp_localize_script('scripts', 'dog_admin__ajax_context', $vars);
	}
}

add_action('admin_menu', 'dog_admin__add_menu');
add_action('admin_enqueue_scripts', 'dog_admin__enqueue_assets', 99999);
add_action('wp_ajax_dog_admin__ajax', DOG_ADMIN__WP_ACTION_AJAX_CALLBACK);