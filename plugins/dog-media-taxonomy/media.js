jQuery(document).ready(function(){
	if (!dog__media_taxonomy || !dog__media_taxonomy.categories) {
		console.log('no media taxonomies found');
		return;
	}
	var $select = jQuery('.actions.bulkactions select');
	for (var i in dog__media_taxonomy.categories) {
		var $option = jQuery('<option></option>').appendTo($select);
		$option.attr('value', dog__media_taxonomy.action_prefix + i);
		if (!parseInt(i)) {
			$option.text(dog__media_taxonomy.categories[i]);
		} else {
			$option.text(dog__media_taxonomy.label.replace('${cat}', dog__media_taxonomy.categories[i]));
		}
	}
});