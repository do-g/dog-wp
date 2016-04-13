<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Pagini memorate în cache') ?></h3>
		<div class="dog-admin--box-content">
			<p>Pentru optimizarea timpului de încărcare a paginilor sistemul reține în memorie aspectul paginilor.
			Următoarea dată când un utilizator va accesa aceeași pagină sistemul va afișa conținutul direct din memoria cache evitând astfel întreaga reprocesare.
			Memoria cache este reînprospătată automat la un anumit interval de timp.
			După ce o pagină a fost memorată modificările ulterioare de conținut nu vor fi vizibile decât după expirarea memoriei curente.
			Dacă se dorește afișarea imediată a modificărilor atunci trebuie golită memoria.
			Apasă butonul de mai jos pentru a șterge toate paginile memorarate în cache.</p>
			<div class="dog-admin--ajax-target"><?= dog_admin__cache_output_form() ?></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary" value="<?= dog__txt_attr('Golește memoria') ?>" onclick="dog_admin__request(this, {method: '<?= $section_name ?>'}, {confirm: '<?= dog__txt_attr('Confirmă golirea memoriei') ?>'})">
				<input type="button" class="dog-admin--control button button-primary" value="<?= dog__txt_attr('Șterge înregistrările selectate') ?>" onclick="dog_admin__section_cache_output(this, '<?= DOG_ADMIN__NONCE_CACHE_OUTPUT_DELETE ?>', {confirm: '<?= dog__txt_attr('Confirmă ștergerea înregistrărilor') ?>'})">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__request(this, {method: '<?= DOG_ADMIN__NONCE_REFRESH_CACHE_OUTPUT ?>'})"></span>
			</p>
		</div>
	</div>
</div>