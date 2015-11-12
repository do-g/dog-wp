<?php
require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
get_template_part('_content-page');
?>
<p><a href="<?php the_permalink(); ?>"><?= dog__txt('Vezi tot articolul') ?></a></p>