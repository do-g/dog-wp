<?php

function dog_local__add_custom_image_sizes() {
	add_image_size('small', 600, 9999);
}

function dog_local__register_sidebar() {
	register_sidebar(array(
		'name'          => 'Sidebar',
		'id'            => 'sidebar',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	));
}