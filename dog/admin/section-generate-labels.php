<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$dog_admin__section_action = DOG_ADMIN__SECTION_GENERATE_LABELS;
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
				<input type="button" class="dog-admin--control button button-warning dog-admin--hidden" value="<?= __('Reîncarcă pagina') ?>" onclick="location.reload()">
				<input type="button" class="dog-admin--control button button-primary" value="<?= __('Detectează etichetele') ?>" onclick="dog_admin__request(this, {method: '<?= $dog_admin__section_action ?>'})">
				<input type="button" class="dog-admin--control button" value="<?= __('Reîncarcă secțiunea') ?>" onclick="dog_admin__refresh_section(this)">
			</p>
		</div>
	</div>
</div>