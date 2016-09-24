<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$frm_errs = Dog_Form::get_form_errors();
if (!$frm_errs) {
	$frm_errs = dog__get_flash_error('form');
	$frm_errs = $frm_errs ? array('generic' => $frm_errs) : null;
}
if ($frm_errs) {
	foreach ($frm_errs as $type => $message) { ?>
		<p class="form-message form-error form-error-<?= esc_attr($type) ?>"><?= esc_html($message) ?></p>
	<?php }
} else {
	$success_message = dog__get_flash_success('form');
	if ($success_message) { ?>
		<p class="form-message form-success"><?= esc_html($success_message) ?></p>
	<?php }
}