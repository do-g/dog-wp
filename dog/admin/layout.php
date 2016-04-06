<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="wrap">
	<h1><?= __('Opțiuni temă') ?></h1>
	<?php $admin_sections = dog_admin__get_sections();
	if ($admin_sections) {
		foreach ($admin_sections as $section_name => $section_title) {
			include DOG_ADMIN__SECTION_FILE_PREFIX . $section_name . '.php';
		}
	} ?>
</div>