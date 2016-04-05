<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
while (have_posts()) {
	the_post();
	get_template_part($included_template);
}