$(document).ready(function() {
	$('.form-element').not('input[type=submit]').click(hideFormErrors).focus(hideFormErrors);
	//setTimeout(hideFormErrors, 5000);
});

function hideFormErrors() {
	$('.form-error').hide();
}