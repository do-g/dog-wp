<?php require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php') ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body class="<?= dog__body_class(array('preloading')) ?>" itemscope itemtype="http://schema.org/<?= dog__schema_page_type() ?>">
	<meta itemprop="inLanguage" content="<?= dog__active_language() ?>">
	<?php dog__include_template('_preloader') ?>
	<section class="page-wrapper">
		<header class="page-header">
			<div class="w-container">
				<a href="<?= esc_url(home_url('/')) ?>" class="logo" title="<?= dog__txt('Acasă') ?>">
					<img src="<?= dog__img_url('logo.png') ?>" />
					<span><?= dog__txt('Bine ați venit') ?></span>
				</a>
				<div class="search-bar"><?php get_search_form() ?></div>
				<?php wp_nav_menu(array('theme_location' => 'location-main-menu', 'menu_class' => 'main-menu', 'container' => false)) ?>
			</div>
		</header>
		<main itemprop="mainContentOfPage" class="page-main">