<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$link_attrs = array(
	'data-id' => $tpl_data['item']->id,
);
if ($tpl_data['config']['url']['playlist']) {
	$link_attrs['href'] = dog__replace_template_vars($tpl_data['config']['url']['playlist'], array('id' => $tpl_data['item']->id));
}
?>
<div class="youtube-sc-item playlist <?= $tpl_data['config']['css_class']['playlist'] ?>">
	<a <?= dog__attributes_array_to_html($link_attrs) ?>>
		<div style="background-image: url('<?= $tpl_data['item']->snippet->thumbnails->high->url ?>');"></div>
		<h3><?= $tpl_data['item']->snippet->title ?></h3>
	</a>
</div>