<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (isset($data['label'])) {
	$field = $data['field'];
	$label = $data['label'];
	$text = $label['text'];
	unset($label['text']);
	$default_class = array("form-label", "form-label-{$field['tag']}", "form-label-{$field['tag']}-{$field['type']}", "form-label-id-{$field['id']}");
	if (!$field['type']) {
		unset($default_class[2]);
	}
	$label['class'] = dog__merge_form_field_classes($default_class, $label['class']);
	$attributes = dog__attributes_array_to_html($label); ?>
	<label for="<?= esc_attr($field['id']) ?>"<?= $attributes ?>><?= esc_html($text) ?></label>
<?php }