<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$dog_admin__section_action = DOG_ADMIN__SECTION_ACTION_CACHE_SETTINGS;
$dog_admin__alert_success = __('Configurarea memoriei cache salvată cu succes');
?>
<div class="dog-admin--section" id="section-<?= $dog_admin__section_action ?>">
	<?php include(realpath(dirname(__FILE__)) . '/messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= __('Configurare memorie cache') ?></h3>
		<div class="dog-admin--box-content">
			<p>Memoria cache ajută la optimizarea timpului de încărcare a paginilor.
			Această memorie persistă pe o perioadă îndelungată și este reînprospătată automat la un anumit interval de timp.
			În această secțiune poți configura comportamentul sistemului în acest sens.</p>
			<div class="dog-admin--ajax-target">
				<?php include(realpath(dirname(__FILE__)) . '/form-cache-settings.php') ?>
			</div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-warning dog-admin--refresh after-<?= DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH ?>" value="<?= __('Reîncarcă pagina') ?>" onclick="location.reload()">
				<input type="button" class="dog-admin--control button button-primary dog-admin--submit after-default after-<?= $dog_admin__section_action ?> after-<?= $dog_admin__section_action ?>-error" value="<?= __('Salvează opțiunile') ?>" onclick="dog_admin__submit(this, '<?= $dog_admin__section_action ?>')">
			</p>
		</div>
	</div>
</div>