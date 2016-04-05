<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');

define('DOG__ENV_DEVELOPMENT', 'development');
define('DOG__ENV_PRODUCTION', 'production');
define('DOG__ENV', DOG__ENV_DEVELOPMENT);
define('DOG__NONCE_ACTION_BASE', '6884c0ef5ece8c4765116c845babcea6');
define('DOG__NONCE_NAME', '6884c0ef5ece8c4765116c845babxg7y');
define('DOG__POST_THUMBNAIL_DEFAULT', 'default-post-thumb.jpg');
define('DOG__HONEYPOT_ENABLED', true);
/**********************************************/
define('DOG__HONEYPOT_TIMER_NAME', '6884c0ef5ece8c4765116c845babxgxx');
define('DOG__HONEYPOT_JAR_NAME', '6884c0ef5ece8c4765116c845babcea6');
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
define('DOG__PREFIX_X', 'dogx__');
define('DOG__PREFIX_AJAX', 'dog_ajax__');
define('DOG__TRANSIENT_OUTPUT_CACHE_PREFIX', DOG__PREFIX . 'outcache');
/**********************************************/
define('DOG__NONCE_VAR_PREFIX', 'nonce__');
define('DOG__WP_ACTION_AJAX_CALLBACK', 'dog__ajax');
define('DOG__AJAX_RESPONSE_STATUS_SUCCESS', wp_hash('ajaxresponseok'));
define('DOG__AJAX_RESPONSE_STATUS_ERROR', wp_hash('ajaxresponseerror'));
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

date_default_timezone_set(DOG__TIMEZONE);

if (DOG__ENV == DOG__ENV_DEVELOPMENT) {
	$error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT;
	if (defined('E_DEPRECATED')) {
		$error_reporting = $error_reporting & ~E_DEPRECATED;
	}
	error_reporting($error_reporting);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
} else {
	error_reporting(0);
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
}