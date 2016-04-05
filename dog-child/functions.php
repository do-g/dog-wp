<?php
require_once(get_template_directory() . '/_block-direct-access.php');

function dogx__add_custom_image_sizes() {
	add_image_size('small', 600, 9999);
}