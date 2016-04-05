<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$dog_admin__section_action = DOG_ADMIN__SECTION_ACTION_EXPIRED_TRANSIENTS;
$dog_admin__alert_success = __('Memoria a fost curățată de înregistrări expirate');
?>
<div class="dog-admin--section" id="section-<?= $dog_admin__section_action ?>">
	<?php include(realpath(dirname(__FILE__)) . '/messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= __('Înregistrări din memoria cache cu termen de valabilitate depășit') ?></h3>
		<div class="dog-admin--box-content">
			<p>În această secțiune poți vizualiza și șterge datele memorate în cache pentru care termenul de expirare a trecut.
			Aceste informații nu mai sunt nici folosite nici necesare prin urmare pot fi șterse fără probleme.
			Mai mult, se recomandă verificarea periodică a acestei secțiuni și ștergerea atunci când este nevoie.</p>
			<div class="dog-admin--ajax-target"><?= _dog_admin__list_expired_transients() ?></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-warning dog-admin--refresh after-<?= DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH ?>" value="<?= __('Reîncarcă pagina') ?>" onclick="location.reload()">
				<input type="button" class="dog-admin--control button button-primary dog-admin--submit after-default after-<?= $dog_admin__section_action ?> after-<?= $dog_admin__section_action ?>-error" value="<?= __('Curăță memoria') ?>" onclick="dog_admin__request(this, '<?= $dog_admin__section_action ?>', null, {confirm: '<?= __('Confirmă curățarea memoriei') ?>'})">
			</p>
		</div>
	</div>
</div>