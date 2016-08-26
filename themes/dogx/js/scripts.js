$d.set_webfonts({
  google: {
    families: []
  },
  custom: {
    families: [],
    testStrings: {
      'somefont': '\f078\f017'
    }
  }
});

$d.preload(['sessions', 'fonts', 'images']);

$(document).ready(function() {
	$d.preload_images();
	$('.form-element').not('input[type=submit]').click($d.hide_form_errors).focus($d.hide_form_errors);
  $d.preloader_done('session');
});