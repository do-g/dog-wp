<?php

require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');

function dog_admin__menu_order($menu_order) {
	return dog__override_with('admin_menu_order', array('index.php', 'edit.php', 'edit.php?post_type=page', 'edit-comments.php'));
}

function dog_admin__alter_top_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wpseo-menu');
}

/***** Start CSS Class Meta Box *****/

function dog_admin__post_css_class_setup() {
	add_action('add_meta_boxes', 'dog_admin__add_post_css_class_meta_box');
	add_action('save_post', 'dog_admin__save_post_css_class', 10, 2);
}

function dog_admin__add_post_css_class_meta_box() {
	add_meta_box(
		'dog-post-css-class',
		dog__txt('Personalizări de stil'),
		'dog_admin__render_post_css_class',
		array('post', 'page'),
		'side',
		'default'
	);
}

function dog_admin__render_post_css_class($object, $box) {
	$meta_key_name = DOG__ENTRY_CSS_CLASS_META_KEY;
	$meta_value = get_post_meta($object->ID, $meta_key_name, true);
	$nonce_action = $meta_key_name . 'nacce';
	$nonce_field = $meta_key_name . '_nnce';
	wp_nonce_field($nonce_action, $nonce_field); ?>
	<p>
		<label for="<?= $meta_key_name ?>"><?= dog__txt('Clase CSS') ?></label>
		<br />
		<input class="widefat" type="text" name="<?= $meta_key_name ?>" id="<?= $meta_key_name ?>" value="<?= esc_attr($meta_value) ?>" />
	</p>
<?php }

function dog_admin__save_post_css_class($post_id, $post) {
	if (isset($_POST[DOG__ENTRY_CSS_CLASS_META_KEY])) {
		dog_admin__save_css_class($post_id, $post, 'post');
	}
}

function dog_admin__save_css_class($obj_id, $obj, $obj_type) {
	$meta_key_name = DOG__ENTRY_CSS_CLASS_META_KEY;
	$nonce_action = $meta_key_name . 'nacce';
	$nonce_field = $meta_key_name . '_nnce';
	if (!isset($_POST[$nonce_field]) || !wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
    	return;
	}
	$new_meta_value = isset($_POST[$meta_key_name]) ? sanitize_text_field($_POST[$meta_key_name]) : '';
	$parts = explode(' ', $new_meta_value);
	foreach ($parts as &$p) {
		$p = sanitize_html_class($p);
	}
	$new_meta_value = implode(' ', $parts);
	$is_term = $obj_type == 'taxonomy';
	if ($is_term) {
		$meta_value = get_term_meta($obj_id, $meta_key_name, true);
	} else {
		$meta_value = get_post_meta($obj_id, $meta_key_name, true);
	}
  	if ($new_meta_value && '' == $meta_value) {
  		if ($is_term) {
  			add_term_meta($obj_id, $meta_key_name, $new_meta_value, true);
  		} else {
    		add_post_meta($obj_id, $meta_key_name, $new_meta_value, true);
    	}
	} elseif ($new_meta_value && $new_meta_value != $meta_value) {
		if ($is_term) {
			update_term_meta($obj_id, $meta_key_name, $new_meta_value);
  		} else {
    		update_post_meta($obj_id, $meta_key_name, $new_meta_value);
    	}
    } elseif ('' == $new_meta_value && $meta_value) {
    	if ($is_term) {
    		delete_term_meta($obj_id, $meta_key_name);
  		} else {
    		delete_post_meta($obj_id, $meta_key_name);
    	}
	}
}

function dog_admin__render_taxonomy_add_css_class($taxonomy_name) {
	$meta_key_name = DOG__ENTRY_CSS_CLASS_META_KEY;
	$nonce_action = $meta_key_name . 'nacce';
	$nonce_field = $meta_key_name . '_nnce';
	wp_nonce_field($nonce_action, $nonce_field); ?>
	<div class="form-field <?= $meta_key_name ?>-wrap">
		<label for="<?= $meta_key_name ?>"><?= dog__txt('Clase CSS') ?></label>
		<input name="<?= $meta_key_name ?>" id="<?= $meta_key_name ?>" type="text" value="">
		<p><?= dog__txt('Introdu aici una sau mai multe clase CSS personalizate') ?></p>
	</div>
<?php }

function dog_admin__render_taxonomy_edit_css_class($term) {
	$meta_key_name = DOG__ENTRY_CSS_CLASS_META_KEY;
	$meta_value = get_term_meta($term->term_id, $meta_key_name, true);
	$nonce_action = $meta_key_name . 'nacce';
	$nonce_field = $meta_key_name . '_nnce';
	wp_nonce_field($nonce_action, $nonce_field); ?>
	<tr class="form-field <?= $meta_key_name ?>-wrap">
		<th scope="row">
			<label for="<?= $meta_key_name ?>"><?= dog__txt('Clase CSS') ?></label>
		</th>
		<td>
			<input name="<?= $meta_key_name ?>" id="<?= $meta_key_name ?>" type="text" value="<?= esc_attr($meta_value) ?>" />
			<p class="description"><?= dog__txt('Introdu aici una sau mai multe clase CSS personalizate') ?></p>
		</td>
	</tr>
<?php }

function dog_admin__save_taxonomy_css_class($term_id) {
	if (isset($_POST[DOG__ENTRY_CSS_CLASS_META_KEY])) {
		$term = get_term($term_id);
		dog_admin__save_css_class($term_id, $term, 'taxonomy');
	}
}

/***** End CSS Class Meta Box *****/

add_action('wp_before_admin_bar_render', 'dog_admin__alter_top_bar');
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'dog_admin__menu_order');
add_action('load-post.php', 'dog_admin__post_css_class_setup');
add_action('load-post-new.php', 'dog_admin__post_css_class_setup' );
add_action('category_add_form_fields', 'dog_admin__render_taxonomy_add_css_class', 10);
add_action('category_edit_form_fields', 'dog_admin__render_taxonomy_edit_css_class', 10);
add_action('create_category', 'dog_admin__save_taxonomy_css_class');
add_action('edited_category', 'dog_admin__save_taxonomy_css_class');
dog__call_x_function('admin_hooks');