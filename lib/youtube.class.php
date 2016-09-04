<?php
class Dog_Youtube {

	protected $base_url = 'https://www.googleapis.com/youtube/v3/';
	protected $url_fragment;
	protected $error;
	private   $api_key;

	public function __construct($api_key) {
		$this->api_key = $api_key;
	}

	protected function get_url($params = array()) {
		$params = array_merge(array('key' => $this->api_key), $params);
		return $this->base_url . $this->url_fragment . '?' . http_build_query($params);
	}

	protected function request($params = array()) {
		$json = file_get_contents($this->get_url($params));
		$response = json_decode($json);
		if (!$response) {
			$this->error = json_last_error_msg();
			return false;
		}
		if ($response->error) {
			$this->error = $response->error->message;
			return false;
		}
		return $response;
	}

	public function get_error() {
		return $this->error;
	}

}

class Dog_Youtube_Playlistitems extends Dog_Youtube {

	protected $url_fragment = 'playlistItems';
	private   $playlist_id;

	public function __construct($api_key, $playlist_id = null) {
		$this->playlist_id = $playlist_id;
		parent::__construct($api_key);
	}

	public function get_all($params) {
		return $this->request(array_merge(array(
			'playlistId' => $this->playlist_id
		), $params));
	}

}