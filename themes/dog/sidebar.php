<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<div class="content-column side">
	<aside class="sidebar primary-sidebar widget-area" role="complementary">
	<?php
		dog__include_template('_sidebar');
		if (is_active_sidebar('sidebar')) {
			dynamic_sidebar('sidebar');
		}
	?>
	</aside>
</div>