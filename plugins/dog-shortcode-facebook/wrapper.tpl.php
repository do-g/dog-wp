<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$content = $tpl_data['content'];
$type = $tpl_data['type'];
$obj_type = $tpl_data['obj_type'];
$mode = $tpl_data['mode'];
$config = $tpl_data['config'];
$classes = array(
	"dog-sc-{$type}",
	"facebook-sc-{$type}",
	$config['css_class'][$type],
);
$fill_classes = array(
	"dog-sc-item",
	"facebook-sc-item",
	"flexbox-row-fill",
);
if ($obj_type) {
	array_splice($classes, -1, 0, "object-{$obj_type}");
	array_splice($fill_classes, -1, 0, array("object-{$obj_type}", $config['css_class'][$obj_type]));
}
if ($mode) {
	array_splice($classes, -1, 0, "mode-{$mode}");
	array_splice($fill_classes, -2, 0, "mode-{$mode}");
}
?>
<section class="<?= dog__to_css_class($classes) ?>">
	<?= $content ?>
	<div class="<?= dog__to_css_class($fill_classes) ?>"></div>
	<div class="<?= dog__to_css_class($fill_classes) ?>"></div>
</section>