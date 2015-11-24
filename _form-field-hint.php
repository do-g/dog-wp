<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (isset($data['hint'])) {
	$field = $data['field'];
	$hint = $data['hint'];
	$text = $hint['text'];
	unset($hint['text']);
	$default_class = array("form-hint", "form-hint-{$field['tag']}", "form-hint-{$field['tag']}-{$field['type']}", "form-hint-id-{$field['id']}");
	if (!$field['type']) {
		unset($default_class[2]);
	}
	$hint['class'] = dog__merge_form_field_classes($default_class, $hint['class']);
	$attributes = dog__attributes_array_to_html($hint); ?>
	<span<?= $attributes ?>><?= esc_html($text) ?></span>
<?php }