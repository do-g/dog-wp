<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Combină și comprimă fișierele statice') ?></h3>
		<div class="dog-admin--box-content">
			<p>Se recomandă ca fișierele statice (în general cu extensia .js și .css) să fie combinate împreună și comprimate.
			Acest lucru ajută la optimizarea timpului de răspuns al paginilor care au de descărcat mai puține fișiere de dimensiuni mai reduse.
			Nu se recomandă folosirea acestei opțiuni dacă situl este în dezvoltare ci doar după lansare.
			Alege mai jos ce fișiere vor fi incluse apoi apasă butonul pentru a le comprima.</p>
			<div class="dog-admin--ajax-target"><?= dog_admin__minify_form() ?></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary" value="<?= dog__txt_attr('Comprimă fișierele') ?>" onclick="dog_admin__submit(this, {method: '<?= $section_name ?>'})">
				<input type="button" class="dog-admin--control button" value="<?= dog__txt_attr('Șterge compresia') ?>" onclick="dog_admin__request(this, {method: '<?= DOG_ADMIN__NONCE_DELETE_MINIFY ?>'})">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__request(this, {method: '<?= DOG_ADMIN__NONCE_REFRESH_MINIFY ?>'})"></span>
			</p>
		</div>
	</div>
</div>