var dog_admin__timer_messages = [];
var dog_admin__section_selector = '.dog-admin--section';
var dog_admin__ajax_target_selector = '.dog-admin--ajax-target';
var dog_admin__control_selector = '.dog-admin--control';
var dog_admin__alert_message_selector = '.dog-admin--message';
var dog_admin__reload_button_selector = '.button-warning';
var dog_admin__alert_error_class = 'dog-admin--error';
var dog_admin__hidden_class = 'dog-admin--hidden';
var DOG_ADMIN__CSS_CLASS_LOADING = 'loading';

var DOG_ADMIN__AJAX_OPTION_KEY_LOAD_RESPONSE_DATA_ON_ERROR = 'load_response_data_on_error';
var DOG_ADMIN__AJAX_RESPONSE_KEY_NOSELECTION = 'noselection';

/***** UTILITY METHODS *****/

function dog_admin__disable_controls($section) {
	$section.find(dog_admin__control_selector).addClass('disabled');
}

function dog_admin__enable_controls($section) {
	$section.find(dog_admin__control_selector).removeClass('disabled');
}

function dog_admin__hide_controls($section, selector) {
	selector = selector ? selector : dog_admin__control_selector;
	$section.find(selector).addClass(dog_admin__hidden_class);
}

function dog_admin__show_controls($section, selector) {
	selector = selector ? selector : dog_admin__control_selector;
	$section.find(selector).removeClass(dog_admin__hidden_class);
}

function dog_admin__parent_section(obj) {
	return jQuery(obj).is(dog_admin__section_selector) ? jQuery(obj) : jQuery(obj).parents(dog_admin__section_selector);
}

function dog_admin__ajax_target($section) {
	return $section.find(dog_admin__ajax_target_selector);
}

function dog_admin__show_message($section, message, is_error) {
	var $alert = $section.find(dog_admin__alert_message_selector);
	$alert.find('p').html(message);
	if (is_error) {
		$alert.addClass(dog_admin__alert_error_class);
	}
	$alert.find('button.notice-dismiss').click(function(){
		dog_admin__hide_message($section);
	});
	$alert.fadeIn('fast');
	var parent_id = $section.attr('id');
	dog_admin__timer_messages[parent_id] = setTimeout(function() {
		dog_admin__hide_message($section);
	}, 6000);
}

function dog_admin__hide_message($section) {
	var parent_id = $section.attr('id');
	clearTimeout(dog_admin__timer_messages[parent_id]);
	$section.find(dog_admin__alert_message_selector).fadeOut('fast', function(){
		jQuery(this).removeClass(dog_admin__alert_error_class).find('p').html('');
	});
}

function dog_admin__init_form_errors($section) {
	$section.find('.form-element').click(function(){
		dog_admin__hide_form_errors($section);
	}).focus(function(){
		dog_admin__hide_form_errors($section);
	});
}

function dog_admin__hide_form_errors($section) {
	$section.find('.form-error').fadeOut('fast');
	$section.find('.has-error').removeClass('has-error');
}

/***** AJAX METHODS *****/

function dog_admin__empty_ajax_target(obj) {
	var $section = dog_admin__parent_section(obj);
	$section.find(dog_admin__ajax_target_selector).empty();
}

function dog_admin__refresh_section(obj, method) {
	var $section = dog_admin__parent_section(obj);
	if (method) {

	} else {
		dog_admin__empty_ajax_target($section);
	}
}

function dog_admin__prepare_request_data(data) {
	return ajaxPrepareData(data, getNonce(data.method));
}

function dog_admin__before_request($section) {
	dog_admin__hide_message($section);
	dog_admin__empty_ajax_target($section);
	dog_admin__disable_controls($section);
	dog_admin__hide_controls($section, dog_admin__reload_button_selector);
	$section.addClass(DOG_ADMIN__CSS_CLASS_LOADING);
}

function dog_admin__request(obj, data, options, callback) {
	if (options && options.confirm && !confirm(options.confirm)) {
		return;
	}
	var $section = dog_admin__parent_section(obj);
	var $target = dog_admin__ajax_target($section);
	options = jQuery.extend({
		data: dog_admin__prepare_request_data(data),
		beforeSend: function(jqXHR, settings) {
			console.log(settings);
			return dog_admin__before_request($section);
		}
	}, options);
	jQuery.ajax(options).done(function(response, textStatus, jqXHR) {
		console.log(response);
		dog_admin__process_response(response, $section, $target, options, callback);
	}).fail(function(jqXHR, textStatus, errorThrown) {
		dog_admin__show_message($section, dog__wp.DOG__ALERT_KEY_CLIENT_FAILURE, true);
		dog_admin__show_controls($section, dog_admin__reload_button_selector);
	}).always(function(data_jqXHR, textStatus, jqXHR_errorThrown) {
		dog_admin__enable_controls($section);
		$section.removeClass(DOG_ADMIN__CSS_CLASS_LOADING);
	});
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

function dog_admin__process_response(response, $section, $target, options, callback) {
	if (!validateResponseNonce(response, options.data[dog__wp.DOG__NONCE_NAME])) {
		dog_admin__show_message($section, dog__wp.DOG__ALERT_KEY_SERVER_FAILURE, true);
	} else {
		var is_error = isResponseError(response);
		if (callback) {
			callbacks(response, options, is_error);
		}
		dog_admin__show_message($section, response.message, is_error);
		$target.html(response.data);
		if (is_error) {
			dog_admin__init_form_errors($section);
			dog_admin__show_controls($section, dog_admin__reload_button_selector);
		}
	}
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

function dog_admin__get_section_form(obj) {
	var parent = dog_admin__parent_section(obj);
	return jQuery(parent).find('form')[0];
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
	ajaxInit();
});