<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$index = $tpl_data['index'];
$type = $tpl_data['type'];
$mode = $tpl_data['mode'];
$item = $tpl_data['item'];
$attrs = $tpl_data['attrs'];
$config = $tpl_data['config'];
$classes = array(
	"dog-sc-item",
	"gdrive-sc-item",
	"object-{$type}",
	"mode-{$mode}",
	"ratio-{$attrs[Dog_Shortcode_GoogleDrive::ATTR_RATIO][$index]}",
	$config['css_class'][$type],
	$attrs[Dog_Shortcode_GoogleDrive::ATTR_CLASS][$index],
);
$holder_attrs = array(
	'data-id' => $item->id,
	'style' => '',
);
if ($attrs[Dog_Shortcode_GoogleDrive::ATTR_WIDTH][$index]) {
	$holder_attrs['style'] .= "width: {$attrs[Dog_Shortcode_GoogleDrive::ATTR_WIDTH][$index]}px;";
}
if ($attrs[Dog_Shortcode_GoogleDrive::ATTR_HEIGHT][$index]) {
	$holder_attrs['style'] .= "height: {$attrs[Dog_Shortcode_GoogleDrive::ATTR_HEIGHT][$index]}px; padding-bottom: 0;";
}
switch ($type) {
	case Dog_Shortcode_GoogleDrive::OBJECT_DOC:
		$embed = "https://docs.google.com/document/d/e/{$item->id}/pub?embedded=true";
		break;
	case Dog_Shortcode_GoogleDrive::OBJECT_SHEET:
		$embed = "https://docs.google.com/spreadsheets/d/e/{$item->id}/pubhtml?widget=true&headers=false";
		if (isset($attrs[Dog_Shortcode_GoogleDrive::ATTR_SHEET][$index])) {
			$embed .= "&gid={$attrs[Dog_Shortcode_GoogleDrive::ATTR_SHEET][$index]}&single=true";
		}
		break;
	case Dog_Shortcode_GoogleDrive::OBJECT_SLIDE:
		$start = $attrs[Dog_Shortcode_GoogleDrive::ATTR_START][$index] ? $attrs[Dog_Shortcode_GoogleDrive::ATTR_START][$index] : 0;
		$loop = $attrs[Dog_Shortcode_GoogleDrive::ATTR_LOOP][$index] ? $attrs[Dog_Shortcode_GoogleDrive::ATTR_LOOP][$index] : 0;
		$delay = ($attrs[Dog_Shortcode_GoogleDrive::ATTR_DELAY][$index] ? $attrs[Dog_Shortcode_GoogleDrive::ATTR_DELAY][$index] : 3) * 1000;
		$embed = "https://docs.google.com/presentation/d/e/{$item->id}/embed?start={$start}&loop={$loop}&delayms={$delay}";
		break;
	case Dog_Shortcode_GoogleDrive::OBJECT_FORM:
		$embed = "https://docs.google.com/forms/d/e/{$item->id}/viewform?embedded=true";
		break;
	default:
		$embed = "https://docs.google.com/viewer?srcid={$item->id}&pid=explorer&efh=false&a=v&chrome=false&embedded=true";
		break;
}
?>
<div class="<?= dog__to_css_class($classes) ?>" <?= dog__attributes_array_to_html($holder_attrs) ?>>
	<iframe src="<?= $embed ?>" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>
</div>