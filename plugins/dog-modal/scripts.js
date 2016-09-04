(function($) {

  $(document).ready(function() {
    if (dog__md.popup.auto_init) {
      $('body').dog_md();
    }
    if (dog__md.image_gallery.auto_init) {
      $('body').dog_md_image_gallery({
        selector: '[rel^="dog-md-image-gallery"]'
      });
    }
  });

  $.fn.dog_md = function(action_or_options, action_params) {

    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};

    function init() {
      $(document).trigger('dog_md.before_init');
      self.on('click', '.dog-md-close', hide);
      $(document).trigger('dog_md.after_init');
    }

    function load(content) {
      unload();
      $(document).trigger('dog_md.before_load');
      var $body = self.find('.dog-md-body');
      if (typeof content === 'object') {
        $body.append(content);
      } else {
        $body.html(content);
      }
      $(document).trigger('dog_md.after_load');
    }

    function unload() {
      $(document).trigger('dog_md.before_unload');
      self.find('.dog-md-body').empty();
      $(document).trigger('dog_md.after_unload');
    }

    function show() {
      $(document).trigger('dog_md.before_show');
      self.addClass('dog-md');
      $(document).trigger('dog_md.after_show');
    }

    function hide() {
      $(document).trigger('dog_md.before_hide');
      self.removeClass('dog-md');
      $(document).trigger('dog_md.after_hide');
    }

    switch (action) {
      case 'load':
        load(action_params);
        break;
      case 'unload':
        unload();
        break;
      case 'show':
        show();
        break;
      case 'hide':
        hide();
        break;
      default:
        init();
        break;
    }

    return this;
  }

  $.fn.dog_md_gallery = function(action_or_options, action_params) {

    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var gallery_class = 'dog-md-gallery';
    var gallery_class_cached = 'dog-md-gallery-cached';
    var gallery_attr_index = 'data-index';
    var gallery_attr_caption = 'title';
    var auto_advance_timeout;
    var info;
    var $items;

    function get_active_index() {
      return self.find('.' + gallery_class + ' > ul').attr(gallery_attr_index);
    }

    function get_info(gallery_data) {
      info = {
        id: gallery_data,
        uid: gallery_data.replace('[', '-').replace(']', ''),
        group: gallery_data.indexOf('[') >= 0 ? gallery_data.replace('[', '|').replace(']', '').split('|').pop() : null
      };
    }

    function advance(arrow, to_index) {
      clearTimeout(auto_advance_timeout);
      index = to_index;
      var $ul = self.find('.' + gallery_class + ' > ul');
      var current_index = $ul.attr(gallery_attr_index);
      $(document).trigger('dog_md_gallery.before_advance', [arrow, current_index, to_index]);
      if (index === null || index === undefined) {
        index = parseInt(current_index);
        index = arrow ? ($(arrow).hasClass('left') ? index - 1 : index + 1) : index;
      }
      var $lis = $ul.children('li');
      var lis_count = $lis.size();
      index = index < 0 ? lis_count - 1 : index;
      index = index >= lis_count ? 0 : index;
      var $li = $lis.eq(index);
      var obj = $items.eq(index);
      if ($li.hasClass('loading')) {
        if (options && options.callbacks && options.callbacks.show_item) {
          options.callbacks.show_item(index, obj, $li);
        }
      }
      $ul.attr(gallery_attr_index, index);
      $ul.css('left', -(index * 100) + '%');
      $('.' + gallery_class + ' > aside').html((index + 1) + ' ' + dog__md.gallery.labels.of + ' ' + lis_count);
      $li.removeClass('loading');
      $(document).trigger('dog_md_gallery.after_advance', [arrow, index, to_index]);
      if (options.auto_advance_delay && parseInt(options.auto_advance_delay) && !arrow && $items.size() > 1) {
        auto_advance_timeout = setTimeout(function(){
          advance(null, index + 1);
        }, options.auto_advance_delay);
      }
    }

    function init() {
      var selector = options.selector || null;
      self.on('click', selector, function(){
        show(this);
        return false;
      });
      $(document).on('dog_md.after_hide', function(){
        clearTimeout(auto_advance_timeout);
      });
      $(document).trigger('dog_md_gallery.after_init');
    }

    function show(obj) {
      var $modal_container = $s.to_jquery(options.modal_container || 'body');
      get_info($(obj).attr('rel'));
      $items = info.group ? self.find('[rel="' + info.id + '"]') : $(obj);
      var index = $items.index(obj);
      // if the same gallery has been called twice
      // it is already built so show it
      // just the index needs to be set
      if (info.group && self.find('.' + info.uid).size()) {
        self.find('.' + gallery_class).addClass(gallery_class_cached);
        advance(null, index);
        $modal_container.dog_md('show');
        return;
      }
      var $gallery = $('<div></div>');
      $gallery.addClass(gallery_class);
      $gallery.addClass(options.css_class);
      $gallery.addClass(info.uid);
      $modal_container.dog_md('load', $gallery);
      var $details = $('<aside></aside>').appendTo($gallery);
      var $ul = $('<ul></ul>').appendTo($gallery);
      $ul.attr(gallery_attr_index, index);
      $ul.css('width', $items.size() + '00%');
      $items.each(function(){
        var $li = $('<li></li>').appendTo($ul);
        $li.addClass('loading');
        var $caption = $('<figcaption></figcaption>').appendTo($li);
        var caption_text = $(this).attr(gallery_attr_caption) ? $(this).attr(gallery_attr_caption) : $(this).find('img').attr(gallery_attr_caption);
        $li.children('figcaption').text(caption_text);
        if (options && options.callbacks && options.callbacks.prepare_item) {
          options.callbacks.prepare_item(index, this, $li);
        }
      });
      if ($items.size() > 1) {
        var $nav = $('<nav></nav>').appendTo($gallery);
        var $left = $('<img />').appendTo($nav);
        $left.attr('src', dog__md.gallery.images.left_arrow_url);
        $left.addClass('left');
        var $right = $('<img />').appendTo($nav);
        $right.attr('src', dog__md.gallery.images.right_arrow_url);
        $right.addClass('right');
        $nav.find('img').click(function(){
          advance(this);
        });
      }
      advance(null, index);
      $modal_container.dog_md('show');
      $(document).trigger('dog_md_gallery.after_show', obj);
    }

    switch (action) {
      case 'advance':
        advance(action_params.obj, action_params.index);
        break;
      case 'get_active_index':
        return get_active_index();
      default:
        init();
        break;
    }

    return this;
  }

  $.fn.dog_md_image_gallery = function(action_or_options, action_params) {

    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var gallery_class = 'dog-md-image-gallery';
    var gallery_attr_url = 'href';
    var gallery_attr_download = 'data-download';

    function show_item(index, obj, $container) {
      var img_url = $(obj).attr(gallery_attr_url);
      var download_url = $(obj).attr(gallery_attr_download) ? $(obj).attr(gallery_attr_download) : img_url;
      if (dog__md.image_gallery.use_background_images) {
        $container.css('background-image', 'url(' + img_url + ')');
      } else {
        var $helper = $('<div></div>').appendTo($container);
        $helper.addClass('v-center-helper');
        var $img = $('<img />').appendTo($container);
        $img.addClass('contain-width');
        $img.on('load', function() {
          if ($img.height() > $container.height()) {
            $img.removeClass('contain-width').addClass('contain-height');
          }
        });
        $img.attr('src', img_url);
      }
      var $a = $('<a></a>').appendTo($container);
      $a.addClass('download');
      $a.attr('href', download_url);
      $a.attr('download', img_url.split('/').pop());
      $a.html('&#8681;');
    }

    return this.dog_md_gallery($.extend({
      css_class: gallery_class,
      auto_advance_delay: dog__md.image_gallery.auto_advance_delay,
      callbacks: {
        show_item: show_item
      }
    }, action_or_options));

  }

}(jQuery));