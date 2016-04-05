<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (dog__is_post('contact_submit')) {
	dog__whitelist_fields(array('nume', 'email', 'mesaj'));
	dog__get_post_data(DOG__NAMESPACE_CONTACT);
	dog__validate_nonce(dog__contact_uri(), dog__contact_uri());
	dog__validate_honeypot();
	dog__validate_required_fields(array('nume', 'email', 'mesaj'));
	dog__validate_regex_field('nume', '/^[\\s\\p{L}-]+$/iu', dog__txt('Numele introdus este invalid'));
	dog__validate_regex_field('email', DOG__REGEX_KEY_EMAIL, dog__txt('Adresa email invalida'));
	if (dog__form_is_valid()) {
		if (dog__send_form_email(DOG__NAMESPACE_CONTACT, $errors)) {
			dog__set_flash_success('form', dog__txt('Mesajul a fost trimis'));
			dog__safe_redirect(dog__current_uri());
		} else {
			dog__set_form_error(dog__txt('Formularul nu poate fi trimis'));
		}
	} else if (!dog__get_form_errors()) {
		dog__set_form_error(dog__txt('Corectati erorile'));
	}
}
get_header();
dog__showContent('_content-contact');
get_footer();