/***** preloader *****/

var preloader = {
	queue: {},
	images_registered: false,
	images_loaded: 0,
	app_loaded: false,
	timeout: null,
	delay: 10000,
	reset: function() {
		this.queue = {};
	},
	register: function(key) {
		this.queue[key] = false;
	},
	complete: function(key) {
	  if (this.queue.hasOwnProperty(key)) {
			this.queue[key] = true;
			this.check();
	  }
	},
	completeAll: function() {
		for (key in this.queue) {
			if (!this.queue[key]) {
				console.log('failed to load key: ' + key);
			}
			this.queue[key] = true;
	  }
		this.check();
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
	  this.update(percent);
	},
	update: function(percent) {
	  percent = percent && percent > 1 ? percent : 1;
	  $('.preloader .bar').css('width', percent + '%');
	  if (percent == 100) {
	    $(document).trigger('dog.preloader_complete');
	  }
	},
  hide: function() {
    $('body').removeClass('preloading');
    if (!this.app_loaded) {
      this.app_loaded = true;
    }
  }
};

$(document).on('dog.preloader_complete', function() {
  preloader.hide();
});

var webfont_config = {
  classes: false,
  _fontLoading: _fontLoading,
  fontactive: _fontReady,
  fontinactive: _fontReady,
  active: _fontsReady,
  inactive: _fontsReady
};

function _preloaderImageToKey(number) {
  return stringToKey(number, 'image');
}

function _preloaderFontToKey(family, variation) {
  return stringToKey(family + variation, 'font');
}

function _fontLoading(family, variation) {
  preloader.register(_preloaderFontToKey(family, variation));
}

function _fontReady(family, variation) {
  preloader.complete(_preloaderFontToKey(family, variation));
}

function _fontsReady() {
  preloader.complete('fonts');
}

function preloaderStart(fontConfig, extra_items) {
	preloader.timeout = setTimeout(function(){
		preloader.completeAll();
		clearTimeout(preloader.timeout);
	}, preloader.delay);
	preloader.register('page');
	preloader.register('fonts');
	if (extra_items) {
		for (i in extra_items) {
			preloader.register(extra_items[i]);
		}
	}
	WebFont.load(fontConfig);
}

function _preloaderRegisterImages(count) {
  preloader.images_registered = true;
  for (n = 1; n <= count; n++) {
    preloader.register(_preloaderImageToKey(n));
  }
}

function preloadImages() {
  preloader.images_registered = false;
  preloader.images_loaded = 0;
  preloader.register('images');
  $('body').imagesLoaded({ background: '.has-bg-img' }, function() {
  	preloader.complete('images');
  }).progress(function(instance, image) {
  	if (!preloader.images_registered) {
    	_preloaderRegisterImages(instance.images.length);
  	}
    preloader.images_loaded++;
    preloader.complete(_preloaderImageToKey(preloader.images_loaded));
  });
}

function preloadPage() {
  if (preloader.app_loaded) {
  	preloader.reset();
    preloader.register('page');
  }
  preloadImages();
}

/***** usefull functions *****/

function stringToKey(value, prefix, suffix) {
  value += '';
  value = value.toLowerCase();
  value = value.replace(/[-]+/g, '_');
  value = value.replace(/\s+/g, '_');
  value = value.replace(/\W+/g, '');
  return (prefix ? prefix : '') + value + (suffix ? suffix : '');
}

function hideFormErrors(error_selector) {
  error_selector = error_selector ? error_selector : '.form-error';
	$(error_selector).hide();
}

function isScreen(breakpoint) {
	return $('.device-' + breakpoint).is(':visible')
}

function isPage(selector) {
	return $('body').is(selector);
}

function pageScrollTo(target, options, callback) {
  $(window).scrollTo(target, options, callback);
}

function themeUrl(path) {
	return dog__wp.theme_url + (path ? '/' + path.trimLeft('/') : '');
}

function imageUrl(name) {
	return themeUrl('images/' + name);
}

function nonceVarKey(name) {
  return stringToKey(name, dog__wp.DOG__NONCE_VAR_PREFIX);
}

function getNonce(key) {
  return dog__wp[nonceVarKey(key)];
}

function ajaxInit() {
  $.ajaxSetup({
    url: dog__wp.ajax_url,
    method: 'POST'
  });
}

function ajaxDefaults() {
  return {
    action: dog__wp.DOG__WP_ACTION_AJAX_CALLBACK
  };
}

function ajaxPrepareData(data) {
  return $.extend(data, ajaxDefaults());
}

/***** internal functions *****/

function _toggleParentClass(event) {
  $(event.delegateTarget).toggleParentClass(event.data.css_class, event.data.parent_selector);
}

/***** jQuery overrides *****/

$.fn.initToggleParentClass = function(events, options) {
  if (events) {
    var settings = $.extend({
      css_class: 'active',
      parent_selector: null
    }, options);
    this.on(events, settings, _toggleParentClass);
  }
  return this;
}

$.fn.toggleParentClass = function(css_class, parent_selector) {
  $parent = parent_selector ? this.parents(parent_selector) : this.parent();
  $parent.toggleClass(css_class);
  return this;
}

$.fn.activateItem = function(index, selector, active_class) {
	index = index ? index : 0;
	active_class = active_class ? active_class : 'active';
	var $items = selector ? this.filter(selector) : this;
  $items.removeClass(active_class);
  var $next = $items.eq(index);
  $next.addClass(active_class);
  return $next;
}

$.fn.activateNextItem = function(selector, active_class) {
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

$.fn.nextSibling = function(selector, reverse) {
  var $next = reverse ? this.prev(selector) : this.next(selector);
  if (!$next.size()) {
    var $siblings = this.siblings(selector);
    $next = $siblings.size() ? (reverse ? $siblings.last() : $siblings.first()) : this;
  }
  return $next;
}

$.fn.prevSibling = function(selector) {
  return this.nextSibling(selector, true);
}

$.fn.scrollTo = function(target, options, callback){
  var settings = $.extend({
    target: target,
    easing: 'swing'
  }, options);
  settings.speed = settings.speed ? settings.speed : 600;
  settings.offset = settings.offset ? settings.offset : 0;
  this.each(function() {
    var $scrollPane = $(this);
    var $animationPane = $scrollPane;
    var callback_for = null;
    var parent_top = $scrollPane.scrollTop();
    if (this == window) {
      $animationPane = $('body, html');
      callback_for = 'html';
      parent_top = 0;
    }
    var scrollTarget = (typeof settings.target == 'number') ? settings.target : $(settings.target);
    var scrollY = (typeof scrollTarget == 'number') ? scrollTarget : scrollTarget.offset().top + parent_top - parseInt(settings.offset);
    if ($scrollPane.scrollTop() == scrollY) {
      if (typeof callback == 'function' && (!settings.single_callback || $scrollPane.is('html'))) {
        callback.call(this, scrollY, false);
      }
      return;
    }
    $animationPane.animate({scrollTop: scrollY}, settings.speed, settings.easing, function() {
      if (typeof callback == 'function' && (!$callback_for || $(this).is($callback_for))) {
        callback.call(this, scrollY, true);
      }
    });
  });
}

$.fn.random = function() {
  return this.eq(Math.floor(Math.random() * this.length));
}

$.fn.removeClassWithPrefix = function(prefix) {
  return this.each(function(n, elem) {
    var all_classes = elem.className.split(/\s+/);
    for (c in all_classes) {
    	var class_name = all_classes[c];
      if (class_name.indexOf(prefix) == 0) {
      	$(this).removeClass(class_name);
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