(function($) {

  $(document).ready(function() {
    if (dog__my.auto_init) {
      $('body').dog_md_youtube_gallery({
        selector: '[rel^="dog-md-youtube-gallery"]'
      });
    }
  });

  $.fn.dog_md_youtube_gallery = function(action_or_options, action_params) {

    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var gallery_class = 'dog-md-youtube-gallery';
    var gallery_attr_url = 'href';
    var gallery_attr_caption = 'title';

    $(document).on('dog_md_gallery.before_advance dog_md.before_hide', function(event, arrow, index, to_index){
      if (!index) {
        index = self.dog_md_gallery('get_active_index');
      }
      if ($('iframe#youtube_player_' + index).size()) {
        $('iframe#youtube_player_' + index).get(0).contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
      }
    });

    $(document).on('dog_md_gallery.after_advance', function(event, arrow, index, to_index){
      if ($('iframe#youtube_player_' + index).size()) {
        $('iframe#youtube_player_' + index).get(0).contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
      }
    });

    function show_item(index, obj, $container) {
      var $wrapper = $('<div></div>').appendTo($container);
      $wrapper.addClass('ratio-container');
      var $ratio = $('<div></div>').appendTo($wrapper);
      $ratio.addClass('ratio');
      var $iframe = $('<iframe></iframe>').appendTo($ratio);
      $iframe.attr('src', $(obj).attr(gallery_attr_url) + '&enablejsapi=1');
      $iframe.attr('frameborder', 0);
      $iframe.attr('id', 'youtube_player_' + index);
    }

    return this.dog_md_gallery($.extend({
      css_class: gallery_class,
      auto_advance_delay: dog__my.auto_advance_delay,
      callbacks: {
        show_item: show_item
      }
    }, action_or_options));

  }

}(jQuery));