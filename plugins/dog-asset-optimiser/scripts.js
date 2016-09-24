jQuery(document).ready(function(){
	jQuery('.dog-form-assets .form-element-button-button').click(function(){
		var message = jQuery(this).attr('data-confirm');
		if (message && !confirm(message)) {
			return false;
		}
		var $form = jQuery(this).parents('.dog-form-assets');
		var $tmp = jQuery('<input></input>').appendTo($form);
		$tmp.attr('type', 'hidden');
		$tmp.attr('name', jQuery(this).attr('name'));
		$tmp.attr('value', 1);
		$form.submit();
	});
});