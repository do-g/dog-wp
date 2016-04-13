<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Etichete') ?></h3>
		<div class="dog-admin--box-content">
			<p>Etichetele sunt acele fragmente text care apar pe sit dar nu fac parte din conținut.
			Dacă este cazul acestea trebuie traduse în toate limbile sitului.
			Folosește această secțiune pentru a genera automat lista de etichete în vederea traducerii.
			Sistemul va citi fișierele temei și va extrage etichetele pentru a putea fi modificate.
			După finalizarea procesului lista de etichete va fi disponibilă în secțiunea <a href="/wp-admin/options-general.php?page=mlang&tab=strings&group=theme">Traduceri</a>.
			Apasă butonul de mai jos pentru a începe căutarea și procesarea etichetelor.</p>
			<div class="dog-admin--ajax-target"></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary" value="<?= dog__txt_attr('Detectează etichetele') ?>" onclick="dog_admin__request(this, {method: '<?= $section_name ?>'})">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__empty_ajax_target(this)"></span>
			</p>
		</div>
	</div>
</div>