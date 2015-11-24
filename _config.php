<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

$dog__schemaorg_page_types = array(
	'AboutPage' => array('despre', 'about'),
	'ContactPage' => array('contact', 'contact-us'),
	'CollectionPage' => array(),
	'ItemPage' => array(),
	'ProfilePage' => array(),
	'SearchResultsPage' => array()
);

$dog__contact_slugs = array(
	dog__default_language() => 'contact',
	'en' => 'contact-us'
);

$dog__template_override = array(
	'page-contact' => array('contact-us')
);

$dog__output_cache_ignore_uri = array('/wp-cron.php');

define('DOG__ENV', 'development');
define('DOG__NONCE_ACTION_BASE', dog__hash('0#lugTOv[qAkI;%;8<c}Wwi-+*qzhy]cXHKVZ#}-[9+H6!-|UzE]IBYc@[ -]aDT'));
define('DOG__NONCE_NAME', dog__hash('&Uh`{r3g1DbW2md5UMz!~#G-_g6cU|4h%[;7yrb?vi$dS4mlI7q+~Gn>7M%M<dS4'));
define('DOG__POST_THUMBNAIL_DEFAULT', 'default-post-thumb.jpg');
define('DOG__HONEYPOT_ENABLED', true);
/**********************************************/
define('DOG__HONEYPOT_TIMER_NAME', dog__hash(DOG__NONCE_ACTION_BASE));
define('DOG__HONEYPOT_JAR_NAME', dog__hash(DOG__NONCE_NAME));
define('DOG__URI_TEMPLATE_PAGE', '/${slug}/');
define('DOG__URI_TEMPLATE_CONTACT', DOG__URI_TEMPLATE_PAGE);
define('DOG__NAMESPACE_CONTACT', 'contact');
define('DOG__TIMEZONE', 'Europe/Bucharest');
/**********************************************/
define('DOG__FIELD_ERROR_REQUIRED', 'required');
define('DOG__FIELD_ERROR_REGEX', 'regex');
define('DOG__FIELD_NAME_FORM', 'form');
define('DOG__POST_FIELD_TYPE_TEXT', 'text');
define('DOG__POST_FIELD_TYPE_EMAIL', 'email');
define('DOG__POST_FIELD_TYPE_TEXTAREA', 'textarea');
define('DOG__POST_FIELD_TYPE_NATURAL', 'natural');
define('DOG__POST_FIELD_TYPE_ARRAY_TEXT', 'array_text');
define('DOG__SESSION_KEY_FLASH', 'flash');
define('DOG__SESSION_KEY_SUCCESS', 'success');
define('DOG__SESSION_KEY_ERROR', 'error');
define('DOG__REGEX_KEY_EMAIL', 'email');
define('DOG__PREFIX', 'dog__');
define('DOG__PREFIX_ADMIN', 'dog_admin__');
define('DOG__TRANSIENT_OUTPUT_CACHE_PREFIX', DOG__PREFIX . 'outcache');
/**********************************************/
define('DOG_ADMIN__DIR', 'admin');
define('DOG_ADMIN__MENU_SLUG', 'dog-theme-options');
define('DOG_ADMIN__NAMESPACE_CACHE_SETTINGS', 'cache_settings');
define('DOG_ADMIN__WP_ACTION_AJAX_CALLBACK', 'dog_admin__ajax');
define('DOG_ADMIN__AJAX_NONCE_FIELD', '_ajax_nonce');
define('DOG_ADMIN__AJAX_NONCE_VAR_PREFIX', 'nonce__');
define('DOG_ADMIN__SECTION_FILE_PREFIX', 'section-');
define('DOG_ADMIN__MESSAGE_CODE_PLACEHOLDER', '{$code}');
define('DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH', 'nonce_mismatch');
define('DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED', 'output_cache_enabled');
define('DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES', 'output_cache_expires');
define('DOG_ADMIN__CACHE_EXPIRATION_HOURS_DEFAULT', 24);
define('DOG_ADMIN__TRANSIENT_DB_PREFIX', '_transient_');
define('DOG_ADMIN__TRANSIENT_TIMEOUT_DB_PREFIX', DOG_ADMIN__TRANSIENT_DB_PREFIX . 'timeout_');
define('DOG_ADMIN__SECTION_ACTION_GENERATE_LABELS', 'dog_admin__generate_labels');
define('DOG_ADMIN__SECTION_ACTION_CACHE_SETTINGS', 'dog_admin__cache_settings');
define('DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT', 'dog_admin__cache_output');
define('DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT_DELETE', 'dog_admin__cache_output_delete');
define('DOG_ADMIN__SECTION_ACTION_EXPIRED_TRANSIENTS', 'dog_admin__expired_transients');
define('DOG_ADMIN__AJAX_RESPONSE_STATUS_SUCCESS', dog__hash('ajaxresponseok'));
define('DOG_ADMIN__AJAX_RESPONSE_STATUS_ERROR', dog__hash('ajaxresponseerror'));
define('DOG_ADMIN__AJAX_RESPONSE_KEY_SUCCESS', 'success');
define('DOG_ADMIN__AJAX_RESPONSE_KEY_SUCCESS2', 'success2');
define('DOG_ADMIN__AJAX_RESPONSE_KEY_AJAX', 'ajax');
define('DOG_ADMIN__AJAX_RESPONSE_KEY_FAILURE', 'failure');
define('DOG_ADMIN__AJAX_RESPONSE_KEY_FORM', 'form');
define('DOG_ADMIN__AJAX_RESPONSE_CODE_SUCCESS', 1);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_AJAX', 999);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_FAILURE', 1000);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_NONCE', 1001);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_METHOD', 1002);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_INVALID_FORM', 1003);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_MISMATCH_NONCE', 1004);
define('DOG_ADMIN__AJAX_RESPONSE_CODE_MISSING_PARAM', 1005);

$dog__form_field_types = array(
	DOG__NAMESPACE_CONTACT => array(
		'nume' => DOG__POST_FIELD_TYPE_TEXT,
		'email' => DOG__POST_FIELD_TYPE_EMAIL,
		'mesaj' => DOG__POST_FIELD_TYPE_TEXTAREA
	),
	DOG_ADMIN__NAMESPACE_CACHE_SETTINGS => array(
		DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED => DOG__POST_FIELD_TYPE_NATURAL,
		DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES => DOG__POST_FIELD_TYPE_NATURAL
	)
);

$dog_admin__sections = array(
	DOG_ADMIN__SECTION_ACTION_GENERATE_LABELS => null,
	DOG_ADMIN__SECTION_ACTION_CACHE_SETTINGS => null,
	DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT => array(
		DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT => null,
		DOG_ADMIN__SECTION_ACTION_CACHE_OUTPUT_DELETE => null
	),
	DOG_ADMIN__SECTION_ACTION_EXPIRED_TRANSIENTS => null
);