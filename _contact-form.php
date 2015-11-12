<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<form action="<?= DOG__URI_PATH_CONTACT ?>" method="post" class="contact-form">
	<?php
		dog__show_form_field(array(
			'container' => array(),
			'label' => array(
				'text' => dog__txt('Numele dumneavoastră')
			),
			'field' => array(
				'tag' => 'input',
				'type' => 'text',
				'name' => 'nume',
				'value' => dog__get_post_value('nume'),
				'placeholder' => strtoupper(dog__txt('Nume')),
				'maxlength' => 30
			)
		));
		dog__show_form_field(array(
			'container' => array(),
			'label' => array(
				'text' => dog__txt('Adresa dumneavoastră email')
			),
			'field' => array(
				'tag' => 'input',
				'type' => 'email',
				'name' => 'email',
				'value' => dog__get_post_value('email'),
				'placeholder' => strtoupper(dog__txt('E-mail')),
				'maxlength' => 50
			)
		));
		dog__show_form_field(array(
			'container' => array(),
			'label' => array(
				'text' => dog__txt('Mesajul dumneavoastră')
			),
			'field' => array(
				'tag' => 'textarea',
				'name' => 'mesaj',
				'value' => dog__get_post_value('mesaj'),
				'placeholder' => strtoupper(dog__txt('Mesaj'))
			)
		));
		dog__show_form_field(array(
			'field' => array(
				'tag' => 'input',
				'type' => 'submit',
				'name' => 'contact_submit',
				'class' => 'button',
				'value' => dog__txt('Trimite')
			)
		));
		wp_nonce_field(DOG__NONCE_ACTION, DOG__NONCE_NAME);
	?>
	<?php
		$frm_errs = dog__get_form_errors();
		if (!$frm_errs) {
			$frm_errs = dog__get_flash_error('form');
			$frm_errs = $frm_errs ? array('generic' => $frm_errs) : null;
		}
	?>
	<?php if ($frm_errs) { ?>
		<?php foreach ($frm_errs as $type => $message) { ?>
			<p class="form-message form-error form-error-<?= esc_attr($type) ?>"><?= esc_html($message) ?></p>
		<?php } ?>
	<?php } else { ?>
		<p class="form-message form-success"><?= esc_html(dog__get_flash_success('form')) ?></p>
	<?php } ?>
</form>