<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Înregistrări din memoria cache cu termen de valabilitate depășit') ?></h3>
		<div class="dog-admin--box-content">
			<p>În această secțiune poți vizualiza și șterge datele memorate în cache pentru care termenul de expirare a trecut.
			Aceste informații nu mai sunt nici folosite nici necesare prin urmare pot fi șterse fără probleme.
			Mai mult, se recomandă verificarea periodică a acestei secțiuni și ștergerea atunci când este nevoie.</p>
			<div class="dog-admin--ajax-target"><?= dog_admin__list_expired_transients() ?></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary" value="<?= dog__txt_attr('Curăță memoria') ?>" onclick="dog_admin__request(this, {method: '<?= $section_name ?>'})">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__request(this, {method: '<?= DOG_ADMIN__NONCE_REFRESH_EXPIRED_TRANSIENTS ?>'})"></span>
			</p>
		</div>
	</div>
</div>