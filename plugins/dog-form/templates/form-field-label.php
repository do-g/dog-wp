<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
if (isset($tpl_data['label'])) {
	$field = $tpl_data['field'];
	$label = $tpl_data['label'];
	$text = $label['text'];
	unset($label['text']);
	$default_css_classes = array(
		"form-label",
		"form-label-{$field['tag']}",
		"form-label-{$field['tag']}-{$field['type']}",
		"form-label-id-{$field['id']}"
	);
	if (!$field['type']) {
		unset($default_css_classes[2]);
	}
	$label['class'] = dog__merge_css_classes($default_css_classes, $label['class']);
	$attributes = dog__attributes_array_to_html($label); ?>
	<label for="<?= esc_attr($field['id']) ?>" <?= $attributes ?>><?= esc_html($text) ?><?php if ($field['required']) { ?>
		<span class="required">*</span>
	<?php } ?></label>
<?php }