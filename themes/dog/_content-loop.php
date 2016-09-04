<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$include_template = $tpl_data['template'];
unset($tpl_data['template']);
if (have_posts()) {
	while (have_posts()) {
		the_post();
		dog__include_template($include_template, $tpl_data);
	}
	the_posts_pagination(array(
		'prev_text'          => dog__txt('Pagina anterioară'),
		'next_text'          => dog__txt('Pagina următoare'),
		'before_page_number' => '<span class="before-page-number">' . dog__txt('Pagina') . '</span>',
	));
} else {
	dog__include_template('_empty-loop', $tpl_data);
}