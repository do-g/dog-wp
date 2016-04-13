<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Configurare memorie cache') ?></h3>
		<div class="dog-admin--box-content">
			<p>Memoria cache ajută la optimizarea timpului de încărcare a paginilor.
			Această memorie persistă pe o perioadă îndelungată și este reînprospătată automat la un anumit interval de timp.
			În această secțiune poți configura comportamentul sistemului în acest sens.</p>
			<div class="dog-admin--ajax-target"><?= dog_admin__cache_settings_form() ?></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary" value="<?= dog__txt_attr('Salvează opțiunile') ?>" onclick="dog_admin__submit(this, {method: '<?= $section_name ?>'})">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__request(this, {method: '<?= DOG_ADMIN__NONCE_REFRESH_CACHE_SETTINGS ?>'})"></span>
			</p>
		</div>
	</div>
</div>