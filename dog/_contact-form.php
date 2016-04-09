<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<form action="<?= dog__contact_url() ?>" method="post" class="contact-form">
	<?php
		dog__show_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Numele dumneavoastră')
			),
			'errors' => array(),
			'field' => array(
				'tag' => 'input',
				'type' => 'text',
				'name' => 'nume',
				'value' => dog__get_post_value('nume'),
				'placeholder' => dog__txt('Nume'),
				'maxlength' => 30,
				'required' => 'required'
			)
		));
		dog__show_form_field(array(
			'wrapper' => array(),
			'errors' => array(),
			'field' => array(
				'tag' => 'input',
				'type' => 'email',
				'name' => 'email',
				'value' => dog__get_post_value('email'),
				'placeholder' => dog__txt('Adresa email'),
				'maxlength' => 50,
				'required' => 'required'
			),
			'hint' => array(
				'text' => dog__txt('Veți primi răspuns pe această adresă'),
			)
		));
		dog__show_form_field(array(
			'field' => array(
				'tag' => 'input',
				'type' => 'tel',
				'name' => 'telefon',
				'value' => dog__get_post_value('telefon'),
				'placeholder' => dog__txt('+40 722 312 789')
			)
		));
		dog__show_form_field(array(
			'field' => array(
				'tag' => 'input',
				'type' => 'number',
				'name' => 'varsta',
				'value' => dog__get_post_value('varsta')
			),
			'label' => array(
				'text' => dog__txt('Vârsta dumneavoastră')
			)
		));
		dog__show_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Mesajul dumneavoastră')
			),
			'field' => array(
				'tag' => 'textarea',
				'name' => 'mesaj',
				'value' => dog__get_post_value('mesaj'),
				'placeholder' => dog__txt('Mesaj'),
				'required' => 'required'
			),
			'errors' => array()
		));
		dog__show_form_field(array(
			'field' => array(
				'tag' => 'button',
				'type' => 'submit',
				'name' => 'contact_submit',
				'class' => 'button',
				'value' => dog__txt('Trimite')
			)
		));
		dog__nonce_field(dog__contact_url());
		dog__honeypot_field();
		dog__render_form_errors();
	?>
</form>