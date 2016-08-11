<?php

function dog__get_option($name, $default = null) {
	return get_option(DOG__OPTION_PREFIX . $name, $default);
}

function dog__update_option($name, $value, $autoload = false) {
	return update_option(DOG__OPTION_PREFIX . $name, $value, $autoload);
}

function dog__delete_option($name) {
	return delete_option(DOG__OPTION_PREFIX . $name);
}

function dog__theme_list() {
	return array('dog', 'dogx');
}