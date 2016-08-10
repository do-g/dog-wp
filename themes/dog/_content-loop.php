<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		$include_template = $tpl_data['template'];
		unset($tpl_data['template']);
		dog__include_template($include_template, $tpl_data);
	}
}