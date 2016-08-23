jQuery(document).ready(function(){
	if (!dog__media_features || !dog__media_features.categories) {
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
		$button = jQuery('<button></button>').appendTo('.media-toolbar-secondary');
		$button.attr({
			type: 'button',
			class: 'button media-button button-primary button-large apply-selected-button'
		}).text(dog__media_features.labels.apply_switch_category).click(dog__mf_switch_category);
	}
	for (var i in dog__media_features.categories) {
		var $option = jQuery('<option></option>').appendTo($select);
		$option.attr('value', dog__media_features.switch_category_action_prefix + i);
		if (!parseInt(i)) {
			$option.text(dog__media_features.categories[i]);
		} else {
			$option.text(dog__media_features.labels.switch_category.replace('${cat}', dog__media_features.categories[i]));
		}
	}
});

function dog__mf_switch_category() {
	var $items = jQuery('.attachments > li.selected');
	if (!$items.size()) {
		return alert(dog__media_features.labels.no_item_selected);
	}
	var selected = jQuery.map($items, function(elem){
		return jQuery(elem).attr('data-id');
	});
	console.log(selected);
}