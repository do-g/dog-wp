<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta content='IE=edge,chrome=1' http-equiv='X-UA-Compatible'>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="image" content="page-image.jpg" />
	<?php wp_head(); ?>
</head>
<body class="<?= dog__body_class(array('preloading')) ?>" itemscope itemtype="http://schema.org/<?= dog__schema_page_type() ?>">
	<meta itemprop="inLanguage" content="<?= dog__active_language() ?>">
	<?php dog__include_template('_preloader') ?>
	<section class="page-wrapper">
		<header class="page-header">
			<a href="<?= esc_url(home_url('/')) ?>" id="logo">
				<img src="<?= dog__img_url('logo.png') ?>" />
				<span><?= dog__txt('Bine ați venit') ?></span>
			</a>
			<?php wp_nav_menu(array('theme_location' => 'location-main-menu', 'menu_class' => 'main-menu', 'container' => false)) ?>
		</header>
		<main itemprop="mainContentOfPage" class="page-main">