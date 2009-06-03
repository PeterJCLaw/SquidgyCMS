<?php
#name = Status
#description = Look after the security & update status of the site
#package = Core - required
#type = admin
###

class AdminStatus extends Admin {
	function AdminStatus() {
		parent::__construct();
	}

	function printFormAdmin() {
	}

	function get_status() {
		foreach($this->things_to_check as $thing) {
			$thingStatus = $thing.'Status';
			$this->$thingStatus();
		}
	}

	function UserStatus() {
		$this->DirStatus('Users', $this->site_root.'/Users');
	}

	function DataStatus() {
		$this->DirStatus('Data', $this->data_root);
	}

	function DirStatus($store, $path) {
		if(!file_exists($path) || !is_dir($path)) {
			$this->data[$store] = 'Folder does not exist';
			return;
		}
		if(!is_readable($path)) {
			$this->data[$store] = 'Folder is not readable';
			return;
		}
		if(!is_writeable($path)) {
			$this->data[$store] = 'Folder is not writable';
			return;
		}

		$this->data[$store] = 'Folder OK';
		return;
	}

	function submit($content=0) {
	}
}
?>