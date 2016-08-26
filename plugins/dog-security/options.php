<?php
	require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
	$issues = get_transient(DOG_ADMIN__TRANSIENT_FORM_RESPONSE);
	delete_transient(DOG_ADMIN__TRANSIENT_FORM_RESPONSE);
	$messages = dog__get_admin_form_messages();
	$errors = dog__get_admin_form_errors();
?>
<div class="wrap dog-admin--page">
	<h1>Opțiuni securitate</h1>
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
	<form name="form" method="post" action="admin-post.php" class="dog-admin--form dog-form-labels">
	 	<p class="page-description"><?= dog__txt('Verifică aici dacă fișierele temelor și modulelor sunt securizate împotriva accesului direct din afara aplicației.
		Fișierele cu extensia .php trebuie să întoarcă un răspuns securizat cererilor externe.
		În cazul în care sunt detectate fișiere cu această vulnerabilitate te rugăm să iei legătura cu un programator.') ?></p>
		<?php
			if ($issues) {
				echo dog__string_to_html_tag(implode('<br />', $issues), 'pre');
			}
 			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'input',
					'type' => 'hidden',
					'name' => 'action',
					'value' => 'dog_save_sc_options',
				),
			));
			Dog_Form::render_nonce_field('sc-options');
			Dog_Form::render_honeypot_field();
		?>
		<p class="submit">
			<?php
				Dog_Form::render_form_field(array(
					'field' => array(
						'tag' => 'button',
						'type' => 'submit',
						'name' => 'check',
						'value' => dog__txt('Verifică accesul fișierelor'),
						'class' => 'button button-primary',
					),
				));
			?>
		</p>
	</form>
</div>