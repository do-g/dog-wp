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
    $admin_sections = dog__get_admin_sections();
    if ($admin_sections) {
    	foreach ($admin_sections as $name => $title) {
    		add_submenu_page($menu_slug, $title, $title, $capability, 'admin.php?page=' . $menu_slug . '#section-' . $name);
    	}
    }
}

function dog_admin__theme_options() {
	include 'layout.php';
}

function dog_admin__get_custom_nonces() {
	global $dog_admin__custom_nonces;
	return $dog_admin__custom_nonces;
}

function dog_admin__nonces() {
	$nonces = array();
	$admin_sections = dog__get_admin_sections();
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
	global $dog__theme_labels_file;
	$all_labels = array();
	$themes = array_reverse(dog__get_dog_theme_names());
	if ($themes) {
		$theme_files = array();
		$output = array("<?php\n");
		foreach ($themes as $theme) {
			$labels = dog_admin__get_path_labels(get_theme_root(), $theme, $theme_files);
			$all_labels = array_merge($all_labels, $labels);
			$output = array_merge($output, $labels);
		}
		file_put_contents($dog__theme_labels_file, implode('', $output));
	}
	$plugins = dog__get_dog_plugin_names();
	if ($plugins) {
		foreach ($plugins as $plugin) {
			$output = array("<?php\n");
			$labels = dog_admin__get_path_labels(WP_PLUGIN_DIR, $plugin);
			$all_labels = array_merge($all_labels, $labels);
			$output = array_merge($output, $labels);
			$dest = WP_PLUGIN_DIR . "/{$plugin}/_pll_labels.php";
			file_put_contents($dest, implode('', $output));
			copy($dest, str_replace('.php', ".{$plugin}.bak", $dog__theme_labels_file));
		}
	}
	$response = dog__txt('Am găsit următoarele etichete (${n}):', array('n' => count($all_labels)));
	$response = dog__string_to_html_tag($response, 'p');
	$response .= dog__string_to_html_tag(implode('<br />', $all_labels), 'pre');
	return dog__ajax_response_ok($response);
}

function dog_admin__get_path_labels($path, $name, &$all_translated_files = array()) {
	$path = "{$path}/{$name}";
	$content = $labels = $keys = array();
	if (!is_dir($path)) {
		return $content;
	}
	$ignore_files = array('_pll_labels.php');
	$pattern = "{$path}/*.php";
	$files = glob($pattern, GLOB_NOSORT);
	if ($files) {
		foreach ($files as $file) {
			$file_name = basename($file);
			if (in_array($file_name, $ignore_files) || in_array($file_name, $all_translated_files)) {
				continue;
			}
			array_push($all_translated_files, $file_name);
		    $labels = array_merge($labels, dog__extract_file_labels($file));
		}
	}
	if ($labels) {
		foreach ($labels as $label) {
			$key = sanitize_title($label);
			if (!in_array($key, $keys)) {
				array_push($content, "pll_register_string('{$key}', '{$label}', '{$name}', true);\n");
				array_push($keys, $key);
			}
		}
	}
	return $content;
}

function dog_admin__get_file_output($filename, $tpl_data = null) {
	$filepath = dog__parent_admin_file_path($filename);
	return dog__get_file_output($filepath, $tpl_data);
}

function dog_ajax__security() {
	$issues = $all = array();
	$themes = dog__get_dog_theme_names();
	if ($themes) {
		foreach ($themes as $theme) {
			$all = array_merge($all, dog_admin__check_path_security(get_theme_root() . "/{$theme}", $issues));
		}
	}
	$plugins = dog__get_dog_plugin_names();
	if ($plugins) {
		foreach ($plugins as $plugin) {
			$all = array_merge($all, dog_admin__check_path_security(WP_PLUGIN_DIR . "/{$plugin}", $issues));
		}
	}
	$response = dog__txt('Am găsit ${n} fișiere nesecurizate din totalul de ${t}', array('n' => count($issues), 't' => count($all)));
	if ($issues) {
		$response = dog__string_to_html_tag($response, 'p');
		$response .= implode('<br />', $issues);
	}
	$response = dog__string_to_html_tag($response, 'pre');
	return dog__ajax_response_ok($response);
}

function dog_admin__check_path_security($path, &$issues = array()) {
	$pattern = '/^.+\.php$/i';
	$ignore_files = array('_pll_labels.php');
	$php_files = dog__search_files($path, $pattern);
	if ($php_files) {
		foreach ($php_files as $i => $file) {
			$name = basename($file);
			if (in_array($name, $ignore_files)) {
				unset($php_files[$i]);
				continue;
			}
			$fragment = str_replace(WP_CONTENT_DIR, '', $file);
			$url = WP_CONTENT_URL . $fragment;
			$response = wp_remote_get($url);
			if ($response && (!empty($response['body']) || $response['response']['code'] != 404)) {
				array_push($issues, '<a href="' . $url . '" target="_blank">' . $fragment . '</a>');
			}
		}
	}
	return $php_files;
}

function dog_admin__minify_form() {
	return dog_admin__get_file_output('form-minify.php');
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

function dog_admin__fix_svg_size() {
	echo '<style>
	    svg, img[src*=".svg"] {
	    	min-width: 50px !important;
	      	min-height: 50px !important;
	      	max-width: 150px !important;
	      	max-height: 150px !important;
	    }
	</style>';
}

add_action('admin_head', 'dog_admin__fix_svg_size');
add_action('admin_menu', 'dog_admin__add_menu');
add_action('admin_enqueue_scripts', 'dog_admin__enqueue_assets', 99999);
add_action('wp_ajax_' . DOG_ADMIN__WP_ACTION_AJAX_CALLBACK, 'dog__ajax_handler');
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'dog_admin__menu_order');
dog__call_x_function('admin_hooks');