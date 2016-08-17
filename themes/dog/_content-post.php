<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<div class="post" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
	<article itemscope itemtype="http://schema.org/Article" class="wp-item post-item">
		<?php dog__include_template('_content-item') ?>
		<a href="<?php the_permalink(); ?>" class="item-link" itemprop="url"><?= dog__txt('Vezi tot articolul') ?></a>
	</article>
</div>