<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php'); ?>
<article itemscope itemtype="http://schema.org/Article" class="entry single post">
	<h1 class="entry-title" itemprop="headline"><?php the_title() ?></h1>
	<div class="entry-body" itemprop="articleBody"><?php the_content() ?></div>
</article>