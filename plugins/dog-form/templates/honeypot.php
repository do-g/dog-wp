<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$dog__form_uses_honeypot = true;
?>
<div class="nocache-tm">
	<input type="hidden" name="<?= DOG__HP_TIMER_NAME ?>" id="<?= DOG__HP_TIMER_NAME ?>" value="<?= microtime(true) ?>" />
</div>
<div class="nocache-hp" style="position: fixed !important; left: -5000% !important;">
	<input type="date" name="<?= DOG__HP_JAR_NAME ?>" id="<?= DOG__HP_JAR_NAME ?>" maxlength="10" min="1997-02-29" max="1997-02-29" />
</div>