$.extend(webfont_config, {
	google: {
    families: []
  },
  custom: {
    families: [],
    testStrings: {
      'somefont': '\f078\f017'
    }
  },
});

preloaderStart(webfont_config, []);

$(document).ready(function() {
	preloadPage();
	$('.form-element').not('input[type=submit]').click(hideFormErrors).focus(hideFormErrors);
	//setTimeout(hideFormErrors, 5000);
	preloader.complete('page');
});