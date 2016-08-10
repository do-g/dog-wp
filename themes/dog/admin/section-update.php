<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Actualizări temă') ?></h3>
		<div class="dog-admin--box-content">
			<p>Verifică aici dacă există versiuni noi disponibile pentru tema de bază.
			Actualizările aduc îmbunătățiri sau noi funcționalități.
			Tema curentă a sitului este o extensie a temei de bază și depinde de aceasta.
			Atenție, există riscul ca o nouă versiune a temei de bază să fie incompatibilă cu funcționalitatea temei curente.
			Nu este recomandată actualizarea temei de bază fără asistență de specialitate.</p>
			<div class="dog-admin--ajax-target"><?= dog_admin__update_info() ?></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary key-check" value="<?= dog__txt_attr('Verifică actualizări disponibile') ?>" onclick="dog_admin__section_update(this, '<?= DOG_ADMIN__NONCE_UPDATE_CHECK ?>')">
				<input type="button" class="dog-admin--control button button-primary key-update dog-admin--hidden" value="<?= dog__txt_attr('Doresc actualizarea') ?>" onclick="dog_admin__section_update(this, '<?= $section_name ?>')">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__section_update(this, '<?= DOG_ADMIN__NONCE_UPDATE_INFO ?>')"></span>
			</p>
		</div>
	</div>
</div>