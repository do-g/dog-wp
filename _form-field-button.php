<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$field = $data['field'];
$value = $field['value'];
unset($field['value']);
$attributes = dog__attributes_array_to_html($field);
?>
<<?= tag_escape($data['global']['tag']) ?><?= $attributes ?>><?= esc_textarea($value) ?></<?= $data['global']['tag'] ?>>