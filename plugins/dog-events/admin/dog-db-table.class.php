<?php

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Dog_Events_Table extends WP_List_Table {

	const VIEW_ALL = 'all';
	const VIEW_PUBLISHED = 'published';
	const VIEW_UNPUBLISHED = 'unpublished';
	const VIEW_BREAKS = 'breaks';
	const VIEW_TRASH = 'trash';
	private $tbl;

	public function __construct() {
		parent::__construct(array(
			'singular' => dog__txt('Eveniment'),
			'plural'   => dog__txt('Evenimente'),
			'ajax'     => false,
		));
		$this->tbl = Dog_Events::tbl_name();
	}

	function get_columns() {
 		return array(
 			'cb'    			=> '<input type="checkbox" />',
    		'title' 			=> dog__txt('Titlu'),
    		'recurrent_unit'    => dog__txt('Recurență'),
    		'next'    			=> dog__txt('Data'),
    		'active'    		=> dog__txt('Publicat'),
  		);
	}

	function get_sortable_columns() {
	  	return array(
		    'title'  			=> array('title', false),
		    'recurrent_unit' 	=> array('recurrent_unit', false),
		    'next'   			=> array('next', false),
	  	);
	}

	function get_hidden_columns() {
	  	return array();
	}

	function prepare_items() {
		$this->process_bulk_action();
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$per_page = $this->get_items_per_page('dog_events_per_page', 20);
  		$current_page = $this->get_pagenum();
  		$total_items = $this->record_count();
  		$this->set_pagination_args(array(
    		'total_items' => $total_items,
    		'per_page'    => $per_page,
  		));
		$this->items = $this->get_events($per_page, $current_page);
	}

	public function column_default($item, $column_name) {
  		return $item->$column_name;
	}

	public function column_title($item) {
		$nonce = wp_create_nonce('bulk-' . $this->_args['plural']);
		$return_url = urlencode($_SERVER['REQUEST_URI']);
  		$actions = array(
            'edit'		=> sprintf('<a href="' . Dog_Events::admin_url('item', 'event=%s') . '">' . dog__txt('Editează') . '</a>', $item->id),
            'delete'	=> sprintf('<a href="' . Dog_Events::admin_url('list', 'action=trash&event=%s&_wpnonce=%s&_wp_http_referer=%s') . '">' . dog__txt('Aruncă la gunoi') . '</a>', $item->id, $nonce, $return_url),
        );
  		return sprintf('%1$s %2$s', $item->title, $this->row_actions($actions));
	}

	public function column_recurrent_unit($item) {
  		if ($item->recurrent_unit) {
  			$out = dog__txt('Fiecare');
  			$plural = $item->recurrent_step > 1;
  			$out .= $plural ? " {$item->recurrent_step} " : " ";
  			switch ($item->recurrent_unit) {
  				case Dog_Events::RECURRENT_UNIT_DAY:
  					$out .= $plural ? dog__txt('zile') : dog__txt('zi');
  					break;
  				case Dog_Events::RECURRENT_UNIT_WEEK:
  					$out .= $plural ? dog__txt('săptămâni') : dog__txt('săptămână');
  					break;
  				case Dog_Events::RECURRENT_UNIT_MONTH:
  					$out .= $plural ? dog__txt('luni') : dog__txt('lună');
  					break;
  			}
			return $out;
  		} else {
  			return dog__txt('Singular');
  		}
	}

	function column_cb($item) {
  		return sprintf('<input type="checkbox" name="event[]" value="%s" />', $item->id);
	}

	public function column_active($item) {
		return '<span class="dog-event-status active-' . $item->active . '">' . ($item->active ? '&#10003;' : '&times;') . '</span>';
	}

	public function get_events($per_page = 5, $page_number = 1) {
  		global $wpdb;
  		$sql = "SELECT * FROM {$this->tbl} WHERE deleted = 0";
		if (!empty($_REQUEST['orderby'])) {
			$sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
			$sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
		} else {
			$sql .= ' ORDER BY id DESC';
		}
  		$sql .= " LIMIT $per_page";
  		$sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
  		$result = $wpdb->get_results($sql, 'OBJECT');
  		return $result;
	}

	public function trash_event($id) {
  		global $wpdb;
  		$wpdb->update(
			$this->tbl,
			array('deleted' => 1),
			array('id' => $id),
			array('%d'),
			array('%d')
		);
	}

	public function delete_event($id) {
  		global $wpdb;
		$wpdb->delete(
			$this->tbl,
		    array('id' => $id),
		    array('%d')
		);
	}

	public function record_count($view = null) {
		global $wpdb;
	  	$sql = "SELECT COUNT(*) FROM {$this->tbl}";
	  	switch ($view) {
	  		case self::VIEW_PUBLISHED:
	  			$sql .= ' WHERE active = 1 AND deleted = 0';
	  			break;
	  		case self::VIEW_UNPUBLISHED:
	  			$sql .= ' WHERE active = 0 AND deleted = 0';
	  			break;
	  		case self::VIEW_TRASH:
	  			$sql .= ' WHERE deleted = 1';
	  			break;
	  		default:
	  			$sql .= ' WHERE deleted = 0';
	  			break;
	  	}
	  	return $wpdb->get_var($sql);
	}

	public function no_items() {
		echo dog__txt('Niciun eveniment găsit');
	}

	public function get_bulk_actions() {
  		return array(
    		'trash' => dog__txt('Aruncă la gunoi'),
    		'publish' => dog__txt('Publică'),
    		'unpublish' => dog__txt('Dezactivează'),
 		);
	}

	public function process_bulk_action() {
		$return_url = esc_url_raw(urldecode($_REQUEST['_wp_http_referer']));
		switch ($this->current_action()) {
  			case 'trash':
	    		$nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            	$action = 'bulk-' . $this->_args['plural'];
	    		if (!wp_verify_nonce($nonce, $action)) {
	    			dog__set_transient_flash_error(dog__txt('Sistemul a întâmpinat o eroare. Cererea nu poate fi validată'));
	    		} else {
	    			$events = $_POST['event'] ? $_POST['event'] : ($_GET['event'] ? array($_GET['event']) : null);
	    			if ($events) {
	    				foreach ($events as $id) {
				    		$this->trash_event(absint($id));
				    	}
				    	dog__set_transient_flash_message(dog__txt('Evenimentele au fost mutate în coșul de gunoi'));
	    			} else {
	    				dog__set_transient_flash_error(dog__txt('Nu ai selectat niciun eveniment'));
	    			}
	    		}
	    		wp_redirect($return_url);
      			exit;
  		}
	}

	public function get_views(){
		$views = array();
		$view = !empty($_REQUEST['view']) ? $_REQUEST['view'] : self::VIEW_ALL;
		$active_class = 'current';

		$class = $view == self::VIEW_ALL ? $active_class : '';
		$url = Dog_Events::admin_url();
		$views[self::VIEW_ALL] = "<a href='{$url}' class='{$class}'>" . dog__txt('Toate (${n})', array('n' => $this->record_count())) . "</a>";

		$class = $view == self::VIEW_PUBLISHED ? $active_class : '';
		$url = Dog_Events::admin_url(null, 'view=' . self::VIEW_PUBLISHED);
		$views[self::VIEW_PUBLISHED] = "<a href='{$url}' class='{$class}'>" . dog__txt('Publicate (${n})', array('n' => $this->record_count(self::VIEW_PUBLISHED))) . "</a>";

		$class = $view == self::VIEW_UNPUBLISHED ? $active_class : '';
		$url = Dog_Events::admin_url(null, 'view=' . self::VIEW_UNPUBLISHED);
		$views[self::VIEW_UNPUBLISHED] = "<a href='{$url}' class='{$class}'>" . dog__txt('Inactive (${n})', array('n' => $this->record_count(self::VIEW_UNPUBLISHED))) . "</a>";

		$class = $view == self::VIEW_TRASH ? $active_class : '';
		$url = Dog_Events::admin_url(null, 'view=' . self::VIEW_TRASH);
		$views[self::VIEW_TRASH] = "<a href='{$url}' class='{$class}'>" . dog__txt('Gunoi (${n})', array('n' => $this->record_count(self::VIEW_TRASH))) . "</a>";

		return $views;
	}

}