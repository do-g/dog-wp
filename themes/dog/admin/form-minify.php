<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<form method="post" class="dog-admin--ajaxform" id="dog-form-<?= DOG_ADMIN__SECTION_MINIFY ?>">
<?php
	$field_name = DOG__OPTION_MINIFY_STYLES;
	dog__show_form_field(array(
		'wrapper' => array(),
		'label' => array(
			'text' => dog__txt('Include următoarele fișiere CSS:')
		),
		'field' => array(
			'tag' => 'textarea',
			'name' => $field_name,
			'value' => dog_admin__minify_styles_value($field_name)
		),
		'errors' => array()
	));
	$field_name = DOG__OPTION_MINIFY_SCRIPTS;
	dog__show_form_field(array(
		'wrapper' => array(),
		'label' => array(
			'text' => dog__txt('Include următoarele fișiere JS:')
		),
		'field' => array(
			'tag' => 'textarea',
			'name' => $field_name,
			'value' => dog_admin__minify_scripts_value($field_name)
		),
		'errors' => array()
	));
	dog__nonce_field(DOG_ADMIN__SECTION_MINIFY);
	dog__honeypot_field();
	dog__render_form_errors();
?>
</form>