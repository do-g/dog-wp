<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<form action="<?= dog__contact_url() ?>" method="post" class="contact-form" id="contact-form">
	<?php
		Dog_Form::render_form_errors();
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Numele dumneavoastră'),
			),
			'errors' => array(),
			'field' => array(
				'tag' => 'input',
				'type' => 'text',
				'name' => 'contact_name',
				'value' => Dog_Form::get_post_value('contact_name'),
				'placeholder' => dog__txt('Sugestie pentru câmpul nume'),
				'maxlength' => 30,
				'required' => 'required',
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul nume'),
			),
		));
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Adresa email'),
			),
			'errors' => array(),
			'field' => array(
				'tag' => 'input',
				'type' => 'email',
				'name' => 'contact_email',
				'value' => Dog_Form::get_post_value('contact_email'),
				'placeholder' => dog__txt('Sugestie pentru câmpul email'),
				'maxlength' => 50,
				'required' => 'required',
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul email'),
			),
		));
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Numărul de telefon'),
			),
			'errors' => array(),
			'field' => array(
				'tag' => 'input',
				'type' => 'tel',
				'name' => 'contact_phone',
				'value' => Dog_Form::get_post_value('contact_phone'),
				'placeholder' => dog__txt('Sugestie pentru câmpul telefon'),
				'maxlength' => 20,
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul telefon'),
			),
		));
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Mesajul dumneavoastră'),
			),
			'errors' => array(),
			'field' => array(
				'tag' => 'textarea',
				'name' => 'contact_message',
				'value' => Dog_Form::get_post_value('contact_message'),
				'placeholder' => dog__txt('Sugestie pentru câmpul mesaj'),
				'required' => 'required',
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul mesaj'),
			),
		));
		Dog_Form::render_form_field(array(
			'field' => array(
				'tag' => 'button',
				'type' => 'submit',
				'name' => 'contact_submit',
				'class' => 'button',
				'value' => dog__txt('Trimite formularul')
			)
		));
		Dog_Form::render_nonce_field(dog__contact_url());
		Dog_Form::render_honeypot_field();
	?>
</form>