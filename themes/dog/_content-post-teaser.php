<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<div class="entry-list-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
	<article itemscope itemtype="http://schema.org/Article" class="entry teaser <?= get_post_type() ?> clearfix">
		<h2 class="entry-title" itemprop="headline"><?php the_title(sprintf('<a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a>') ?></h2>
		<?php if ($featured_image = dog__get_featured_image_url('small')) { ?><img src="<?= $featured_image ?>" class="entry-featured-image" /><?php } ?>
		<div class="entry-body entry-excerpt" itemprop="articleBody"><?php the_excerpt() ?></div>
		<a href="<?php the_permalink() ?>" class="entry-url" itemprop="url"><?= dog__txt('Vezi mai mult') ?></a>
	</article>
</div>