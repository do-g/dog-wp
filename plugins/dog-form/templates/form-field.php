<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');

$field_info['global']['content_placeholder'] = '{$content}';
$field_info['field']['id'] = $field_info['field']['id'] ? $field_info['field']['id'] : $field_info['field']['name'];

$html = array(
	'errors'  => dog__get_file_output(dog__sibling_path('form-field-errors.php',  __FILE__), $field_info),
	'wrapper' => dog__get_file_output(dog__sibling_path('form-field-wrapper.php', __FILE__), $field_info),
	'label'   => dog__get_file_output(dog__sibling_path('form-field-label.php',   __FILE__), $field_info),
	'hint'    => dog__get_file_output(dog__sibling_path('form-field-hint.php',    __FILE__), $field_info),
);

$default_css_classes = array(
	"form-element",
	"form-element-{$field_info['field']['tag']}",
	"form-element-{$field_info['field']['tag']}-{$field_info['field']['type']}",
	"form-element-id-{$field_info['field']['id']}"
);
if (!$field_info['field']['type']) {
	unset($default_css_classes[2]);
}
$field_info['field']['class'] = dog__merge_css_classes($default_css_classes, $field_info['field']['class']);

$field_info['global']['tag'] = $field_info['field']['tag'];
unset($field_info['field']['tag']);

$html['field'] = dog__get_file_output(dog__sibling_path('form-field-' . $field_info['global']['tag'] . '.php', __FILE__), $field_info);

$ignore_keys = array('global', 'wrapper');
$final_html = '';
foreach ($field_info as $type => $info) {
	if (!in_array($type, $ignore_keys)) {
		$final_html .= $html[$type];
	}
}

echo $html['wrapper'] ? str_replace($field_info['global']['content_placeholder'], $final_html, $html['wrapper']) : $final_html;