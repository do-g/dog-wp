<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$value = $form_field_data['field']['value'];
unset($form_field_data['field']['value']);
$attributes = dog__attributes_array_to_html($form_field_data['field']);
?>
<<?= tag_escape($form_field_tag) ?><?= $attributes ?>><?= esc_textarea($value) ?></<?= $form_field_tag ?>>