<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<form method="post" class="page-form contact-form" id="contact-form">
	<?php
		Dog_Form::render_form_errors();
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Numele dumneavoastră'),
			),
			'field' => array(
				'tag' => 'input',
				'type' => 'text',
				'name' => 'contact_name',
				'value' => Dog_Form::get_post_value('contact_name'),
				'placeholder' => dog__txt('Sugestie pentru câmpul nume'),
				'maxlength' => Dog_Form::FIELD_MAXLENGTH_FULL_NAME,
				'required' => 'required',
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul nume'),
			),
			'errors' => array(),
		));
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Adresa email'),
			),
			'field' => array(
				'tag' => 'input',
				'type' => 'email',
				'name' => 'contact_email',
				'value' => Dog_Form::get_post_value('contact_email'),
				'placeholder' => dog__txt('Sugestie pentru câmpul email'),
				'maxlength' => Dog_Form::FIELD_MAXLENGTH_ADDRESS,
				'required' => 'required',
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul email'),
			),
			'errors' => array(),
		));
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Numărul de telefon'),
			),
			'field' => array(
				'tag' => 'input',
				'type' => 'tel',
				'name' => 'contact_phone',
				'value' => Dog_Form::get_post_value('contact_phone'),
				'placeholder' => dog__txt('Sugestie pentru câmpul telefon'),
				'maxlength' => Dog_Form::FIELD_MAXLENGTH_PHONE,
			),
			'hint' => array(
				'text' => dog__txt('Informații suplimentare pentru câmpul telefon'),
			),
			'errors' => array(),
		));
		Dog_Form::render_form_field(array(
			'wrapper' => array(),
			'label' => array(
				'text' => dog__txt('Mesajul dumneavoastră'),
			),
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
			'errors' => array(),
		));
	?>
	<div class="form-controls">
	<?php
		Dog_Form::render_form_field(array(
			'field' => array(
				'tag' => 'button',
				'type' => 'submit',
				'name' => 'contact_submit',
				'class' => 'button',
				'value' => dog__txt('Trimite formularul')
			)
		));
		$form_action_hook = 'contact';
		Dog_Form::render_action_field($form_action_hook);
		Dog_Form::render_nonce_field($form_action_hook);
		Dog_Form::render_honeypot_field();
	?>
	</div>
</form>