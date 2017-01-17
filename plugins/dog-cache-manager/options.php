<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<div class="wrap dog-admin--page">
	<h1><?= dog__txt('Opțiuni administrare memorie cache') ?></h1>
	<?= dog__prepare_transient_flash_messages() ?>
	<form name="form" method="post" action="admin-post.php" class="dog-admin--form dog-form-cache-manager">
	 	<p class="page-description"><?= dog__txt('Apasă pe butonul de mai jos pentru a șterge fragmentele păstrate în memoria cache.
	 		Aceste fragmente se actualizează automat la un anumit interval de timp.
	 		Folosește această funcție doar pentru a forța o actualizare imediată.') ?></p>
 		<?php
 			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'input',
					'type' => 'hidden',
					'name' => 'action',
					'value' => 'dog_save_cm_options',
				),
			));
			Dog_Form::render_nonce_field('cm-options');
			Dog_Form::render_honeypot_field();
		?>
		<p class="submit">
			<?php
				Dog_Form::render_form_field(array(
					'field' => array(
						'tag' => 'button',
						'type' => 'submit',
						'name' => 'clear',
						'value' => dog__txt('Golește memoria cache'),
						'class' => 'button button-primary',
					),
				));
			?>
		</p>
	</form>
</div>