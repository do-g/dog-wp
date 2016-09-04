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
  if (!$s.is_page('.home')) {
    build_breadcrumbs();
  }
	$('.form-element').not('input[type=submit]').click($d.hide_form_errors).focus($d.hide_form_errors);
  $d.preloader_done('session');
});

function fix_menu_trail() {
  var fixed = false;
  var safety_counter = 0;
  while (!fixed) {
    if (safety_counter > 10) {
      return;
    }
    safety_counter++;
    fixed = true;
    $('.main-menu li.current-post-ancestor').each(function(){
      $(this).parents('.menu-item').not('.current-post-ancestor').addClass('current-post-ancestor');
      fixed = false;
    });
  }
}

function build_breadcrumbs() {
  fix_menu_trail();
  var bc = [{url: '/', label: $('#logo').attr('title')}];
  var menu_active_trail_selector = [
    '.main-menu li.current-menu-ancestor > a',
    '.main-menu li.current-menu-item > a',
    '.main-menu li.current-post-ancestor > a'
  ].join(', ');
  $(menu_active_trail_selector).each(function(){
    var item = {url: $(this).attr('href'), label: $(this).text()};
    if (!$s.in_array(item, bc)) {
      bc.push(item);
    }
  });
  if (bc) {
    for (var b in bc) {
      var $a = $('<a></a>').appendTo('.breadcrumbs');
      $a.attr('href', bc[b].url);
      $a.text(bc[b].label);
    }
  }
}

document.createElement('main');