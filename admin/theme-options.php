<?php

if ($_POST['dog_submit']) {
	$labels = $keys = array();
	$pattern = get_stylesheet_directory() . '/*.php';
	$output = $pll_labels_file;
	foreach (glob($pattern) as $file) {
	    preg_match_all("/dog__txt\('(.*?)'\)/", file_get_contents($file), $matches);
	    $labels = array_merge($labels, $matches[1]);
	}
	$content = array("<?php\n", "require_once(realpath(dirname(__FILE__)) . '/_block-direct-access.php');\n");
	foreach ($labels as $l) {
		$key = sanitize_title($l);
		if (!in_array($key, $keys)) {
			array_push($content, "pll_register_string('{$key}', '{$l}', 'theme', true);\n");
			array_push($keys, $key);
		}
	}
	file_put_contents($output, implode('', $content));
	add_action('admin_notices', 'dog_admin_theme_labels_notice');
}

add_action('admin_menu', 'dog_admin__add_menu');

function dog_admin__add_menu(){
	$page_title = null;
	$menu_title = 'Etichete din temă';
	$capability = 'administrator';
	$menu_slug = 'dog-theme-labels';
	$function = 'dog_admin__theme_labels';
    add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $menu_slug, $function);
}

function dog_admin__theme_labels() { ?>
	<div class="wrap">
		<h1>Generator etichete folosite în temă</h1>
		<form name="form" method="post">
  			<p>Folosește această secțiune pentru a genera automat lista de etichete prezente în tema sitului.<br />
  			Sistemul va citi fișierele temei și va extrage etichetele pentru a putea fi modificate.<br />
  			După finalizarea procesului lista de etichete va fi disponibilă în secțiunea <a href="/wp-admin/options-general.php?page=mlang&tab=strings">Limbi disponibile</a>.<br />
  			Apasă butonul de mai jos pentru a începe procesarea.</p>
			<p class="submit"><input type="submit" name="dog_submit" id="submit" class="button button-primary" value="Detectează etichetele"></p>
		</form>
	</div>
<?php }

function dog_admin_theme_labels_notice() { ?>
    <div class="updated">
        <p>Etichetele au fost generate. <a href="/wp-admin/options-general.php?page=mlang&tab=strings">Click aici pentru a modifica.</a></p>
    </div>
<?php }