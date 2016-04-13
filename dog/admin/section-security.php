<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<div class="dog-admin--section" id="section-<?= $section_name ?>">
	<?php include dog__parent_admin_file_path('messages.php') ?>
	<div class="dog-admin--box">
		<h3><?= dog__txt('Verificări de securitate') ?></h3>
		<div class="dog-admin--box-content">
			<p>Verifică aici dacă fișierele temei sunt securizate împotriva accesului direct din afara aplicației.
			Fișierele cu extensia .php trebuie să întoarcă un răspuns securizat cererilor externe.
			În cazul în care sunt detectate fișiere cu această vulnerabilitate te rugăm să iei legătura cu un programator.</p>
			<div class="dog-admin--ajax-target"></div>
			<p class="dog-admin--controls">
				<input type="button" class="dog-admin--control button button-primary key-check" value="<?= dog__txt_attr('Verifică accesul la fișiere') ?>" onclick="dog_admin__request(this, {method: '<?= $section_name ?>'})">
				<span class="dog-admin--refresh dashicons-image-rotate" onclick="dog_admin__empty_ajax_target(this)"></span>
			</p>
		</div>
	</div>
</div>