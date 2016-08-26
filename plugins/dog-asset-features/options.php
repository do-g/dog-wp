<?php
	require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');
	$messages = dog__get_admin_form_messages();
	$errors = dog__get_admin_form_errors();
?>
<div class="wrap dog-admin--page">
	<h1><?= dog__txt('Opțiuni grupare și comprimare resurse statice') ?></h1>
	<?php if ($messages) {
		foreach ($messages as $m) { ?>
			<div class='updated'><p><strong><?= $m ?></strong></p></div>
		<?php }
	}
	if ($errors) {
		foreach ($errors as $e) { ?>
			<div class='error'><p><strong><?= $e ?></strong></p></div>
		<?php }
	} ?>
	<form name="form" method="post" action="admin-post.php" class="dog-admin--form dog-form-assets">
 		<p class="page-description"><?= dog__txt('Se recomandă ca resursele statice (în general cu extensia .js și .css) să fie grupate împreună și comprimate.
		Acest lucru ajută la optimizarea timpului de răspuns al paginilor care au de descărcat mai puține fișiere și de dimensiuni mai reduse.
 		Nu se recomandă folosirea acestei opțiuni dacă situl este în dezvoltare ci doar după lansare.
 		Verifică mai jos resursele care vor fi incluse apoi apasă butonul pentru a le procesa.') ?></p>
 		<?php
 			$default_items = apply_filters('dog__af_include_css', array());
 			$value = $default_items ? implode("\n", $default_items) : '';
			Dog_Form::render_form_field(array(
				'wrapper' => array(),
				'label' => array(
					'text' => dog__txt('Sunt incluse următoarele fișiere CSS:'),
				),
				'field' => array(
					'tag' => 'textarea',
					'name' => self::OPTION_CSS,
					'value' => $value,
					'readonly' => true,
				),
				'errors' => array(),
			));
			$default_items = apply_filters('dog__af_include_js', array());
	 		$value = $default_items ? implode("\n", $default_items) : '';
			Dog_Form::render_form_field(array(
				'wrapper' => array(),
				'label' => array(
					'text' => dog__txt('Sunt incluse următoarele fișiere JS:'),
				),
				'field' => array(
					'tag' => 'textarea',
					'name' => self::OPTION_JS,
					'value' => $value,
					'readonly' => true,
				),
				'errors' => array(),
			));
			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'input',
					'type' => 'hidden',
					'name' => 'action',
					'value' => 'dog_save_af_options',
				),
			));
			Dog_Form::render_nonce_field('af-options');
			Dog_Form::render_honeypot_field();
		?>
		<p class="submit">
		<?php
			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'button',
					'type' => 'submit',
					'name' => 'optimize',
					'value' => dog__txt('Optimizează resursele'),
					'class' => 'button button-primary',
				),
			));
			Dog_Form::render_form_field(array(
				'field' => array(
					'tag' => 'button',
					'type' => 'button',
					'name' => 'reset',
					'value' => dog__txt('Elimină optimizarea'),
					'class' => 'button',
					'data-confirm' => dog__txt('Ești sigur că vrei să ștergi resursele optimizate?'),
				),
			));
		?>
		</p>
	</form>
</div>