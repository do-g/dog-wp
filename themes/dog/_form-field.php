<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

$form_field_data = $tpl_data['form_field_data'];
$form_field_data['global']['content_placeholder'] = '{$content}';
$form_field_data['field']['id'] = $form_field_data['field']['id'] ? $form_field_data['field']['id'] : $form_field_data['field']['name'];

$html = array(
	'errors' => dog__get_file_output(dog__parent_file_path('_form-field-errors.php'), $form_field_data),
	'wrapper' => dog__get_file_output(dog__parent_file_path('_form-field-wrapper.php'), $form_field_data),
	'label' => dog__get_file_output(dog__parent_file_path('_form-field-label.php'), $form_field_data),
	'hint' => dog__get_file_output(dog__parent_file_path('_form-field-hint.php'), $form_field_data)
);

$default_class = array("form-element", "form-element-{$form_field_data['field']['tag']}", "form-element-{$form_field_data['field']['tag']}-{$form_field_data['field']['type']}", "form-element-id-{$form_field_data['field']['id']}");
if (!$form_field_data['field']['type']) {
	unset($default_class[2]);
}
$form_field_data['field']['class'] = dog__merge_form_field_classes($default_class, $form_field_data['field']['class']);

$form_field_data['global']['tag'] = $form_field_data['field']['tag'];
unset($form_field_data['field']['tag']);

$html['field'] = dog__get_file_output(dog__parent_file_path('_form-field-' . $form_field_data['global']['tag'] . '.php'), $form_field_data);

$ignore_keys = array('global', 'wrapper');
$final_html = '';
foreach ($form_field_data as $type => $info) {
	if (!in_array($type, $ignore_keys)) {
		$final_html .= $html[$type];
	}
}

echo $html['wrapper'] ? str_replace($form_field_data['global']['content_placeholder'], $final_html, $html['wrapper']) : $final_html;