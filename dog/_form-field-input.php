<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$field = $data['field'];
$attributes = dog__attributes_array_to_html($field);
?>
<<?= tag_escape($data['global']['tag']) ?><?= $attributes ?> />