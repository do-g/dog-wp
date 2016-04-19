function dog__shared_lib() {

  var self = this;

  /***** webfonts *****/

  this.font_loading = function (family, variation) {
    preloader.register(preloader_font_to_key(family, variation));
  }

  this.font_ready = function (family, variation) {
    preloader.done(preloader_font_to_key(family, variation));
  }

  this.fonts_ready = function () {
    preloader.done('fonts');
  }

  var webfont_config = {
    classes: false,
    fontloading: self.font_loading,
    fontactive: self.font_ready,
    fontinactive: self.font_ready,
    active: self.fonts_ready,
    inactive: self.fonts_ready
  };

  this.set_webfonts = function (custom_fonts) {
    jQuery.extend(webfont_config, custom_fonts);
  }

  function webfonts_registered() {
    return webfont_config.google.families.length || webfont_config.custom.families.length;
  }

  /***** preloader *****/

  var preloader = {
  	timeout: null,
  	wait: 10000,
  	reset: function() {
      clearTimeout(this.timeout);
      this.$container = null;
      this.debug = false;
      this.queue = {};
      this.times = {};
      this.assets = ['sessions'];
      this.images_registered = false;
      this.count_images_loaded = 0;
  	},
  	register: function(key, asset_type) {
      if (!asset_type || this.watches(asset_type)) {
  		  this.queue[key] = false;
        if (this.debug) {
          var t = this.set_time_registered(key);
          console.log('registered key [' + key + '] of type [' + asset_type + '] at ' + t);
        }
        return true;
      }
      return false;
  	},
  	done: function(key) {
  	  if (this.queue.hasOwnProperty(key)) {
  			this.queue[key] = true;
        if (this.debug) {
          var t = this.get_time_elapsed(key);
          console.log('completed key [' + key + '] in [' + t[0] + '] seconds at ' + t[1]);
        }
  			this.check();
        return true;
  	  }
      return false;
  	},
  	check: function() {
  	  var total = done = 0;
  	  for (key in this.queue) {
  	    total++;
  	    if (this.queue[key]) {
  	      done++;
  	    }
  	  }
  	  var percent = done / total * 100;
  	  return this.update(percent);
  	},
  	update: function(percent) {
  	  percent = percent && percent > 1 ? percent : 1;
  	  jQuery('.preloader .bar').css('width', percent + '%');
  	  if (percent == 100) {
  	    this.trigger_complete();
  	  }
      return percent;
  	},
    listen: function() {
      jQuery(document).on('dog.preloader_complete', function() {
        preloader.complete();
      });
    },
    trigger_complete: function() {
      jQuery(document).trigger('dog.preloader_complete');
    },
    complete: function() {
      jQuery('body').removeClass('preloading').addClass('ready');
      if (this.debug) {
        var t = this.get_time_elapsed('preloader');
        console.log('completed key [preloader] in [' + t[0] + '] seconds at ' + t[1]);
      }
      this.reset();
    },
    force_complete: function() {
      for (key in this.queue) {
        if (!this.queue[key]) {
          console.log('failed to load key: ' + key);
        }
        this.queue[key] = true;
      }
      this.check();
    },
    watch: function (list) {
      jQuery.extend(this.assets, list);
    },
    watches: function (type) {
      return self.in_array(type, this.assets);
    },
    set_time_registered: function (key) {
      var d = new Date();
      var t = d.getTime();
      this.times[key] = t;
      return t;
    },
    get_time_elapsed: function (key) {
      var d = new Date();
      var t = d.getTime();
      var secs = (t - this.times[key]) / 1000;
      return [secs.toPrecision(2), t];
    }
  };

  this.preload = function (asset_types, container_selector, debug) {
    preloader.reset();
    preloader.debug = debug;
    if (preloader.debug) {
      preloader.set_time_registered('preloader');
    }
    preloader.listen();
    container_selector = container_selector ? container_selector : 'body';
    preloader.$container = jQuery(container_selector);
    preloader.watch(asset_types);
    preloader.timeout = setTimeout(function(){
      preloader.force_complete();
    }, preloader.wait);
    this.preloader_register('session', 'sessions');
    if (webfonts_registered() && preloader.watches('fonts')) {
      preloader.register('fonts');
      WebFont.load(webfont_config);
    }
  }

  this.preload_images = function () {
    if (!preloader.watches('images')) {
      return false;
    }
    preloader.images_registered = false;
    preloader.count_images_loaded = 0;
    preloader.register('images');
    preloader.$container.imagesLoaded({ background: '.has-bg-img' }, function() {
      preloader.done('images');
    }).progress(function(instance, image) {
      if (!preloader.images_registered) {
        preloader_register_images(instance.images.length);
      }
      preloader.count_images_loaded++;
      preloader.done(preloader_image_to_key(preloader.count_images_loaded));
    });
    return true;
  }

  this.preloader_register = function(key, asset_type) {
    return preloader.register(key, asset_type);
  }

  this.preloader_done = function(key) {
    return preloader.done(key);
  }

  function preloader_image_to_key(number) {
    return self.string_to_key(number, 'image');
  }

  function preloader_font_to_key(family, variation) {
    return self.string_to_key(family + variation, 'font');
  }

  function preloader_register_images(count) {
    preloader.images_registered = true;
    for (n = 1; n <= count; n++) {
      preloader.register(preloader_image_to_key(n));
    }
  }

  /***** usefull functions *****/

  this.string_to_key = function (value, prefix, suffix) {
    value += '';
    value = value.toLowerCase();
    value = value.replace(/[-]+/g, '_');
    value = value.replace(/\s+/g, '_');
    value = value.replace(/\W+/g, '');
    return (prefix ? prefix : '') + value + (suffix ? suffix : '');
  }

  this.hide_form_errors = function (error_selector) {
    error_selector = error_selector ? error_selector : '.form-error';
  	jQuery(error_selector).hide();
  }

  this.is_screen = function (breakpoint) {
  	return jQuery('.device-' + breakpoint).is(':visible')
  }

  this.is_page = function (selector) {
  	return jQuery('body').is(selector);
  }

  this.page_scroll_to = function (target, options, callback) {
    jQuery(window).scrollTo(target, options, callback);
  }

  this.theme_url = function (path) {
  	return dog__wp.theme_url + (path ? '/' + path.trimLeft('/') : '');
  }

  this.image_url = function (name) {
  	return this.theme_url('images/' + name);
  }

  function nonce_var_key (name) {
    return self.string_to_key(name, dog__wp.DOG__NC_VAR_PREFIX);
  }

  this.get_nonce = function (key) {
    return dog__wp[nonce_var_key(key)];
  }

  this.get_nonce_name = function () {
    return dog__wp.DOG__NC_NAME;
  }

  this.ajax_init = function () {
    jQuery.ajaxSetup({
      url: dog__wp.ajax_url,
      method: 'POST'
    });
  }

  function ajax_default_data() {
    return {
      action: dog__wp.DOG__WP_ACTION_AJAX_CALLBACK
    };
  }

  this.ajax_prepare_data = function (data, nonce) {
    data = jQuery.extend(ajax_default_data(), data);
    if (nonce) {
      data[this.get_nonce_name()] = nonce;
    }
    return data;
  }

  this.validate_response_nonce = function (response, match) {
    var response_nonce = response[this.get_nonce_name()];
    return response_nonce && response_nonce == match;
  }

  this.is_response_error = function (response) {
    return response.status == dog__wp.DOG__AJAX_RESPONSE_STATUS_ERROR;
  }

  this.form_to_object = function (form){
    var data = {};
    jQuery.each(jQuery(form).serializeArray(), function(n, pair) {
      data[pair.name] = pair.value;
    });
    return data;
  }

  this.form_validate_not_empty = function (data) {
    var ignore = [dog__wp.DOG__NC_NAME, dog__wp.DOG__HP_JAR_NAME, dog__wp.DOG__HP_TIMER_NAME, '_wp_http_referer'];
    var valid = false;
    for (var key in data) {
      if (this.in_array(key, ignore)) {
        continue;
      }
      if (data[key]) {
        return true;
      }
    }
    return false;
  }

  this.in_array = function (needle, haystack) {
    return jQuery.inArray(needle, haystack) !== -1;
  }

  this.class_to_selector = function (class_name) {
    return '.' + class_name;
  }

  this.prepare_offset = function (offset) {
    offset = offset ? offset : {};
    offset.top = offset.top ? offset.top : 0;
    offset.bottom = offset.bottom ? offset.bottom : 0;
    offset.left = offset.left ? offset.left : 0;
    offset.right = offset.right ? offset.right : 0;
    return offset;
  }

}

/***** internal functions *****/

function __inviewport_prepare_parent(parent) {
  var $parent = parent ? jQuery(parent) : jQuery(window);
  return {
    height: $parent.height(),
    width: $parent.width(),
    top: $parent.scrollTop(),
    left: $parent.scrollLeft()
  };
}

function __inviewport_prepare_child($elem) {
  return {
    height: $elem.outerHeight(),
    width: $elem.outerWidth(),
    top: $elem.offset().top,
    left: $elem.offset().left
  };
}

/***** jQuery overrides *****/

jQuery.fn.fullyInViewport = function(parent, offset) {
  var p = __inviewport_prepare_parent(parent);
  var c = __inviewport_prepare_child(this);
  var o = $d.prepare_offset(offset);
  var above_top = p.top + o.top > c.top;
  var below_bottom = p.top + p.height - o.bottom < c.top + c.height;
  var left_of = p.left + o.left > c.left;
  var right_of = p.left + p.width - o.right < c.left + c.width;
  return !above_top && !below_bottom && !left_of && !right_of;
}

jQuery.fn.partiallyInViewport = function(parent, offset) {
  var p = __inviewport_prepare_parent(parent);
  var c = __inviewport_prepare_child(this);
  var o = $d.prepare_offset(offset);
  var above_top = p.top >= c.top + c.height;
  var below_bottom = p.top + p.height <= c.top;
  var left_of = p.left >= c.left + c.width;
  var right_of = p.left + p.width <= c.left;
  return !above_top && !below_bottom && !left_of && !right_of;
}

jQuery.fn.initToggleParentClass = function(events, options) {
  if (events) {
    var settings = jQuery.extend({
      css_class: 'active',
      parent_selector: null
    }, options);
    this.on(events, settings, function(event){
      jQuery(event.delegateTarget).toggleParentClass(event.data.css_class, event.data.parent_selector);
    });
  }
  return this;
}

jQuery.fn.toggleParentClass = function(css_class, parent_selector) {
  $parent = parent_selector ? this.parents(parent_selector) : this.parent();
  $parent.toggleClass(css_class);
  return this;
}

jQuery.fn.activateItem = function(index, selector, active_class) {
	index = index ? index : 0;
	active_class = active_class ? active_class : 'active';
	var $items = selector ? this.filter(selector) : this;
  $items.removeClass(active_class);
  var $next = $items.eq(index);
  $next.addClass(active_class);
  return $next;
}

jQuery.fn.activateNextItem = function(selector, active_class) {
	active_class = active_class ? active_class : 'active';
  var $active = this.filter('.' + active_class);
 	if (!$active.size()) {
 		$active = this.last();
 	}
  $active.removeClass(active_class);
  var $next = $active.nextSibling();
  $next.addClass(active_class);
  return $next;
}

jQuery.fn.nextSibling = function(selector, reverse) {
  var $next = reverse ? this.prev(selector) : this.next(selector);
  if (!$next.size()) {
    var $siblings = this.siblings(selector);
    $next = $siblings.size() ? (reverse ? $siblings.last() : $siblings.first()) : this;
  }
  return $next;
}

jQuery.fn.prevSibling = function(selector) {
  return this.nextSibling(selector, true);
}

jQuery.fn.scrollTo = function(target, options, callback){
  var settings = jQuery.extend({
    target: target,
    easing: 'swing'
  }, options);
  settings.speed = settings.speed ? settings.speed : 600;
  settings.offset = settings.offset ? settings.offset : 0;
  this.each(function() {
    var $scrollPane = jQuery(this);
    var $animationPane = $scrollPane;
    var callback_for = null;
    var parent_scroll_top = $scrollPane.scrollTop();
    if (this == window) {
      $animationPane = jQuery('body, html');
      callback_for = 'html';
      parent_scroll_top = 0;
    }
    var scrollY;
    if (typeof settings.target == 'number') {
      scrollY = settings.target;
    } else {
      var $scrollTarget = jQuery(settings.target);
      var position_from_top = this == window ? $scrollTarget.offset().top : $scrollTarget.position().top;
      scrollY = position_from_top + parent_scroll_top - parseInt(settings.offset);
    }
    if ($scrollPane.scrollTop() == scrollY) {
      if (typeof callback == 'function' && (!$callback_for || jQuery(this).is($callback_for))) {
        callback.call(this, scrollY, false);
      }
      return;
    }
    $animationPane.animate({scrollTop: scrollY}, settings.speed, settings.easing, function() {
      if (typeof callback == 'function' && (!$callback_for || jQuery(this).is($callback_for))) {
        callback.call(this, scrollY, true);
      }
    });
  });
}

jQuery.fn.random = function() {
  return this.eq(Math.floor(Math.random() * this.length));
}

jQuery.fn.removeClassWithPrefix = function(prefix) {
  return this.each(function(n, elem) {
    var all_classes = elem.className.split(/\s+/);
    for (c in all_classes) {
    	var class_name = all_classes[c];
      if (class_name.indexOf(prefix) == 0) {
      	jQuery(this).removeClass(class_name);
      }
    }
  });
}

/***** native overrides *****/

Object.defineProperty(Array.prototype, 'sum', {
	value: function() {
		var sum = 0
	  for (i in this) {
	  	sum += this[i];
	 	}
	  return sum;
	}
});

Object.defineProperty(Array.prototype, 'avg', {
	value: function() {
		return this.sum() / this.length;
	}
});

String.prototype.trimLeft = function(charlist) {
  if (charlist === undefined) {
    charlist = "\s";
  }
  return this.replace(new RegExp("^[" + charlist + "]+"), "");
};

String.prototype.trimRight = function(charlist) {
  if (charlist === undefined) {
    charlist = "\s";
  }
  return this.replace(new RegExp("[" + charlist + "]+$"), "");
};

String.prototype.trim = function(charlist) {
  return this.trimLeft(charlist).trimRight(charlist);
};