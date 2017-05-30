<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$type = $tpl_data['type'];
$mode = $tpl_data['mode'];
$item = $tpl_data['item'];
$config = $tpl_data['config'];
$classes = array(
	"dog-sc-item",
	"facebook-sc-item",
	"object-{$type}",
	"mode-{$mode}",
	$config['css_class'][$type],
);
$link_attrs = array(
	'data-id' => $item->album->id,
	'href' => dog__replace_template_vars($config['url'][$type], array('id' => $item->album->id)),
);
?>
<div class="<?= dog__to_css_class($classes) ?>">
	<a <?= dog__attributes_array_to_html($link_attrs) ?>>
		<div style="background-image: url('<?= $item->album->thumbnail ?>');"></div>
		<h3><?= $item->album->name ?></h3>
	</a>
</div>