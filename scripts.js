$(document).ready(function() {
	$('.form-element').not('input[type=submit]').click(hideFormErrors).focus(hideFormErrors);
	setTimeout(hideFormErrors, 5000);
	$('.home h1.page-title, .uri--contact h1.page-title').lettering('words');
});

function hideFormErrors() {
	$('.form-error').hide();
}