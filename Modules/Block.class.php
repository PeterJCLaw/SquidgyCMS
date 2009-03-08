<?php
class Block {

	function __construct() {
		global $data_root, $site_root;
		$this->site_root	= $site_root;
		$this->data_root	= $data_root;
	}

	function Block() {
		return $this->__construct();
	}

	function get_my_class() {
		return get_class($this);
	}

	function get_info() {
		$a['class']	= $this->get_my_class();
		return $a;
	}
}
?>