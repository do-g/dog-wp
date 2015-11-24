<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (isset($data['wrapper'])) {
	$field = $data['field'];
	$wrapper = $data['wrapper'];
	$default_class = array("form-field", "form-field-{$field['tag']}", "form-field-{$field['tag']}-{$field['type']}", "form-field-id-{$field['id']}");
	if (!$field['type']) {
		unset($default_class[2]);
	}
	if (dog__get_field_errors($field['name'])) {
		array_push($default_class, 'has-error');
	}
	$wrapper['class'] = dog__merge_form_field_classes($default_class, $wrapper['class']);
	$attributes = dog__attributes_array_to_html($wrapper); ?>
	<div<?= $attributes ?>><?= $data['global']['content_placeholder'] ?></div>
<?php }