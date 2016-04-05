<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
get_header();
?>
<section itemscope itemtype="http://schema.org/ItemList">
	<?php dog__showContent('_content-article') ?>
</section>
<?php get_footer() ?>