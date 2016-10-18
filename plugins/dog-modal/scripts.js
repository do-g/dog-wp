(function($) {

  $(document).ready(function() {
    if (dog__md.popup.auto_init) {
      $('body').dog_md();
    }
    if (dog__md.image_gallery.auto_init) {
      $('body').dog_md_image_gallery({
        selector: '[rel^="' + dog__md.image_gallery.rel_fragment + '"]'
      });
    }
  });

  $.fn.dog_md = function(action_or_options, action_params) {

    $s.debug('Called $.dog_md() on', this);
    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var window_selector = '.dog-md-window';
    var switch_class = 'dog-md';
    var frozen_class = 'frozen';
    $s.debug('$.dog_md() options', options);
    $s.debug('$.dog_md() action', action);

    function init() {
      $s.debug('Triggering dog_md.before_init');
      $(document).trigger('dog_md.before_init');
      $(window_selector).off($s.transitionend(), transition_complete);
      $(window_selector).on($s.transitionend(), transition_complete);
      self.on('click', '.dog-md-close', hide);
      $s.debug('Triggering dog_md.after_init');
      $(document).trigger('dog_md.after_init');
    }

    function transition_complete(event) {
      if ($s.event_has_bubbled(event)) {
        return;
      }
      if (self.hasClass(switch_class)) {
        $s.debug('Triggering dog_md.after_show');
        $(document).trigger('dog_md.after_show');
      } else {
        $s.debug('Triggering dog_md.after_hide');
        $(document).trigger('dog_md.after_hide');
      }
    }

    function load(content) {
      $s.debug('Triggering dog_md.before_load');
      $(document).trigger('dog_md.before_load');
      var $body = self.find('.dog-md-body');
      $s.debug('Loading modal content', content);
      if (typeof content === 'object') {
        $body.append(content);
      } else {
        $body.html(content);
      }
      $s.debug('Triggering dog_md.after_load');
      $(document).trigger('dog_md.after_load');
    }

    function unload() {
      $s.debug('Triggering dog_md.before_unload');
      $(document).trigger('dog_md.before_unload');
      self.find('.dog-md-body').empty();
      $s.debug('Triggering dog_md.after_unload');
      $(document).trigger('dog_md.after_unload');
    }

    function show() {
      $s.debug('Triggering dog_md.before_show');
      $(document).trigger('dog_md.before_show');
      self.addClass([frozen_class, switch_class].join(' '));
    }

    function hide() {
      $s.debug('Triggering dog_md.before_hide');
      $(document).trigger('dog_md.before_hide');
      self.removeClass([frozen_class, switch_class].join(' '));
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

    $s.debug('Called $.dog_md_gallery() on', this);
    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var gallery_class = 'dog-md-gallery';
    var gallery_class_cached = 'dog-md-gallery-cached';
    var gallery_class_list = 'gallery-list';
    var gallery_class_loop = 'loop';
    var gallery_class_single = 'single';
    var gallery_class_left_end = 'left-end';
    var gallery_class_right_end = 'right-end';
    var gallery_attr_index = 'data-index';
    var gallery_attr_dir = 'data-dir';
    var gallery_attr_caption = 'title';
    var gallery_dir_left = 'l';
    var gallery_dir_right = 'r';
    var auto_advance_interval;
    var info;
    var $items;
    var $ul;
    var $gallery;
    var $modal_container;
    var $shown_obj;
    $s.debug('$.dog_md_gallery() options', options);
    $s.debug('$.dog_md_gallery() action', action);

    /***** start events *****/

    function cancel_events() {
      $(document).off('dog_md.before_hide', before_hide);
      $(document).off('dog_md.after_hide', after_hide);
      $(document).off('dog_md.after_show', after_show);
      $(document).off('dog_md.before_load', before_load);
      $(document).off('dog_md.after_load', after_load);
      $(document).off('dog_md.before_unload', before_unload);
      $(document).off('dog_md.after_unload', after_unload);
    }

    function add_events() {
      $(document).on('dog_md.before_hide', before_hide);
      $(document).on('dog_md.after_hide', after_hide);
      $(document).on('dog_md.after_show', after_show);
      $(document).on('dog_md.before_load', before_load);
      $(document).on('dog_md.after_load', after_load);
      $(document).on('dog_md.before_unload', before_unload);
      $(document).on('dog_md.after_unload', after_unload);
    }

    function before_hide(event) {
      $s.debug('Triggering ' + gallery_type() + '.before_hide', {current_index: get_active_index()});
      $(document).trigger(gallery_type() + '.before_hide', {current_index: get_active_index()});
      stop_slideshow();
    }

    function after_hide(event) {
      $s.debug('Triggering ' + gallery_type() + '.after_hide');
      $(document).trigger(gallery_type() + '.after_hide');
      cancel_events();
    }

    function after_show(event) {
      $s.debug('Triggering ' + gallery_type() + '.after_show', {$shown_obj: $shown_obj});
      $(document).trigger(gallery_type() + '.after_show', {$shown_obj: $shown_obj});
    }

    function before_load(event) {
      $s.debug('Triggering ' + gallery_type() + '.before_load');
      $(document).trigger(gallery_type() + '.before_load');
    }

    function after_load(event) {
      $s.debug('Triggering ' + gallery_type() + '.after_load');
      $(document).trigger(gallery_type() + '.after_load');
    }

    function before_unload(event) {
      $s.debug('Triggering ' + gallery_type() + '.before_unload');
      $(document).trigger(gallery_type() + '.before_unload');
    }

    function after_unload(event) {
      $s.debug('Triggering ' + gallery_type() + '.after_unload');
      $(document).trigger(gallery_type() + '.after_unload');
    }

    /***** end events *****/

    function init() {
      $s.debug('Triggering ' + gallery_type() + '.before_init');
      $(document).trigger(gallery_type() + '.before_init');
      var selector = options.selector || null;
      self.on('click', selector, function(){
        show(this);
        return false;
      });
      $s.debug('Triggering ' + gallery_type() + '.after_init');
      $(document).trigger(gallery_type() + '.after_init');
    }

    function gallery_type() {
      return options.gallery_type || 'dog_md_gallery';
    }

    function get_active_index() {
      var $u = $ul || $('.' + gallery_class + ' .' + gallery_class_list);
      return parseInt($u.attr(gallery_attr_index));
    }

    function get_info(gallery_data) {
      info = {
        id: gallery_data,
        uid: gallery_data.replace('[', '-').replace(']', ''),
        group: gallery_data.indexOf('[') >= 0 ? gallery_data.replace('[', '|').replace(']', '').split('|').pop() : null
      };
    }

    function advance(dir, is_slideshow) {
      var $li, index;
      index = get_active_index();
      $s.debug('Triggering ' + gallery_type() + '.before_advance', {current_index: index, dir: dir, is_slideshow: is_slideshow});
      $(document).trigger(gallery_type() + '.before_advance', {current_index: index, dir: dir, is_slideshow: is_slideshow});
      if (!is_slideshow) {
        stop_slideshow();
      }
      $li = $ul.children('li');
      max = $li.size() - 2;
      if (dir == gallery_dir_left) {
        index--;
        if (index < 1) {
          advance_to(max + 1, true);
          return advance(dir);
        }
      } else {
        index++;
        if (index > max) {
          advance_to(0, true);
          return advance(dir);
        }
      }
      advance_to(index);
    }

    function advance_to(index, skip_transition) {
      var $li, obj, to_index, max, left;
      to_index = index;
      $li = $ul.children('li');
      max = $li.size() - 1;
      if (index < 0) {
        index = 0;
      } else if (index > max) {
        index = max;
      }
      $s.debug('Triggering ' + gallery_type() + '.before_advance_to', {to_index: index, requested_index: to_index, current_index: get_active_index(), skip_transition: skip_transition});
      $(document).trigger(gallery_type() + '.before_advance_to', {to_index: index, requested_index: to_index, current_index: get_active_index(), skip_transition: skip_transition});
      $ul.attr(gallery_attr_index, index);
      left = -(index * 100) + '%';
      if (skip_transition) {
        $s.skip_transition($ul, function(){
          $ul.css('left', left);
        });
        advanced();
      } else {
        $ul.css('left', left);
      }
      $li = $li.eq(index);
      if ($li.hasClass('loading')) {
        if (options && options.callbacks && options.callbacks.render_item) {
          obj = $items.eq(index - 1).get(0);
          $s.debug('Rendering gallery item', index, obj, $li);
          options.callbacks.render_item(index, obj, $li);
          $li.removeClass('loading');
        }
      }
    }

    function advanced(event) {
      if ($s.event_has_bubbled(event)) {
        return;
      }
      var index = get_active_index();
      var total = $ul.children('li').size() - 2;
      update_counter(index, total);
      update_nav(index, total);
      $s.debug('Triggering ' + gallery_type() + '.after_advance', {current_index: index});
      $(document).trigger(gallery_type() + '.after_advance', {current_index: index});
    }

    function update_counter(index, total) {
      var label = index + ' ' + dog__md.gallery.labels.of + ' ' + total;
      $gallery.children('aside').html(label);
    }

    function update_nav(index, total) {
      if (options.loop) {
        return;
      }
      $gallery.toggleClass(gallery_class_right_end, index == total);
      $gallery.toggleClass(gallery_class_left_end, index == 1);
    }

    function show(obj) {
      $shown_obj = $s.to_jquery(obj);
      $s.debug('Triggering ' + gallery_type() + '.before_show', {$shown_obj: $shown_obj});
      $(document).trigger(gallery_type() + '.before_show', {$shown_obj: $shown_obj});
      add_events();
      $modal_container = $s.to_jquery(options.modal_container || 'body');
      get_info($shown_obj.attr('rel'));
      $items = info.group ? self.find('[rel="' + info.id + '"]') : $shown_obj;
      // index + 1 because of the cloned item at the beginning of the list
      var index = $items.index(obj) + 1;
      // if the same gallery has been called twice
      // it is already built so show it
      // just the index needs to be set
      if (info.group && self.find('.' + info.uid).size()) {
        $s.debug('Gallery is cached. Showing', index);
        $gallery.addClass(gallery_class_cached);
        advance_to(index, true);
        $modal_container.dog_md('show');
        return;
      }
      $gallery = $('<div></div>');
      $gallery.addClass(gallery_class);
      $gallery.addClass(options.css_class);
      $gallery.addClass(info.uid);
      if (!info.group) {
        $gallery.addClass(gallery_class_single);
      }
      if (options.loop) {
        $gallery.addClass(gallery_class_loop);
      }
      $modal_container.dog_md('unload');
      $modal_container.dog_md('load', $gallery);
      var $details = $('<aside></aside>').appendTo($gallery);
      $ul = $('<ul></ul>').appendTo($gallery);
      $ul.addClass(gallery_class_list);
      $ul.attr(gallery_attr_index, index);
      $ul.off($s.transitionend(), advanced);
      $ul.on($s.transitionend(), advanced);
      var $li, $caption, caption_text;
      $items.each(function(n, elem){
        $s.debug('Processing gallery item', this);
        $li = $('<li></li>').appendTo($ul);
        $li.addClass('loading');
        $caption = $('<figcaption></figcaption>').appendTo($li);
        caption_text = $(this).attr(gallery_attr_caption) ? $(this).attr(gallery_attr_caption) : $(this).find('img').attr(gallery_attr_caption);
        $li.children('figcaption').text(caption_text);
        if (options && options.callbacks && options.callbacks.prepare_item) {
          $s.debug('Preparing gallery item', n + 1, this, $li);
          options.callbacks.prepare_item(n + 1, this, $li);
        }
      });
      add_clones();
      var $nav = $('<nav></nav>').appendTo($gallery);
      var $left = $('<img />').appendTo($nav);
      $left.attr('src', dog__md.gallery.images.left_arrow_url);
      $left.attr(gallery_attr_dir, gallery_dir_left);
      $left.addClass('left');
      var $right = $('<img />').appendTo($nav);
      $right.attr('src', dog__md.gallery.images.right_arrow_url);
      $right.attr(gallery_attr_dir, gallery_dir_right);
      $right.addClass('right');
      $nav.find('img').click(function(){
        advance($(this).attr(gallery_attr_dir));
      });
      if ($items.size() < 2) {
        $gallery.removeClass(gallery_class_loop);
        $gallery.addClass(gallery_class_left_end).addClass(gallery_class_right_end);
      }
      $ul.css('width', $ul.children('li').size() + '00%');
      advance_to(index, true);
      $modal_container.dog_md('show');
      start_slideshow();
    }

    function add_clones() {
      var $li = $ul.find('li');
      var $first = $li.first().clone();
      var $last = $li.last().clone();
      $last.addClass('clone last').prependTo($ul);
      $first.addClass('clone first').appendTo($ul);
      $li = $ul.find('li');
      if (options && options.callbacks && options.callbacks.render_item) {
        options.callbacks.render_item(0, $items.last().get(0), $last);
        $last.removeClass('loading');
        options.callbacks.render_item($li.size() - 1, $items.first().get(0), $first);
        $first.removeClass('loading');
      }
    }

    function start_slideshow() {
      if (options.slideshow_delay && parseInt(options.slideshow_delay) && $items.size() > 1) {
        auto_advance_interval = setInterval(function(){
          advance(gallery_dir_right, true);
        }, options.slideshow_delay);
      }
    }

    function stop_slideshow() {
      clearTimeout(auto_advance_interval);
    }

    switch (action) {
      case 'advance':
        advance(action_params);
        break;
      case 'advance_to':
        advance_to(action_params.index || action_params, action_params.skip_transition);
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

    $s.debug('Called $.dog_md_image_gallery() on', this);
    var self = this;
    var action  = typeof action_or_options === 'string' ? action_or_options : null;
    var options = typeof action_or_options === 'object' ? action_or_options : {};
    var gallery_class = 'dog-md-image-gallery';
    var gallery_attr_url = 'href';
    var gallery_attr_img = 'data-img';
    var gallery_attr_download = 'data-download';
    $s.debug('$.dog_md_image_gallery() options', options);
    $s.debug('$.dog_md_image_gallery() action', action);

    function prepare_item(index, obj, $container) {
      $s.debug('Called $.dog_md_image_gallery().prepare_item()', index, obj, $container);
      var img_url = $(obj).attr(gallery_attr_url);
      $container.attr(gallery_attr_img, img_url);
    }

    function render_item(index, obj, $container) {
      $s.debug('Called $.dog_md_image_gallery().render_item()', index, obj, $container);
      var img_url = $container.attr(gallery_attr_img);
      var download_url = $(obj).attr(gallery_attr_download);
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
      if (download_url) {
        var $a = $('<a></a>').appendTo($container);
        $a.addClass('download');
        $a.attr('href', download_url);
        $a.attr('download', img_url.split('/').pop());
        $a.html('&#8681;');
      }
    }

    return this.dog_md_gallery($.extend({
      gallery_type: 'dog_md_image_gallery',
      css_class: gallery_class,
      loop: dog__md.image_gallery.loop,
      slideshow_delay: dog__md.image_gallery.slideshow_delay,
      callbacks: {
        prepare_item: prepare_item,
        render_item: render_item
      }
    }, action_or_options));

  }

}(jQuery));