<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body class="<?= dog__body_class() ?>">
	<header>
		<a href="<?= esc_url(home_url('/')) ?>" id="logo">
			<img src="<?= dog__img_uri('logo.png') ?>" />
			<span><?= dog__txt('Bine ați venit') ?></span>
		</a>
		<?php wp_nav_menu(array('theme_location' => 'main-menu')) ?>
	</header>
	<main>