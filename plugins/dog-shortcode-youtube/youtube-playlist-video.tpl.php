<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$gallery = $tpl_data['attrs']['gallery'] ? $tpl_data['attrs']['gallery'] : $tpl_data['item']->snippet->playlistId;
$link_attrs = array(
	'data-vid' => $tpl_data['item']->snippet->resourceId->videoId,
	'data-pid' => $tpl_data['item']->snippet->playlistId,
	'rel' => "{$tpl_data['config']['gallery_rel']}[{$gallery}]",
);
if ($tpl_data['config']['url']['playlist_video']) {
	$link_attrs['href'] = dog__replace_template_vars($tpl_data['config']['url']['playlist_video'], array(
		'vid' => $tpl_data['item']->snippet->resourceId->videoId,
		'pid' => $tpl_data['item']->snippet->playlistId,
	));
}
?>
<div class="youtube-sc-item playlist-video <?= $tpl_data['config']['css_class']['playlist_video'] ?>">
	<a <?= dog__attributes_array_to_html($link_attrs) ?>>
		<div style="background-image: url('<?= $tpl_data['item']->snippet->thumbnails->high->url ?>');"></div>
		<h3><?= $tpl_data['item']->snippet->title ?></h3>
	</a>
</div>