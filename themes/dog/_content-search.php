<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<div class="search-results-list">
	<h1 class="entry-title"><?= dog__txt('Rezultatele căutării după "${s}"', array('s' => get_search_query())) ?></h1>
	<?php dog__loop_content('_content-post-list') ?>
</div>