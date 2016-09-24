<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$rel = $tpl_data['config']['gallery_rel'];
$gallery = $tpl_data['attrs']['gallery'];
$link_attrs = array(
	'data-id' => $tpl_data['item']->id,
	'rel' => $gallery ? "{$rel}[{$gallery}]" : $rel,
);
if ($tpl_data['config']['url']['video']) {
	$link_attrs['href'] = dog__replace_template_vars($tpl_data['config']['url']['video'], array('id' => $tpl_data['item']->id));
}
?>
<div class="youtube-sc-item video <?= $tpl_data['config']['css_class']['video'] ?>">
	<a <?= dog__attributes_array_to_html($link_attrs) ?>>
		<div style="background-image: url('<?= $tpl_data['item']->snippet->thumbnails->high->url ?>');"></div>
		<h3><?= $tpl_data['item']->snippet->title ?></h3>
	</a>
</div>