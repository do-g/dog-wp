<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
?><article class="entry detail archive category main">
	<h1 class="entry-title page-title"><?php single_cat_title() ?></h1>
	<div class="entry-body"><?php
		dog__loop_content('_content-post-teaser');
	?></div>
</article>