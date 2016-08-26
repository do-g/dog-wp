<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
if (isset($tpl_data['hint'])) {
	$field = $tpl_data['field'];
	$hint = $tpl_data['hint'];
	$text = $hint['text'];
	unset($hint['text']);
	$default_css_classes = array(
		"form-hint",
		"form-hint-{$field['tag']}",
		"form-hint-{$field['tag']}-{$field['type']}",
		"form-hint-id-{$field['id']}"
	);
	if (!$field['type']) {
		unset($default_css_classes[2]);
	}
	$hint['class'] = dog__merge_css_classes($default_css_classes, $hint['class']);
	$attributes = dog__attributes_array_to_html($hint); ?>
	<span <?= $attributes ?>><?= esc_html($text) ?></span>
<?php }