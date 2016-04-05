<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
$post_tags = get_the_tags();
if ($post_tags) {
	$tags = array();
	foreach ($post_tags as $t) {
		array_push($tags, $t->term_id);
	}
	$query = new WP_Query(array(
		'post_type' => $related_post_type ? $related_post_type : array('page', 'post'),
		'post__not_in' => array(get_the_ID()),
		'nopaging' => true,
		'tag__in' => $tags,
		'orderby' => 'rand'
	));
	if ($query->have_posts()) { ?>
		<div class="list related">
		<?php while ($query->have_posts()) {
			$query->the_post();
			get_template_part('_content-article');
		} ?>
		</div>
	<?php }
	wp_reset_postdata();
}
?>