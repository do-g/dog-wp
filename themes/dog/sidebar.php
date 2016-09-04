<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
if (is_active_sidebar('sidebar')) { ?>
	<div class="content-column side">
		<div class="sidebar primary-sidebar widget-area" role="complementary">
			<?php dynamic_sidebar('sidebar') ?>
		</div>
	</div>
<?php }