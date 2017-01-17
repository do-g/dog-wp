<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$tag = $tpl_data['tag'] ? $tpl_data['tag'] : 'div';
$classes = array('flexbox-row-fill');
if ($tpl_data['css_class']) {
	$tpl_data['css_class'] = is_array($tpl_data['css_class']) ? $tpl_data['css_class'] : array($tpl_data['css_class']);
	$classes = array_merge($tpl_data['css_class'], $classes);
}
?>
<<?= $tag ?> class="<?= implode(' ', $classes) ?>"></<?= $tag ?>>
<<?= $tag ?> class="<?= implode(' ', $classes) ?>"></<?= $tag ?>>