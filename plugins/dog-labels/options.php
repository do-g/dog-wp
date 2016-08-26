<?php
	require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
	$labels = get_transient(DOG_ADMIN__TRANSIENT_FORM_RESPONSE);
	delete_transient(DOG_ADMIN__TRANSIENT_FORM_RESPONSE);
	$messages = dog__get_admin_form_messages();
	$errors = dog__get_admin_form_errors();
	$translate_section_name = dog__txt('Traduceri');
	$translate_section_link = '<a href="/wp-admin/options-general.php?page=mlang&tab=strings&group=theme">' . $translate_section_name . '</a>';
?>
<div class="wrap dog-admin--page">
	<h1>Opțiuni etichete</h1>
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
	 	<p class="page-description"><?= dog__txt('Etichetele sunt acele fragmente text care apar pe sit dar nu fac parte din conținut.
			Dacă este cazul acestea trebuie traduse în toate limbile sitului.
			Folosește această secțiune pentru a genera automat lista de etichete în vederea traducerii.
			Sistemul va citi fișierele temei și va extrage etichetele pentru a putea fi modificate.
			Apasă butonul de mai jos pentru a începe căutarea și procesarea etichetelor.') ?></p>
		<?php
			if ($labels) {
				echo dog__string_to_html_tag(implode('', $labels), 'pre');
			}
 			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'input',
					'type' => 'hidden',
					'name' => 'action',
					'value' => 'dog_save_lb_options',
				),
			));
			Dog_Form::render_nonce_field('lb-options');
			Dog_Form::render_honeypot_field();
		?>
		<p class="submit">
			<?php
				Dog_Form::render_form_field(array(
					'field' => array(
						'tag' => 'button',
						'type' => 'submit',
						'name' => 'generate',
						'value' => dog__txt('Detectează etichetele'),
						'class' => 'button button-primary',
					),
				));
			?>
		</p>
	</form>
</div>