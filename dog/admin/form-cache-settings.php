<?php require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php') ?>
<form method="post" class="dog-admin--ajaxform" id="dog-form-<?= DOG_ADMIN__SECTION_CACHE_SETTINGS ?>">
<?php
	dog__show_form_field(array(
		'wrapper' => array(
			'class' => 'wrapper-label-radio-group'
		),
		'label' => array(
			'text' => dog__txt('Activează memoria cache')
		)
	));
	$field_name = DOG__OPTION_OUTPUT_CACHE_ENABLED;
	$value = dog__get_post_value_or_default($field_name, dog__get_option($field_name, 0));
	dog__show_form_field(array(
		'field' => array(
			'tag' => 'input',
			'type' => 'radio',
			'name' => $field_name,
			'id' => $field_name . '_yes',
			'value' => 1,
			'required' => 'required',
			'checked' => dog__get_checked_attr_value($value == 1)
		),
		'label' => array(
			'text' => __('Da')
		)
	));
	dog__show_form_field(array(
		'field' => array(
			'tag' => 'input',
			'type' => 'radio',
			'name' => $field_name,
			'id' => $field_name . '_no',
			'value' => 0,
			'required' => 'required',
			'checked' => dog__get_checked_attr_value($value == 0)
		),
		'label' => array(
			'text' => __('Nu')
		),
		'errors' => array()
	));
	$field_name = DOG__OPTION_OUTPUT_CACHE_EXPIRES;
	dog__show_form_field(array(
		'wrapper' => array(),
		'label' => array(
			'text' => dog__txt('Memoria expiră în')
		),
		'field' => array(
			'tag' => 'input',
			'type' => 'number',
			'name' => $field_name,
			'value' => dog__get_post_value_or_default($field_name, dog__get_option($field_name, DOG_ADMIN__CACHE_EXPIRATION_HOURS_DEFAULT)),
			'required' => 'required'
		),
		'hint' => array(
			'text' => dog__txt('ore')
		),
		'errors' => array()
	));
	dog__nonce_field(DOG_ADMIN__SECTION_CACHE_SETTINGS);
	dog__honeypot_field();
	dog__render_form_errors();
?>
</form>