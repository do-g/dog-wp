<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$dog_admin__section_action = DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT;
$dog_admin__selection_action = DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT_DELETE;
$dog_admin__alert_success = __('Memoria paginilor a fost golită');
$dog_admin__alert_success2 = __('Ștergerea din memorie s-a finalizat cu succes');
?>
<div class="dog-admin--section" id="section-<?= $dog_admin__section_action ?>">
	<?php include(realpath(dirname(__FILE__)) . '/messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= __('Pagini memorate în cache') ?></h3>
		<div class="dog-admin--box-content">
			<p>Pentru optimizarea timpului de încărcare a paginilor sistemul reține în memorie aspectul paginii pentru o anumită adresă URL.
			Următoarea dată când un utilizator va accesa aceeași adresă sistemul va afișa pagina direct din memoria cache evitând astfel întreaga reprocesare.
			Memoria cache este reînprospătată automat la un anumit interval de timp.
			După ce o pagină a fost memorată modificările ulterioare de conținut nu vor fi vizibile decât după expirarea memoriei curente.
			Dacă se dorește afișarea imediată a modificărilor atunci trebuie golită memoria.
			Apasă butonul de mai jos pentru a șterge toate paginile memorarate în cache.</p>
			<div class="dog-admin--ajax-target">
				<form method="post" class="dog-admin--ajaxform" id="form-<?= $dog_admin__section_action ?>">
					<?= _dog_admin__list_output_cache() ?>
				</form>
			</div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-warning dog-admin--refresh after-<?= DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH ?>" value="<?= __('Reîncarcă pagina') ?>" onclick="location.reload()">
				<input type="button" class="dog-admin--control button button-primary dog-admin--submit after-all" value="<?= __('Golește memoria') ?>" onclick="dog_admin__request(this, '<?= $dog_admin__section_action ?>', null, {confirm: '<?= __('Confirmă golirea memoriei') ?>'})">
				<input type="button" class="dog-admin--control button button-primary dog-admin--submit after-all" value="<?= __('Șterge înregistrările selectate') ?>" onclick="dog_admin__submit(this, '<?= $dog_admin__selection_action ?>', null, {validate_not_empty: true, confirm: '<?= __('Confirmă ștergerea înregistrărilor') ?>'})">
			</p>
		</div>
	</div>
</div>