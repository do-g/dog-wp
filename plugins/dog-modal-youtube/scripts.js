(function($) {

  $(document).ready(function() {
    if (dog__my.auto_init) {
      $('body').dog_md_youtube_gallery({
        selector: '[rel^="' + dog__my.rel_fragment + '"]'
      });
    }
  });

  $.fn.dog_md_youtube_gallery = function(action_or_options, action_params) {

    $s.debug('Called $.dog_md_youtube_gallery() on', this);
    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var gallery_class = 'dog-md-youtube-gallery';
    var gallery_attr_url = 'href';
    $s.debug('$.dog_md_youtube_gallery() options', options);
    $s.debug('$.dog_md_youtube_gallery() action', action);

    $(document).off('dog_md_youtube_gallery.before_show', add_events);
    $(document).on('dog_md_youtube_gallery.before_show', add_events);

    function add_events() {
      $(document).on('dog_md_youtube_gallery.before_advance_to', pause_video);
      $(document).on('dog_md_youtube_gallery.before_hide', pause_video);
      $(document).on('dog_md_youtube_gallery.after_advance', play_video);
      $(document).on('dog_md_youtube_gallery.after_hide', cancel_events);
    }

    function cancel_events() {
      $(document).off('dog_md_youtube_gallery.before_advance_to', pause_video);
      $(document).off('dog_md_youtube_gallery.before_hide', pause_video);
      $(document).off('dog_md_youtube_gallery.after_advance', play_video);
      $(document).off('dog_md_youtube_gallery.after_hide', cancel_events);
    }

    function pause_video(event, data) {
      var index = data.current_index;
      if ($('iframe#youtube_player_' + index).size()) {
        $s.debug('Stopping video ', index);
        $('iframe#youtube_player_' + index).get(0).contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
      }
    }

    function play_video(event, data) {
      var index = data.current_index;
      if ($('iframe#youtube_player_' + index).size()) {
        $s.debug('Starting video ', index);
        $('iframe#youtube_player_' + index).get(0).contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
      }
    }

    function render_item(index, obj, $container) {
      $s.debug('Called $.dog_md_youtube_gallery().render_item()', index, obj, $container);
      var $wrapper = $('<div></div>').appendTo($container);
      $wrapper.addClass('ratio-container');
      var $ratio = $('<div></div>').appendTo($wrapper);
      $ratio.addClass('ratio');
      var $iframe = $('<iframe></iframe>').appendTo($ratio);
      var url = $(obj).attr(gallery_attr_url);
      url = url.replace('enablejsapi=', 'a64bd7a=');
      if ($container.hasClass('clone')) {
        url = url.replace('autoplay=1', 'autoplay=0');
      }
      $iframe.attr('src', url + '&enablejsapi=1');
      $iframe.attr('frameborder', 0);
      $iframe.attr('id', 'youtube_player_' + index);
    }

    return this.dog_md_gallery($.extend({
      gallery_type: 'dog_md_youtube_gallery',
      css_class: gallery_class,
      loop: dog__my.loop,
      auto_advance_delay: dog__my.auto_advance_delay,
      callbacks: {
        render_item: render_item
      }
    }, action_or_options));

  }

}(jQuery));