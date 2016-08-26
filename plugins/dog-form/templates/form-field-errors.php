<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$field = $tpl_data['field'];
$errors = $tpl_data['errors'];
if ($fld_errs = Dog_Form::get_field_errors($field['name'])) {
	$default_css_classes = array(
		"form-error",
		"field-error",
		"field-error-{$field['tag']}",
		"field-error-{$field['tag']}-{$field['type']}",
		"field-error-id-{$field['id']}"
	);
	if (!$field['type']) {
		unset($default_css_classes[3]);
	}
	foreach ($fld_errs as $type => $message) {
		$error_class = array_merge($default_css_classes, array("field-error-type-{$type}"));
		$errors['class'] = dog__merge_css_classes($error_class, $errors['class']);
		$attributes = dog__attributes_array_to_html($errors); ?>
		<span <?= $attributes ?>><?= esc_html($message) ?></span>
	<?php }
}