<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$dog__form_uses_honeypot = true;
dog__show_form_field(array(
	'field' => array(
		'tag' => 'input',
		'type' => 'hidden',
		'name' => DOG__HP_TIMER_NAME,
		'value' => microtime(true),
		'maxlength' => 50
	)
));
dog__show_form_field(array(
	'wrapper' => array(
		'style' => 'display: none'
	),
	'label' => array(
		'text' => dog__txt('Nu completați')
	),
	'field' => array(
		'tag' => 'input',
		'type' => 'date',
		'name' => DOG__HP_JAR_NAME,
		'maxlength' => 10,
		'min' => '1997-02-29',
		'max' => '1997-02-29'
	)
));