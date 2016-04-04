<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$dog_admin__section_action = DOG_ADMIN__SECTION_ACTION_GENERATE_LABELS;
$dog_admin__alert_success = __('Etichetele au fost generate') . '. <a href="/wp-admin/options-general.php?page=mlang&tab=strings">' . __('Click aici pentru a modifica') . '</a>';
?>
<div class="dog-admin--section" id="section-<?= $dog_admin__section_action ?>">
	<?php include(realpath(dirname(__FILE__)) . '/messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= __('Etichete') ?></h3>
		<div class="dog-admin--box-content">
			<p>Etichetele sunt acele fragmente text care apar pe sit dar nu fac parte din conținut.
			Folosește această secțiune pentru a genera automat lista de etichete prezente în tema sitului.
			Sistemul va citi fișierele temei și va extrage etichetele pentru a putea fi modificate.
			După finalizarea procesului lista de etichete va fi disponibilă în secțiunea <a href="/wp-admin/options-general.php?page=mlang&tab=strings&group=theme">Limbi disponibile</a>.
			Apasă butonul de mai jos pentru a începe căutarea și procesarea etichetelor.</p>
			<div class="dog-admin--ajax-target"></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-warning dog-admin--refresh after-<?= DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH ?>" value="<?= __('Reîncarcă pagina') ?>" onclick="location.reload()">
				<input type="button" class="dog-admin--control button button-primary dog-admin--submit after-default after-<?= $dog_admin__section_action ?> after-<?= $dog_admin__section_action ?>-error" value="<?= __('Detectează etichetele') ?>" onclick="dog_admin__request(this, '<?= $dog_admin__section_action ?>')">
				<input type="button" class="dog-admin--control button dog-admin--cancel after-<?= $dog_admin__section_action ?>" value="<?= __('Ascunde rezultatul') ?>" onclick="dog_admin__empty_ajax_target(this, 'default')">
			</p>
		</div>
	</div>
</div>