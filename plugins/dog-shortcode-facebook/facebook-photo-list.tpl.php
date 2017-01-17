<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$type = $tpl_data['type'];
$mode = $tpl_data['mode'];
$item = $tpl_data['item'];
$config = $tpl_data['config'];
$photo_link = $config['gallery_rel'] ? null : $item->link;
$classes = array(
	"dog-sc-item",
	"facebook-sc-item",
	"object-{$type}",
	"mode-{$mode}",
	$config['css_class'][$type],
);
$link_attrs = array(
	'data-id' => $item->id,
	'href' => $config['url'][$type] ? dog__replace_template_vars($config['url'][$type], array('id' => $item->id)) : $photo_link,
	'rel' => $config['gallery_rel'],
	'title' => $item->name,
);
$src_set = array();
foreach ($item->images as $img_size) {
	array_push($src_set, $img_size->width);
	$link_attrs["data-src-{$img_size->width}"] = $img_size->source;
}
$link_attrs["data-src-set"] = implode(',', $src_set);
?>
<div class="<?= dog__to_css_class($classes) ?>">
	<a <?= dog__attributes_array_to_html($link_attrs) ?>>
		<div style="background-image: url('<?= $item->thumbnail ?>');"></div>
		<h3><?= $item->name ?></h3>
	</a>
</div>