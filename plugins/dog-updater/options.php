<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<div class="wrap dog-admin--page">
	<h1><?= dog__txt('Opțiuni actualizare') ?></h1>
	<?= dog__prepare_transient_flash_messages() ?>
	<form name="form" method="post" action="admin-post.php" class="dog-admin--form dog-form-updater">
	 	<p class="page-description"><?= dog__txt('Apasă pe butonul de mai jos pentru a verifica actualizările disponibile pentru module și teme din grupul DOG.
	 		Atenție aceste actualizări, deși de dorit, pot afecta compatibilitatea sitului cu modulele de care depinde.
	 		Nu este recomandată actualizarea fără consultanță tehnică.') ?></p>
 		<?php
 			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'input',
					'type' => 'hidden',
					'name' => 'action',
					'value' => 'dog_save_up_options',
				),
			));
			Dog_Form::render_nonce_field('up-options');
			Dog_Form::render_honeypot_field();
		?>
		<p class="submit">
			<?php
				Dog_Form::render_form_field(array(
					'field' => array(
						'tag' => 'button',
						'type' => 'submit',
						'name' => 'check',
						'value' => dog__txt('Verifică actualizări disponibile'),
						'class' => 'button button-primary',
					),
				));
			?>
		</p>
	</form>
</div>