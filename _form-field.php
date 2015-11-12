<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$fdata = $form_field_data;
$tag = $fdata['field']['tag'];
unset($fdata['field']['tag']);
$field_name = $fdata['field']['name'];
$fdata['field']['id'] = $fdata['field']['id'] ? $fdata['field']['id'] : $field_name;
?>
<?php if (isset($fdata['container'])) {
	$default_class = array("form-field", "form-field-{$tag}", "form-field-{$tag}-{$fdata['field']['type']}");
	$fdata['container']['class'] = dog__merge_form_field_classes($default_class, $fdata['container']['class']);
	$attributes = dog__attributes_array_to_html($fdata['container']); ?>
	<div<?= $attributes ?>>
<?php } ?>
<?php if (isset($fdata['label'])) {
	$text = $fdata['label']['text'];
	unset($fdata['label']['text']);
	$default_class = array("form-label", "form-label-{$tag}", "form-label-{$tag}-{$fdata['field']['type']}");
	$fdata['label']['class'] = dog__merge_form_field_classes($default_class, $fdata['label']['class']);
	$attributes = dog__attributes_array_to_html($fdata['label']); ?>
	<label for="<?= esc_attr($fdata['field']['id']) ?>"<?= $attributes ?>><?= esc_html($text) ?></label>
<?php } ?>
<?php if ($fld_errs = dog__get_field_errors($field_name)) { ?>
	<?php foreach ($fld_errs as $type => $message) { ?>
		<p class="form-error field-error field-error-<?= esc_attr($type) ?>"><?= esc_html($message) ?></p>
	<?php } ?>
<?php } ?>
<?php
$default_class = array("form-element", "form-element-{$tag}", "form-element-{$tag}-{$fdata['field']['type']}");
$fdata['field']['class'] = dog__merge_form_field_classes($default_class, $fdata['field']['class']);
set_query_var('form_field_data', $fdata);
set_query_var('form_field_tag', $tag);
get_template_part('_form-field-' . $tag);
?>
<?php if (isset($fdata['container'])) { ?>
	</div>
<?php } ?>