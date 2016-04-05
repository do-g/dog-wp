<div class="wrap">
	<h1><?= __('Opțiuni temă') ?></h1>
	<?php if ($dog_admin__sections) {
		foreach ($dog_admin__sections as $section) {
			include DOG_ADMIN__SECTION_FILE_PREFIX . $section . '.php';
		}
	} ?>
</div>