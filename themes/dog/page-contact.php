<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (dog__is_post('contact_submit')) {
	Dog_Form::whitelist_keys(array('contact_name', 'contact_email', 'contact_phone', 'contact_message'));
	Dog_Form::sanitize_post_data(array(
		'contact_name'    => DOG__POST_FIELD_TYPE_TEXT,
		'contact_email'   => DOG__POST_FIELD_TYPE_EMAIL,
		'contact_phone'   => DOG__POST_FIELD_TYPE_TEXT,
		'contact_message' => DOG__POST_FIELD_TYPE_TEXTAREA,
	));
	Dog_Form::validate_nonce(dog__contact_url(), dog__contact_url());
	Dog_Form::validate_honeypot();
	Dog_Form::validate_required_fields(array('contact_name', 'contact_email', 'contact_message'));
	Dog_Form::validate_email_fields('contact_email');
	Dog_Form::validate_regex_fields(array(
		'contact_name',
		'contact_phone',
	), array(
		'/^[\\s\\p{L}-\']+$/iu',
		'/^[0-9-\+\.\(\)\\s]+$/',
	), array(
		dog__txt('Numele introdus este invalid'),
		dog__txt('Numărul de telefon este invalid'),
	));
	if (Dog_Form::form_is_valid()) {
		$result = dog__send_form_mail_standard();
		if ($result === true) {
			dog__set_flash_success('form', dog__txt('Mesajul a fost trimis'));
			dog__safe_redirect(dog__contact_success_url());
		} else {
			Dog_Form::set_form_error(dog__txt('Formularul nu poate fi trimis sau a fost trimis incomplet'));
			foreach ($result as $n => $e) {
				Dog_Form::set_form_error($e, 'mail_' . $n);
			}
		}
	} else if (!Dog_Form::get_form_errors()) {
		Dog_Form::set_form_error(dog__txt('Corectati erorile'));
	}
}
dog__include_template('_content-columns', array('template' => '_content-contact'));