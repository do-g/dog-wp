var dog_admin__timer_messages = [];
var DOG_ADMIN__CSS_CLASS_LOADING = 'loading';
var DOG_ADMIN__AJAX_OPTION_KEY_LOAD_RESPONSE_DATA_ON_ERROR = 'load_response_data_on_error';
var DOG_ADMIN__AJAX_RESPONSE_KEY_NOSELECTION = 'noselection';

function dog_admin__setup_ajax() {
	jQuery.ajaxSetup({
		cache: false,
		url: ajaxurl,
		method: 'POST'
	});
}

function dog_admin__ajax(options, callbacks) {
	jQuery.ajax(options).done(function(data, textStatus, jqXHR) {
		if (callbacks.done) {
			callbacks.done(data, textStatus, jqXHR);
		}
	}).fail(function(jqXHR, textStatus, errorThrown) {
		if (callbacks.fail) {
			callbacks.fail(jqXHR, textStatus, errorThrown);
		}
	}).always(function(data_jqXHR, textStatus, jqXHR_errorThrown) {
		if (callbacks.allways) {
			callbacks.allways(data_jqXHR, textStatus, jqXHR_errorThrown);
		}
	});
}

function dog_admin__form_validate_not_empty(data) {
	var valid = false;
	for (var i in data) {
		if (data[i]) {
			return true;
		}
	}
	return false;
}

function dog_admin__form_to_object(form){
	var data = {};
	jQuery.each(jQuery(form).serializeArray(), function(n, pair) {
	  data[pair.name] = pair.value;
	});
	return data;
}

function dog_admin__submit(obj, method, params, extra_options, target) {
	var form = dog_admin__get_section_form(obj);
	var data = jQuery.extend(dog_admin__form_to_object(form), params);
	extra_options = extra_options ? extra_options : {};
	if (extra_options.validate_not_empty && !dog_admin__form_validate_not_empty(data)) {
		dog_admin__show_message(obj, DOG_ADMIN__AJAX_RESPONSE_KEY_NOSELECTION);
		return;
	}
	extra_options[DOG_ADMIN__AJAX_OPTION_KEY_LOAD_RESPONSE_DATA_ON_ERROR] = true;
	dog_admin__request(obj, method, data, extra_options, target);
}

function dog_admin__request(obj, method, params, extra_options, target) {
	if (extra_options && extra_options.confirm && !confirm(extra_options.confirm)) {
		return;
	}
	target = dog_admin__get_ajax_target(obj, target);
	var options = {
		data: dog_admin__prepare_request_data(method, params),
		beforeSend: function(jqXHR, settings) {
			return dog_admin__before_request(obj, method, settings, target);
		}
	};
	options = jQuery.extend(options, extra_options);
	dog_admin__ajax(options, {
		done: function(response, textStatus, jqXHR) {
			dog_admin__process_response(obj, method, response, options, target);
		},
		fail: function(jqXHR, textStatus, errorThrown) {
			dog_admin__show_message(obj, dog_admin__wp.DOG_ADMIN__AJAX_RESPONSE_KEY_AJAX, dog_admin__wp.DOG_ADMIN__AJAX_RESPONSE_CODE_AJAX);
		},
		allways: function(data_jqXHR, textStatus, jqXHR_errorThrown) {
			jQuery(target).removeClass(DOG_ADMIN__CSS_CLASS_LOADING);
			dog_admin__enable_controls(obj);
		}
	});
}

function dog_admin__prepare_request_data(method, params) {
	var nonce_key = dog_admin__wp.DOG__NONCE_VAR_PREFIX + method;
	var general = {
		action: dog_admin__wp.DOG_ADMIN__WP_ACTION_AJAX_CALLBACK,
		method: method
	};
	general[dog_admin__wp.DOG__NONCE_NAME] = dog_admin__wp[nonce_key];
	return jQuery.extend(general, params);
}

function dog_admin__before_request(obj, method, settings, target) {
	dog_admin__hide_messages(obj);
	dog_admin__empty_ajax_target(obj, false, target);
	jQuery(target).addClass(DOG_ADMIN__CSS_CLASS_LOADING);
	dog_admin__disable_controls(obj);
}

function dog_admin__process_response(obj, method, response, options, target) {
	if (!response[dog_admin__wp.DOG__NONCE_NAME] || response[dog_admin__wp.DOG__NONCE_NAME] != options.data[dog_admin__wp.DOG__NONCE_NAME]) {
		dog_admin__show_message(obj, dog_admin__wp.DOG_ADMIN__AJAX_RESPONSE_KEY_FAILURE, dog_admin__wp.DOG_ADMIN__AJAX_RESPONSE_CODE_MISMATCH_NONCE);
		method = dog_admin__wp.DOG_ADMIN__CONTROL_CLASS_AFTER_NONCE_MISMATCH;
	} else {
		dog_admin__show_message(obj, response.key, response.code);
		var is_error;
		if (is_error = dog_admin__is_response_error(response)) {
			if (options[DOG_ADMIN__AJAX_OPTION_KEY_LOAD_RESPONSE_DATA_ON_ERROR]) {
				jQuery(target).html(response.data);
				dog_admin__init_form_errors(obj);
			}
		} else {
			jQuery(target).html(response.data);
		}
	}
	dog_admin__show_controls(obj, method, is_error);

}

function dog_admin__is_response_error(response) {
	return response.status == dog_admin__wp.DOG_ADMIN__AJAX_RESPONSE_STATUS_ERROR;
}

function dog_admin__empty_ajax_target(obj, show_only, target) {
	target = dog_admin__get_ajax_target(obj, target);
	setTimeout(function(){
		jQuery(target).empty();
	}, 1);
	if (show_only) {
		dog_admin__show_controls(obj, show_only);
	}
}

function dog_admin__show_message(obj, key, code) {
	var parent = dog_admin__get_parent_section(obj);
	var notice = jQuery(parent).find('.dog-admin--message.status-' + key)[0];
	if (code) {
		jQuery(notice).html(jQuery(notice).html().replace(dog_admin__wp.DOG_ADMIN__MESSAGE_CODE_PLACEHOLDER, code));
	}
	jQuery(notice).find('button.notice-dismiss').click(function(){
		dog_admin__hide_messages(obj, [key]);
	});
	jQuery(notice).fadeIn('fast');
	var parent_id = jQuery(parent).attr('id');
	dog_admin__timer_messages[parent_id] = dog_admin__timer_messages[parent_id] ? dog_admin__timer_messages[parent_id] : [];
	dog_admin__timer_messages[parent_id][key] = setTimeout(function() {
		dog_admin__hide_messages(obj, [key]);
	}, 6000);
}

function dog_admin__hide_messages(obj, keys) {
	var parent = dog_admin__get_parent_section(obj);
	var parent_id = jQuery(parent).attr('id');
	if (dog_admin__timer_messages[parent_id]) {
		for (var k in dog_admin__timer_messages[parent_id]) {
			if (!keys || jQuery.inArray(k, keys) !== -1) {
				clearTimeout(dog_admin__timer_messages[parent_id][k]);
				jQuery(parent).find('.dog-admin--timeout.status-' + k).fadeOut('fast');
			}
		}
	}
}

function dog_admin__get_section_form(obj) {
	var parent = dog_admin__get_parent_section(obj);
	return jQuery(parent).find('form')[0];
}

function dog_admin__get_parent_section(obj) {
	if (obj) {
		while (!jQuery(obj).hasClass('dog-admin--section')) {
			obj = jQuery(obj).parent()[0];
		}
	} else {
		obj = 'body';
	}
	return obj;
}

function dog_admin__get_ajax_target(obj, selector) {
	if (typeof selector == 'object') {
		return selector;
	}
	selector = selector ? selector : '.dog-admin--ajax-target';
	var parent = dog_admin__get_parent_section(obj);
	return jQuery(parent).find(selector)[0];
}

function dog_admin__show_controls(obj, method, is_error) {
	var error = is_error ? '-error' : '';
	var parent = dog_admin__get_parent_section(obj);
	jQuery(parent).find('.dog-admin--control').hide();
	jQuery(parent).find('.dog-admin--control.after-all, .dog-admin--control.after-' + method + error).show();
}

function dog_admin__disable_controls(obj) {
	var parent = dog_admin__get_parent_section(obj);
	jQuery(parent).find('.dog-admin--control').addClass('disabled');
}

function dog_admin__enable_controls(obj) {
	var parent = dog_admin__get_parent_section(obj);
	jQuery(parent).find('.dog-admin--control').removeClass('disabled');
}

function dog_admin__init_form_errors(obj) {
	var parent = dog_admin__get_parent_section(obj);
	jQuery(parent).find('.form-element').click(function(){
		dog_admin__hide_form_errors(obj);
	}).focus(function(){
		dog_admin__hide_form_errors(obj);
	});
}

function dog_admin__hide_form_errors(obj) {
	var parent = dog_admin__get_parent_section(obj);
	jQuery(parent).find('.form-error').fadeOut('fast');
	jQuery(parent).find('.has-error').removeClass('has-error');
}

jQuery(document).ready(function(){
	jQuery('.dog-admin--section h3').click(function(){
		var parent = jQuery(this).parent().parent()[0];
		jQuery(parent).find('.dog-admin--box-content').slideToggle('fast', function(){
			jQuery(parent).toggleClass('expanded');
			var key = 'dog_admin__options_box__' + jQuery(parent).attr('id');
			jQuery.cookie(key, jQuery(parent).hasClass('expanded') ? 'expanded' : 'collapsed');
		});
	});
	jQuery('.dog-admin--section').each(function(){
		var key = 'dog_admin__options_box__' + jQuery(this).attr('id');
		var cls = jQuery.cookie(key) ? jQuery.cookie(key) : 'expanded';
		jQuery(this).addClass(cls);
	});
	dog_admin__setup_ajax();
});