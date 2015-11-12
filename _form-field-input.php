<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$attributes = dog__attributes_array_to_html($form_field_data['field']);
?>
<<?= tag_escape($form_field_tag) ?><?= $attributes ?> />