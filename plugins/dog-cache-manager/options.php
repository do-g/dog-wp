<?php
	require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
	$messages = dog__get_admin_form_messages();
	$errors = dog__get_admin_form_errors();
?>
<div class="wrap dog-admin--page">
	<h1>Opțiuni administrare memorie cache</h1>
	<?php if ($messages) {
		foreach ($messages as $m) { ?>
			<div class='updated'><p><strong><?= $m ?></strong></p></div>
		<?php }
	}
	if ($errors) {
		foreach ($errors as $e) { ?>
			<div class='error'><p><strong><?= $e ?></strong></p></div>
		<?php }
	} ?>
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