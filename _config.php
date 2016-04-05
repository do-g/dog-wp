<?php

require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

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
define('DOG__ADMIN_DIR', 'admin');
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
define('DOG__PREFIX_LOCAL', 'dog_local__');
define('DOG__PREFIX_AJAX', 'dog_ajax__');
define('DOG__TRANSIENT_OUTPUT_CACHE_PREFIX', DOG__PREFIX . 'outcache');
/**********************************************/
define('DOG__NONCE_VAR_PREFIX', 'nonce__');
define('DOG__WP_ACTION_AJAX_CALLBACK', 'dog__ajax');
define('DOG__AJAX_RESPONSE_STATUS_SUCCESS', dog__hash('ajaxresponseok'));
define('DOG__AJAX_RESPONSE_STATUS_ERROR', dog__hash('ajaxresponseerror'));
define('DOG__AJAX_RESPONSE_CODE_INVALID_NONCE', 1001);
define('DOG__AJAX_RESPONSE_CODE_INVALID_METHOD', 1002);
define('DOG__AJAX_RESPONSE_CODE_INVALID_FORM', 1003);
define('DOG__AJAX_RESPONSE_CODE_INVALID_PARAM', 1004);
define('DOG__ALERT_KEY_RESPONSE_ERROR', 'response_error');
define('DOG__ALERT_KEY_SERVER_FAILURE', 'server_failure');
define('DOG__ALERT_KEY_CLIENT_FAILURE', 'client_failure');
define('DOG__ALERT_KEY_FORM_INVALID', 'form_invalid');
define('DOG__ALERT_KEY_EMPTY_SELECTION', 'empty_selection');
/**********************************************/
define('DOG_ADMIN__MENU_SLUG', 'dog-theme-options');
define('DOG_ADMIN__MENU_HOOK', 'toplevel_page_' . DOG_ADMIN__MENU_SLUG);
define('DOG_ADMIN__NAMESPACE_CACHE_SETTINGS', 'cache_settings');
define('DOG_ADMIN__SECTION_FILE_PREFIX', 'section-');
define('DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED', 'output_cache_enabled');
define('DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES', 'output_cache_expires');
define('DOG_ADMIN__CACHE_EXPIRATION_HOURS_DEFAULT', 24);
define('DOG_ADMIN__TRANSIENT_DB_PREFIX', '_transient_');
define('DOG_ADMIN__TRANSIENT_TIMEOUT_DB_PREFIX', DOG_ADMIN__TRANSIENT_DB_PREFIX . 'timeout_');
define('DOG_ADMIN__SECTION_GENERATE_LABELS', 'generate-labels');
define('DOG_ADMIN__SECTION_CACHE_SETTINGS', 'cache-settings');
define('DOG_ADMIN__SECTION_CACHE_OUTPUT', 'cache-output');
define('DOG_ADMIN__SECTION_EXPIRED_TRANSIENTS', 'expired-transients');
define('DOG_ADMIN__NONCE_CACHE_OUTPUT_DELETE', 'cache-output-delete');

$dog__schemaorg_page_types = dog__extend_with('schemaorg_page_types', array(
	'AboutPage' => array('despre', 'about'),
	'ContactPage' => array('contact', 'contact-us'),
	'CollectionPage' => array(),
	'ItemPage' => array(),
	'ProfilePage' => array(),
	'SearchResultsPage' => array()
));

$dog__contact_slugs = dog__extend_with('contact_slugs', array(
	dog__default_language() => 'contact',
	'en' => 'contact-us'
));

$dog__template_override = dog__extend_with('template_override', array(
	'page-contact' => array('contact-us')
));

$dog__output_cache_ignore_uri = dog__extend_with('output_cache_ignore_uri', array('/wp-cron.php'));

$dog__form_field_types = dog__extend_with('form_field_types', array(
	DOG__NAMESPACE_CONTACT => array(
		'nume' => DOG__POST_FIELD_TYPE_TEXT,
		'email' => DOG__POST_FIELD_TYPE_EMAIL,
		'mesaj' => DOG__POST_FIELD_TYPE_TEXTAREA
	),
	DOG_ADMIN__NAMESPACE_CACHE_SETTINGS => array(
		DOG_ADMIN__OPTION_OUTPUT_CACHE_ENABLED => DOG__POST_FIELD_TYPE_NATURAL,
		DOG_ADMIN__OPTION_OUTPUT_CACHE_EXPIRES => DOG__POST_FIELD_TYPE_NATURAL
	)
));

$dog_admin__sections = dog__extend_with('admin_sections', array(
	DOG_ADMIN__SECTION_GENERATE_LABELS,
	DOG_ADMIN__SECTION_CACHE_SETTINGS,
	DOG_ADMIN__SECTION_CACHE_OUTPUT,
	DOG_ADMIN__SECTION_EXPIRED_TRANSIENTS,
));

$dog_admin__custom_nonces = dog__extend_with('admin_custom_nonces', array(
	DOG_ADMIN__NONCE_CACHE_OUTPUT_DELETE
));

$dog__alert_messages = dog__extend_with('alert_messages', array(
	DOG__ALERT_KEY_RESPONSE_ERROR => __('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi procesat. Codul de eroare este ${code}'),
	DOG__ALERT_KEY_SERVER_FAILURE => __('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi procesat'),
	DOG__ALERT_KEY_CLIENT_FAILURE => __('Sistemul a întâmpinat o eroare. Cererea nu poate fi trimisă'),
	DOG__ALERT_KEY_FORM_INVALID => __('Formularul nu poate fi validat. Te rugăm să corectezi erorile'),
	DOG__ALERT_KEY_EMPTY_SELECTION => __('Acțiunea nu poate fi finalizată. Selectează cel puțin o înregistrare'),
	'labels_generated' => __('Etichetele au fost generate') . '. <a href="/wp-admin/options-general.php?page=mlang&tab=strings">' . __('Click aici pentru a modifica') . '</a>',
));