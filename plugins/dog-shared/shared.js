function dog__shared_lib() {

  var self = this;

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
  	return dog__sh.theme_url + (path ? '/' + path.trimLeft('/') : '');
  }

  this.image_url = function (name) {
  	return this.theme_url('images/' + name);
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

  /***** ajax *****/

  this.get_nonce_name = function () {
    return dog__sh.nc_name;
  }

  this.get_nonce = function (key) {
    var nonce_key = this.string_to_key(key, dog__sh.nc_var_prefix);
    return dog__sh[nonce_key];
  }

  this.prepare_ajax_data = function (data) {
    var nonce_name = this.get_nonce_name();
    var nonce = data[nonce_name] ? data[nonce_name] : this.get_nonce(data.method);
    if (nonce) {
      data[nonce_name] = nonce;
    }
    var ajax_data = {
      action: dog__sh.ajax_callback
    };
    jQuery.extend(ajax_data, data);
    return ajax_data;
  }

  this.ajax_request = function (data, options, callbacks) {
    var ajax_options = {
      url: dog__sh.ajax_url,
      method: 'POST',
      data: this.prepare_ajax_data(data),
      beforeSend: function(jqXHR, settings) {
        console.log(settings);
        if (callbacks.before) {
          return callbacks.before(settings);
        }
      }
    };
    jQuery.extend(ajax_options, options);
    jQuery.ajax(ajax_options).done(function(response, textStatus, jqXHR) {
      console.log(response);
      self.process_ajax_response(response, ajax_options, callbacks);
    }).fail(function(jqXHR, textStatus, errorThrown) {
      if (callbacks.fail) {
        return callbacks.fail(textStatus, errorThrown);
      }
    }).always(function(data_jqXHR, textStatus, jqXHR_errorThrown) {
      if (callbacks.always) {
        return callbacks.always(data_jqXHR, textStatus, jqXHR_errorThrown);
      }
    });
  }

  this.validate_response_nonce = function (response, match) {
    var response_nonce = response[this.get_nonce_name()];
    return response_nonce && response_nonce == match;
  }

  this.is_response_error = function (response) {
    return response.status == dog__sh.ajax_response_status_error;
  }

  this.process_ajax_response = function(response, options, callbacks) {
    var is_error = false;
    if (!this.validate_response_nonce(response, options.data[this.get_nonce_name()])) {
      response.message = response.message ? response.message : dog__sh.alert_response_error_nonce;
      is_error = true;
    } else {
      is_error = this.is_response_error(response);
    }
    if (callbacks.done) {
      callbacks.done(response, is_error);
    }
  }

  this.form_to_object = function (form){
    var data = {};
    jQuery.each(jQuery(form).serializeArray(), function(n, pair) {
      data[pair.name] = pair.value;
    });
    return data;
  }

  this.ajax_submit = function (obj, data, options, callbacks) {
    var form = jQuery(obj).is('form') ? obj : jQuery(obj).parents('form').first();
    var form_data = this.form_to_object(form);
    data = jQuery.extend(form_data, data);
    this.ajax_request(data, options, callbacks);
  }

  this.form_validate_not_empty = function (data) {
    var ignore = [dog__sh.nc_name, dog__sh.hp_jar_name, dog__sh.hp_time_name, '_wp_http_referer'];
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

  /***** notices *****/

  this.show_admin_error = function(message, timeout) {
    this.show_admin_message(message, 'error', timeout);
  }

  this.show_admin_message = function (message, type, timeout) {
    type = type ? type : 'updated';
    timeout = timeout ? timeout : 0;
    var $notice = jQuery('<div></div>').insertAfter('.wrap > h1');
    $notice.addClass('dog-admin--notice');
    $notice.addClass(type);
    var $p = jQuery('<p></p>').appendTo($notice);
    $p.text(message);
    if (timeout) {
      setTimeout(function(){
        $notice.remove();
      }, timeout);
    }
  }

  this.hide_admin_errors = function (selector) {
    var $notices = jQuery('.dog-admin--notice');
    if (selector) {
      $notices = $notices.filter(selector);
    }
    $notices.remove();
  }

}

$s = new dog__shared_lib();

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