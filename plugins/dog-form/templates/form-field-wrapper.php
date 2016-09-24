<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
if (isset($tpl_data['wrapper'])) {
	$field = $tpl_data['field'];
	$wrapper = $tpl_data['wrapper'];
	$default_css_classes = array(
		"form-field",
		"form-field-{$field['tag']}",
		"form-field-{$field['tag']}-{$field['type']}",
		"form-field-id-{$field['id']}"
	);
	if (!$field['type']) {
		unset($default_css_classes[2]);
	}
	if (!Dog_Form::field_is_valid($field['name'])) {
		array_push($default_css_classes, 'has-errors');
	}
	$wrapper['class'] = dog__merge_css_classes($default_css_classes, $wrapper['class']);
	$attributes = dog__attributes_array_to_html($wrapper); ?>
	<div <?= $attributes ?>><?= $tpl_data['global']['content_placeholder'] ?></div>
<?php }