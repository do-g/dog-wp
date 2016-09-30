var dogx = {};
(function($, pub) {

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

  function build_breadcrumbs() {
    $d.breadcrumbs({
      base_items: [
        {url: '/', label: $('#logo').attr('title'), options: {css_class: 'base'}}
      ],
      fix_menu_trail: true
    }).build();
  }

  pub.init_map = function() {
    $(document).ready(function() {
      var center = {lat: 44.444314, lng: 26.019424};
      options = {
        center: center,
        zoom: 17,
        scrollwheel: false,
        disableDefaultUI: true
      };
      var map = get_map(options);
      var marker = new google.maps.Marker({
        position: center,
        map: map,
        icon: {
          url: $s.image_url('marker.svg'),
          scaledSize: new google.maps.Size(50,50)
        }
      });
    });
  }

  document.createElement('main');

})(jQuery, dogx);