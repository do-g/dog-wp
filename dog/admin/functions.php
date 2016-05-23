<?php

require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');

function dog_admin__add_menu(){
	$page_title = dog__txt('Opțiuni temă');
	$menu_title = $page_title;
	$capability = 'administrator';
	$menu_slug = DOG_ADMIN__MENU_SLUG;
	$function = 'dog_admin__theme_options';
	$icon = 'dashicons-layout';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon);
    $admin_sections = dog_admin__get_sections();
    if ($admin_sections) {
    	foreach ($admin_sections as $name => $title) {
    		add_submenu_page($menu_slug, $title, $title, $capability, 'admin.php?page=' . $menu_slug . '#section-' . $name);
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

function dog_ajax__generate_labels() {
	global $dog__pll_labels_file;
	$labels = $keys = array();
	$output = $dog__pll_labels_file;
	$ignore = array('_pll_labels.php');
	$file_names = array();
	$child_pattern = dog__file_path('*.php');
	$all_files = glob($child_pattern);
	$parent_pattern = dog__parent_file_path('*.php');
	$all_files = array_merge($all_files, glob($parent_pattern));
	foreach ($all_files as $file) {
		$file_name = basename($file);
		if (in_array($file_name, $ignore) || in_array($file_name, $file_names)) {
			continue;
		}
		array_push($file_names, $file_name);
	    $labels = array_merge($labels, dog__extract_file_labels($file));
	}
	$admin_labels = $admin_keys = array();
	$file_names = array();
	$child_admin_pattern = dog__admin_file_path('*.php');
	$all_admin_files = glob($child_admin_pattern);
	$parent_admin_pattern = dog__parent_admin_file_path('*.php');
	$all_admin_files = array_merge($all_admin_files, glob($parent_admin_pattern));
	foreach ($all_admin_files as $file) {
		$file_name = basename($file);
		if (in_array($file_name, $ignore) || in_array($file_name, $file_names)) {
			continue;
		}
		array_push($file_names, $file_name);
	    $admin_labels = array_merge($admin_labels, dog__extract_file_labels($file));
	}
	$content = array("<?php\n", "require_once(realpath(dirname(__FILE__)) . '/../dog/_block-direct-access.php');\n");
	foreach ($labels as $l) {
		$key = sanitize_title($l);
		if (!in_array($key, $keys)) {
			array_push($content, "pll_register_string('{$key}', '{$l}', 'theme', true);\n");
			array_push($keys, $key);
		}
	}
	foreach ($admin_labels as $l) {
		$key = sanitize_title($l);
		if (!in_array($key, $admin_keys)) {
			array_push($content, "pll_register_string('{$key}', '{$l}', 'admin', true);\n");
			array_push($admin_keys, $key);
		}
	}
	file_put_contents($output, implode('', $content));
	$all_labels = array_merge($labels, $admin_labels);
	$response = dog__txt('Am găsit următoarele etichete (${n}):', array('n' => count($all_labels)));
	$response = dog__string_to_html_tag($response, 'p');
	$response .= dog__string_to_html_tag(implode('<br />', $all_labels), 'pre');
	return dog__ajax_response_ok($response);
}

function dog_admin__get_include_contents($filename, $tpl_data = null) {
	$filepath = dog__parent_admin_file_path($filename);
	return dog__get_include_contents($filepath, $tpl_data);
}

function dog_admin__update_info() {
	$info = dog__get_option(DOG__OPTION_UPDATE_INFO);
	$last_check = $info && $info->last_check ? $info->last_check : dog__txt('nu există');
	$response = dog__txt('Ultima verificare: ${d}', array('d' => $last_check)) . '<br />';
	$response .= dog__txt('Versiunea instalată este: ${v}', array('v' => dog__parent_theme_version()));
	return dog__string_to_html_tag($response, 'pre');
}

function dog_ajax__update_info() {
	return dog__ajax_response_ok(dog_admin__update_info());
}

function dog_ajax__update_check() {
	$info = wp_remote_get(DOG__UPDATE_URL);
	if (!is_array($info)) {
		return dog__ajax_response_error(array('message' => dog__txt('Sistemul a întâmpinat o eroare. Comunicarea cu serverului de actualizări a eșuat')));
	}
	$info = json_decode($info['body']);
	if (json_last_error() != JSON_ERROR_NONE) {
		return dog__ajax_response_error(array('message' => dog__txt('Sistemul a întâmpinat o eroare. Răspunsul serverului de actualizări nu poate fi procesat')));
	}
	$current_version = dog__parent_theme_version();
	$is_newer = false;
	if (version_compare($info->version, $current_version) == 1) {
		$response  = dog__txt('Este disponibilă versiunea: ${v}', array('v' => $info->version)) . '<br />';
		$response .= dog__txt('Versiunea instalată este: ${v}', array('v' => $current_version));
		$is_newer = true;
	} else {
		$response = dog__txt('Versiunea instalată ${v} este cea mai recentă', array('v' => $current_version));
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
		$link = '<a href="/wp-admin/update-core.php">' . dog__txt('Apasă aici pentru a porni actualizarea') . '</a>';
		return dog__ajax_response_ok(null, array('message' => dog__txt('Noua versiune este disponibilă în pagina de actualizări.') . ' ' . $link));
	} else {
		return dog__ajax_response_error(array('message' => dog__txt('Sistemul a întâmpinat o eroare. Informațiile necesare actualizării nu sunt complete. Te rugăm inițiază o nouă verificare')));
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
	$response = dog__txt('Am găsit ${n} fișiere nesecurizate din totalul de ${t}', array('n' => count($issues), 't' => count($php_files)));
	if ($issues) {
		$response = dog__string_to_html_tag($response, 'p');
		$response .= implode('<br />', $issues);
	}
	$response = dog__string_to_html_tag($response, 'pre');
	return dog__ajax_response_ok($response);
}

function dog_admin__minify_form() {
	return dog_admin__get_include_contents('form-minify.php');
}

function dog_admin__minify_styles_value($field_name) {
	$defaults = dog__extend_with('minify_styles', array(
		dog__parent_css_url('shared')
	));
	$defaults = implode("\n", $defaults);
	$option = dog__get_option($field_name, $defaults);
	return dog__get_post_value_or_default($field_name, $option);
}

function dog_admin__minify_scripts_value($field_name) {
	$defaults = dog__extend_with('minify_scripts', array(
		dog__parent_js_url('vendor'),
		dog__parent_js_url('shared')
	));
	$defaults = implode("\n", $defaults);
	$option = dog__get_option($field_name, $defaults);
	return dog__get_post_value_or_default($field_name, $option);
}

function dog_ajax__minify() {
	dog__whitelist_fields(array(DOG__OPTION_MINIFY_STYLES, DOG__OPTION_MINIFY_SCRIPTS));
	dog__get_post_data(DOG_ADMIN__NAMESPACE_MINIFY);
	dog__validate_nonce(DOG_ADMIN__SECTION_MINIFY);
	dog__validate_honeypot();
	if (dog__form_is_valid()) {
		return dog_admin__minify_files();
	} else {
		$response = dog_admin__minify_form();
		return dog__ajax_response_error(array('message' => dog__alert_message(DOG__ALERT_KEY_FORM_INVALID)), $response);
	}
}

function dog_admin__minify_files() {
	$errors = array();
	$dest_dir = dog__compressed_asset_dir();
	if (!is_dir($dest_dir)) {
		if (mkdir($dest_dir, 0755, true) === false) {
			$response = dog_admin__minify_form();
			return dog__ajax_response_error(array('message' => 'Sistemul a întâmpinat o eroare la crearea directorului în cache'), $response);
		}
	}

	/***** styles *****/
	$old_styles_version = dog__get_option(DOG__OPTION_MINIFY_STYLES_VERSION);
	@unlink("{$dest_dir}/{$old_styles_version}.css");
	$styles = dog__get_post_value(DOG__OPTION_MINIFY_STYLES);
	$styles_content = '';
	if ($styles) {
		$styles_list = explode("\n", $styles);
		foreach ($styles_list as $s) {
			$s = trim($s);
			$info = wp_remote_get($s);
			if (!is_array($info) || !$info['body']) {
				array_push($errors, $s);
				continue;
			}
			$styles_content .= "{$info['body']}\n\n";
		}
		$styles_content = trim($styles_content);
		$styles_content = dog__minify_style($styles_content);
	}
	$new_styles_version = md5($styles_content);
	$dest_file = "{$dest_dir}/{$new_styles_version}.css";
	$handle = fopen($dest_file, 'w');
	if ($handle === false) {
		$response = dog_admin__minify_form();
		return dog__ajax_response_error(array('message' => 'Sistemul a întâmpinat o eroare la crearea fișierului CSS'), $response);
	}
	if (fwrite($handle, $styles_content) === false) {
		$response = dog_admin__minify_form();
		return dog__ajax_response_error(array('message' => 'Sistemul a întâmpinat o eroare la scrierea fișierului CSS'), $response);
	}
	dog__update_option(DOG__OPTION_MINIFY_STYLES, $styles, false);
	dog__update_option(DOG__OPTION_MINIFY_STYLES_VERSION, $new_styles_version, false);

	/***** scripts *****/
	$old_scripts_version = dog__get_option(DOG__OPTION_MINIFY_SCRIPTS_VERSION);
	@unlink("{$dest_dir}/{$old_scripts_version}.js");
	$scripts = dog__get_post_value(DOG__OPTION_MINIFY_SCRIPTS);
	$scripts_content = '';
	if ($scripts) {
		$scripts_list = explode("\n", $scripts);
		foreach ($scripts_list as $s) {
			$s = trim($s);
			$info = wp_remote_get($s);
			if (!is_array($info) || !$info['body']) {
				array_push($errors, $s);
				continue;
			}
			$scripts_content .= "{$info['body']}\n\n";
		}
		$scripts_content = trim($scripts_content);
		$scripts_content = dog__minify_script($scripts_content);
	}
	$new_scripts_version = md5($scripts_content);
	$dest_file = "{$dest_dir}/{$new_scripts_version}.js";
	$handle = fopen($dest_file, 'w');
	if ($handle === false) {
		$response = dog_admin__minify_form();
		return dog__ajax_response_error(array('message' => 'Sistemul a întâmpinat o eroare la crearea fișierului JS'), $response);
	}
	if (fwrite($handle, $scripts_content) === false) {
		$response = dog_admin__minify_form();
		return dog__ajax_response_error(array('message' => 'Sistemul a întâmpinat o eroare la scrierea fișierului JS'), $response);
	}
	dog__update_option(DOG__OPTION_MINIFY_SCRIPTS, $scripts, false);
	dog__update_option(DOG__OPTION_MINIFY_SCRIPTS_VERSION, $new_scripts_version, false);

	dog__clear_page_cache();

	if ($errors) {
		return dog_ajax__refresh_minify(array('message' => dog__txt('Fișierele au fost comprimate cu următoarele erori: ') . '<br /><br />' . implode('<br />', $errors)));
	}
	return dog_ajax__refresh_minify(array('message' => dog__txt('Fișierele au fost comprimate')));
}

function dog_ajax__refresh_minify($extra = null) {
	$response = dog_admin__minify_form();
	return dog__ajax_response_ok($response, $extra);
}

function dog_ajax__delete_minify() {
	$dest_dir = dog__compressed_asset_dir();
	$styles_version = dog__get_option(DOG__OPTION_MINIFY_STYLES_VERSION);
	$styles_file = "{$dest_dir}/{$styles_version}.css";
	@unlink($styles_file);
	$scripts_version = dog__get_option(DOG__OPTION_MINIFY_SCRIPTS_VERSION);
	$scripts_file = "{$dest_dir}/{$scripts_version}.js";
	@unlink($scripts_file);
	if (is_file($styles_file) || is_file($scripts_file)) {
		$response = dog_admin__minify_form();
		return dog__ajax_response_error(array('message' => 'Sistemul a întâmpinat o eroare la ștergerea fișierelor'), $response);
	}
	dog__delete_option(DOG__OPTION_MINIFY_STYLES_VERSION);
	dog__delete_option(DOG__OPTION_MINIFY_SCRIPTS_VERSION);
	dog__clear_page_cache();
	return dog_ajax__refresh_minify(array('message' => dog__txt('Fișierele comprimate au fost șterse')));
}

function dog_admin__menu_order($menu_order) {
	return dog__override_with('admin_menu_order', array('index.php', 'edit.php', 'edit.php?post_type=page', 'edit-comments.php'));
}

function dog_admin__enqueue_assets($hook) {
	if ($hook != DOG_ADMIN__MENU_HOOK) {
		return;
	}
	wp_enqueue_style('admin_styles', dog__parent_admin_url('styles.css'), null, null);
	wp_enqueue_script('jquery_cookie', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array('jquery'), null, true);
	wp_enqueue_script('base_scripts', dog__parent_js_url('shared'), array('jquery_cookie'), null, true);
	$vars = dog__extend_with('admin_js_vars', dog__js_vars());
	$nonces = dog_admin__nonces();
	wp_localize_script('base_scripts', 'dog__wp', array_merge($vars, $nonces));
	wp_enqueue_script('admin_scripts', dog__parent_admin_url('scripts.js'), array('base_scripts'), null, true);
}

add_action('admin_menu', 'dog_admin__add_menu');
add_action('admin_enqueue_scripts', 'dog_admin__enqueue_assets', 99999);
add_action('wp_ajax_' . DOG_ADMIN__WP_ACTION_AJAX_CALLBACK, 'dog__ajax_handler');
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'dog_admin__menu_order');
dog__call_x_function('admin_hooks');