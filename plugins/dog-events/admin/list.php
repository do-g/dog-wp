<?php
	require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
	$status = $_GET['status'];
	$deleted = $_GET['deleted'];
	$table = Dog_Events::get_list_table();
  	$table->prepare_items();
?>
<div class="wrap dog-admin--page">
	<h1 class="wp-heading-inline"><?= dog__txt('Evenimente') ?></h1>
	<a href="" class="page-title-action"><?= dog__txt('Adaugă eveniment') ?></a>
	<hr class="wp-header-end spacer">
	<?= dog__prepare_transient_flash_messages() ?>
	<?php $table->views() ?>
	<form method="get">
		<input type="hidden" name="page" value="<?= Dog_Events::PLUGIN_SLUG ?>" />
		<?php $table->display() ?>
	</form>
	<!--<ul class="subsubsub">
		<li class="all">
			<a href="<?= self::admin_url('list') ?>" class="<?= $status === null && !$deleted ? 'current' : '' ?>"><?= dog__txt('În total') ?> <span class="count">(1)</span></a> |
		</li>
		<li>
			<a href="<?= self::admin_url('list', 'status=1') ?>" class="<?= $status === '1' ? 'current' : '' ?>"><?= dog__txt('Publicate') ?> <span class="count">(1)</span></a> |
		</li>
		<li>
			<a href="<?= self::admin_url('list', 'status=0') ?>" class="<?= $status === '0' ? 'current' : '' ?>"><?= dog__txt('Inactive') ?> <span class="count">(1)</span></a>
		</li>
		<li>
			<a href="<?= self::admin_url('list', 'deleted=1') ?>" class="<?= $deleted === '1' ? 'current' : '' ?>"><?= dog__txt('Gunoi') ?> <span class="count">(1)</span></a>
		</li>
	</ul>
	<form id="posts-filter" method="get">
		<p class="search-box">
			<input type="search" id="post-search-input" name="s" value="">
			<input type="submit" id="search-submit" class="button" value="<?= dog__txt('Caută evenimente') ?>">
		</p>
		<input type="hidden" name="status" class="post_status_page" value="all">
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="1fedf1eb34">
		<input type="hidden" name="_wp_http_referer" value="/wp-admin/edit.php">
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Selectează acțiunea în masă</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1">Acțiuni în masă</option>
					<option value="edit" class="hide-if-no-js">Editează</option>
					<option value="trash">Mută la gunoi</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Aplică">
			</div>
			<div class="alignleft actions">
				<label for="filter-by-date" class="screen-reader-text">Filtrează după dată</label>
				<select name="m" id="filter-by-date">
					<option selected="selected" value="0">Toate datele</option>
					<option value="201604">aprilie 2016</option>
				</select>
				<label class="screen-reader-text" for="cat">Filtrare după categorie</label>
				<select name="cat" id="cat" class="postform">
					<option value="0">Toate categoriile</option>
					<option class="level-0" value="2">Testcat</option>
					<option class="level-0" value="1">Uncategorized</option>
				</select>
				<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filtrează">
			</div>
			<div class="tablenav-pages one-page">
				<span class="displaying-num">1 element</span>
				<span class="pagination-links">
					<span class="tablenav-pages-navspan" aria-hidden="true">«</span>
					<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
					<span class="paging-input">
						<label for="current-page-selector" class="screen-reader-text">Pagina curentă</label>
						<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
						<span class="tablenav-paging-text">
							 din <span class="total-pages">1</span>
						</span>
					</span>
					<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
					<span class="tablenav-pages-navspan" aria-hidden="true">»</span>
				</span>
			</div>
			<br class="clear">
		</div>
		<h2 class="screen-reader-text">Listă articole</h2>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1">Selectează tot</label>
						<input id="cb-select-all-1" type="checkbox">
					</td>
					<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
						<a href="http://www.wordpress.loc/wp-admin/edit.php?orderby=title&amp;order=asc">
							<span>Titlu</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" id="author" class="manage-column column-author">Autor</th>
					<th scope="col" id="categories" class="manage-column column-categories">Categorii</th>
					<th scope="col" id="tags" class="manage-column column-tags">Etichete</th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc">
						<a href="http://www.wordpress.loc/wp-admin/edit.php?orderby=comment_count&amp;order=asc">
							<span>
								<span class="vers comment-grey-bubble" title="Comentarii">
									<span class="screen-reader-text">Comentarii</span>
								</span>
							</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" id="date" class="manage-column column-date sortable asc">
						<a href="http://www.wordpress.loc/wp-admin/edit.php?orderby=date&amp;order=desc">
							<span>Dată</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
				</tr>
			</thead>
			<tbody id="the-list">
				<tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-testcat">
					<th scope="row" class="check-column">
						<label class="screen-reader-text" for="cb-select-1">Selectează Hello world!</label>
						<input id="cb-select-1" type="checkbox" name="post[]" value="1">
						<div class="locked-indicator">
							<span class="locked-indicator-icon" aria-hidden="true"></span>
							<span class="screen-reader-text">„Hello world!” este blocat</span>
						</div>
					</th>
					<td class="title column-title has-row-actions column-primary page-title" data-colname="Titlu">
						<div class="locked-info">
							<span class="locked-avatar"></span>
							<span class="locked-text"></span>
						</div>
						<strong>
							<a class="row-title" href="http://www.wordpress.loc/wp-admin/post.php?post=1&amp;action=edit" aria-label="„Hello world!” (Editare)">Hello world!</a>
						</strong>
						<div class="hidden" id="inline_1">
							<div class="post_title">Hello world!</div><div class="post_name">hello-world</div>
							<div class="post_author">1</div>
							<div class="comment_status">open</div>
							<div class="ping_status">open</div>
							<div class="_status">publish</div>
							<div class="jj">04</div>
							<div class="mm">04</div>
							<div class="aa">2016</div>
							<div class="hh">19</div>
							<div class="mn">38</div>
							<div class="ss">36</div>
							<div class="post_password"></div>
							<div class="page_template">default</div>
							<div class="post_category" id="category_1">2</div>
							<div class="tags_input" id="post_tag_1"></div>
							<div class="sticky"></div>
							<div class="post_format"></div>
						</div>
						<div class="row-actions">
							<span class="edit">
								<a href="http://www.wordpress.loc/wp-admin/post.php?post=1&amp;action=edit" aria-label="Editează „Hello world!”">Editează</a> |
							</span>
							<span class="inline hide-if-no-js">
								<a href="#" class="editinline" aria-label="Editează rapid „Hello world!”, pe loc">Editează rapid</a> |
							</span>
							<span class="trash">
								<a href="http://www.wordpress.loc/wp-admin/post.php?post=1&amp;action=trash&amp;_wpnonce=05b516db5e" class="submitdelete" aria-label="Mută „Hello world!” la gunoi">Aruncă la gunoi</a> |
							</span>
							<span class="view">
								<a href="http://www.wordpress.loc/2016/04/04/hello-world/" rel="permalink" aria-label="Vezi „Hello world!”">Vizualizează</a>
							</span>
						</div>
						<button type="button" class="toggle-row">
							<span class="screen-reader-text">Arată mai multe detalii</span>
						</button>
					</td>
					<td class="author column-author" data-colname="Autor">
						<a href="edit.php?post_type=post&amp;author=1">admin</a>
					</td>
					<td class="categories column-categories" data-colname="Categorii">
						<a href="edit.php?category_name=testcat">Testcat</a>
					</td>
					<td class="tags column-tags" data-colname="Etichete">
						<span aria-hidden="true">—</span>
						<span class="screen-reader-text">Fără etichete</span>
					</td>
					<td class="comments column-comments" data-colname="Comentarii">
						<div class="post-com-count-wrapper">
							<a href="http://www.wordpress.loc/wp-admin/edit-comments.php?p=1&amp;comment_status=approved" class="post-com-count post-com-count-approved">
								<span class="comment-count-approved" aria-hidden="true">1</span>
								<span class="screen-reader-text">1 comentariu</span>
							</a>
							<span class="post-com-count post-com-count-pending post-com-count-no-pending">
								<span class="comment-count comment-count-no-pending" aria-hidden="true">0</span>
								<span class="screen-reader-text">Niciun comentariu în așteptare</span>
							</span>
						</div>
					</td>
					<td class="date column-date" data-colname="Dată">
						Publicat<br><abbr title="2016/04/04 7:38:36 PM">04.04.2016</abbr>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-2">Selectează tot</label>
						<input id="cb-select-all-2" type="checkbox">
					</td>
					<th scope="col" class="manage-column column-title column-primary sortable desc">
						<a href="http://www.wordpress.loc/wp-admin/edit.php?orderby=title&amp;order=asc">
							<span>Titlu</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column column-author">Autor</th>
					<th scope="col" class="manage-column column-categories">Categorii</th>
					<th scope="col" class="manage-column column-tags">Etichete</th>
					<th scope="col" class="manage-column column-comments num sortable desc">
						<a href="http://www.wordpress.loc/wp-admin/edit.php?orderby=comment_count&amp;order=asc">
							<span>
								<span class="vers comment-grey-bubble" title="Comentarii">
									<span class="screen-reader-text">Comentarii</span>
								</span>
							</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column column-date sortable asc">
						<a href="http://www.wordpress.loc/wp-admin/edit.php?orderby=date&amp;order=desc">
							<span>Dată</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
				</tr>
			</tfoot>
		</table>
		<div class="tablenav bottom">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-bottom" class="screen-reader-text">Selectează acțiunea în masă</label>
				<select name="action2" id="bulk-action-selector-bottom">
					<option value="-1">Acțiuni în masă</option>
					<option value="edit" class="hide-if-no-js">Editează</option>
					<option value="trash">Mută la gunoi</option>
				</select>
				<input type="submit" id="doaction2" class="button action" value="Aplică">
			</div>
			<div class="alignleft actions"></div>
			<div class="tablenav-pages one-page">
				<span class="displaying-num">1 element</span>
				<span class="pagination-links">
					<span class="tablenav-pages-navspan" aria-hidden="true">«</span>
					<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
					<span class="screen-reader-text">Pagina curentă</span>
					<span id="table-paging" class="paging-input">
						<span class="tablenav-paging-text">
							1 din <span class="total-pages">1</span>
						</span>
					</span>
					<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
					<span class="tablenav-pages-navspan" aria-hidden="true">»</span>
				</span>
			</div>
			<br class="clear">
		</div>
	</form>-->
</div>