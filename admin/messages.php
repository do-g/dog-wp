<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
?>
<div class="dog-admin--messages">
	<div class="dog-admin--message dog-admin--error status-failure dog-admin--timeout dog-admin--hidden">
	    <p><?= __('Sistemul a întâmpinat o eroare. Răspunsul nu poate fi procesat. Codul de eroare este {$code}') ?></p>
	    <button type="button" class="notice-dismiss"></button>
	</div>
	<div class="dog-admin--message dog-admin--error status-ajax dog-admin--timeout dog-admin--hidden">
	    <p><?= __('Sistemul a întâmpinat o eroare. Cererea nu poate fi trimisă. Codul de eroare este {$code}') ?></p>
	    <button type="button" class="notice-dismiss"></button>
	</div>
	<div class="dog-admin--message dog-admin--error status-form dog-admin--timeout dog-admin--hidden">
	    <p><?= __('Formularul nu poate fi validat. Te rugăm să corectezi erorile') ?></p>
	    <button type="button" class="notice-dismiss"></button>
	</div>
	<div class="dog-admin--message dog-admin--error status-noselection dog-admin--timeout dog-admin--hidden">
	    <p><?= __('Acțiunea nu poate fi finalizată. Selectează cel puțin o înregistrare') ?></p>
	    <button type="button" class="notice-dismiss"></button>
	</div>
	<div class="dog-admin--message status-success dog-admin--timeout dog-admin--hidden">
	    <p><?= $dog_admin__alert_success ?></p>
	    <button type="button" class="notice-dismiss"></button>
	</div>
	<div class="dog-admin--message status-success2 dog-admin--timeout dog-admin--hidden">
	    <p><?= $dog_admin__alert_success2 ?></p>
	    <button type="button" class="notice-dismiss"></button>
	</div>
</div>