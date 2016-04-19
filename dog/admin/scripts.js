var $d = new dog__shared_lib();

var dog_admin__timer_messages = [];
var dog_admin__section_selector = '.dog-admin--section';
var dog_admin__ajax_target_selector = '.dog-admin--ajax-target';
var dog_admin__control_selector = '.dog-admin--control';
var dog_admin__alert_message_selector = '.dog-admin--message';
var dog_admin__alert_error_class = 'dog-admin--error';
var dog_admin__hidden_class = 'dog-admin--hidden';
var DOG_ADMIN__CSS_CLASS_LOADING = 'loading';

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

function dog_admin__section_form(obj) {
	var $section = dog_admin__parent_section(obj);
	return $section.find('form');
}

function dog_admin__ajax_target($section) {
	return $section.find(dog_admin__ajax_target_selector);
}

function dog_admin__show_message($section, message, is_error) {
	if (!message) {
		return;
	}
	var $alert = $section.find(dog_admin__alert_message_selector);
	$alert.find('p').html(message);
	if (is_error) {
		$alert.addClass(dog_admin__alert_error_class);
	}
	$alert.find('button.notice-dismiss').click(function(){
		dog_admin__hide_message($section);
	});
	$alert.fadeIn('fast', function() {
		var offset = jQuery('#wpadminbar').height();
		if (!$alert.fullyInViewport(null, {top: offset})) {
			$d.page_scroll_to($alert, {offset: offset});
		}
	});
	var parent_id = $section.attr('id');
	dog_admin__timer_messages[parent_id] = setTimeout(function() {
		dog_admin__hide_message($section);
	}, 7000);
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

function dog_admin__prepare_request_data(data) {
	var set_nonce = data[$d.get_nonce_name()] ? data[$d.get_nonce_name()] : $d.get_nonce(data.method);
	return $d.ajax_prepare_data(data, set_nonce);
}

function dog_admin__before_request($section) {
	dog_admin__hide_message($section);
	dog_admin__empty_ajax_target($section);
	dog_admin__disable_controls($section);
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
	}).always(function(data_jqXHR, textStatus, jqXHR_errorThrown) {
		dog_admin__enable_controls($section);
		$section.removeClass(DOG_ADMIN__CSS_CLASS_LOADING);
	});
}

function dog_admin__submit(obj, data, options, callback) {
	var form = dog_admin__section_form(obj);
	var form_data = $d.form_to_object(form);
	data = jQuery.extend(form_data, data);
	dog_admin__request(obj, data, options, callback);
}

function dog_admin__process_response(response, $section, $target, options, callback) {
	if (!$d.validate_response_nonce(response, options.data[$d.get_nonce_name()])) {
		dog_admin__show_message($section, dog__wp.DOG__ALERT_KEY_SERVER_FAILURE, true);
	} else {
		var is_error = $d.is_response_error(response);
		if (callback) {
			callback(response, options, is_error);
		}
		dog_admin__show_message($section, response.message, is_error);
		$target.html(response.data);
		$target.find('pre').scrollTop(9999);
		if (is_error) {
			dog_admin__init_form_errors($section);
		}
	}
}

/***** SECTION METHODS *****/

function dog_admin__section_cache_output(obj, method, options) {
	var $section = dog_admin__parent_section(obj);
	var $form = dog_admin__section_form($section);
	var form_data = $d.form_to_object($form);
	if (!$d.form_validate_not_empty(form_data)) {
		dog_admin__show_message($section, dog__wp.DOG__ALERT_KEY_EMPTY_SELECTION, true);
	} else {
		form_data = jQuery.extend({method: method}, form_data);
		dog_admin__submit(obj, form_data, options);
	}
}

function dog_admin__section_update(obj, method) {
	dog_admin__request(obj, {method: method}, null, function(response, options, is_error){
		var $section = dog_admin__parent_section(obj);
		switch($d.string_to_key(method)) {
			case 'update_check':
				if (!is_error && response.updates) {
					dog_admin__show_controls($section, '.key-update');
				} else {
					dog_admin__hide_controls($section, '.key-update');
				}
				break;
			case 'update_info':
				dog_admin__hide_controls($section, '.key-update');
				break;
			case 'update':
				if (is_error) {
					dog_admin__hide_controls($section, '.key-update');
				}
				break;
		}
	});
}

/***** DOM READY METHODS *****/

jQuery(document).ready(function(){
	jQuery('.dog-admin--section').each(function(){
		var $section = jQuery(this);
		var key = 'dog_admin__options_box__' + $section.attr('id');
		var cls = jQuery.cookie(key) ? jQuery.cookie(key) : 'expanded';
		$section.addClass(cls);
		$section.find('h3').click(function(){
			jQuery(this).siblings('.dog-admin--box-content').slideToggle('fast', function(){
				$section.toggleClass('expanded');
				jQuery.cookie(key, $section.hasClass('expanded') ? 'expanded' : 'collapsed');
			});
		});
	});
	jQuery('#toplevel_page_dog-theme-options ul li a').each(function() {
		var href = jQuery(this).attr('href').split('#');
		var section_id = href[1];
		jQuery(this).attr('href', 'javascript:void(0)').click(function(){
			$d.page_scroll_to('#' + section_id, {offset: 50});
		});
	});
	$d.ajax_init();
});