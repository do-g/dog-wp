<?php
require_once(realpath(dirname(__FILE__)) . '/../_block-direct-access.php');
$cache = dog_admin__get_output_cache();
?>
<form method="post" id="dog-form-<?= DOG_ADMIN__SECTION_CACHE_OUTPUT ?>">
	<pre>
<?php if ($cache) {
	echo dog__replace_template_vars(__('Am găsit ${n} pagini memorate'), array('n' => count($cache))); ?>
	<br />
	<table>
		<tr>
			<th>&nbsp;</th>
			<th><?= __('Adresă URL') ?></th>
			<th><?= __('Cod identificare') ?></th>
			<th><?= __('Expiră') ?></th>
		</tr>
		<?php foreach ($cache as $i => $row) { ?>
			<tr>
				<td><input type="checkbox" name="rid[<?= $i ?>]" value="<?= dog__get_output_cache_transient_hash($row->option_name) ?>" /></td>
				<td><?= $row->u_option_value ?></td>
				<td><?= dog__get_output_cache_transient_hash($row->option_name) ?></td>
				<td><?= date('Y-m-d H:i:s', $row->option_value) ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } else {
	echo __('Nu există pagini în memoria cache');
} ?>
	</pre>
	<?php
		dog__nonce_field(DOG_ADMIN__NONCE_CACHE_OUTPUT_DELETE);
		dog__honeypot_field();
		dog__render_form_errors();
	?>
</form>