jQuery(document).ready(function(){
	if (!dog__mf || !dog__mf.categories) {
		console.log('no media taxonomies found');
		return;
	}
	var $select = jQuery('.actions.bulkactions select');
	if (!$select.size()) {
		$select = jQuery('<select></select>').appendTo('.media-toolbar-secondary');
		$select.attr({
			name: 'bulk_action',
			id: 'dog-mf-grid-bulk-action',
			class: 'attachment-filters bulk-action switch-category'
		});
		var $o = jQuery('<option></option>').appendTo($select);
		$o.attr('value', -1);
		$o.text(dog__mf.labels.bulk_actions);
		$button = jQuery('<button></button>').appendTo('.media-toolbar-secondary');
		$button.attr({
			type: 'button',
			class: 'button media-button button-primary button-large apply-selected-button'
		}).text(dog__mf.labels.apply_switch_category).click(dog__mf_switch_category);
	}
	var cid;
	for (var i in dog__mf.categories) {
		cid = i.replace(dog__mf.switch_category_action_prefix, '');
		var $option = jQuery('<option></option>').appendTo($select);
		$option.attr('value', i);
		if (!parseInt(cid)) {
			$option.text(dog__mf.categories[i]);
		} else {
			$option.text(dog__mf.labels.switch_category.replace('${cat}', dog__mf.categories[i]));
		}
	}
});

function dog__mf_switch_category() {
	$s.hide_admin_errors();
	var action = jQuery('#dog-mf-grid-bulk-action').val();
	if (action == -1) {
		return $s.show_admin_error(dog__mf.labels.no_action_selected);
	}
	var $items = jQuery('.attachments > li.selected');
	if (!$items.size()) {
		return $s.show_admin_error(dog__mf.labels.no_item_selected);
	}
	var selected = jQuery.map($items, function(elem){
		return jQuery(elem).attr('data-id');
	});
	$s.ajax_request({
		media: selected,
		custom_action: action,
		method: 'Dog_Media_Features::update_categories'
	}, null, {
		done: function (response, is_error) {
			if (is_error) {
				$s.show_admin_error(response.message);
			} else {
				$s.show_admin_message(dog__mf.labels.update_complete);
			}
			jQuery('.select-mode-toggle-button').click();
		},
		fail: function (jqXHR, textStatus, errorThrown) {
			$s.show_admin_error(dog__sh.labels.alert_request_error);
		}
	});
}