<?php
#name = Status
#description = Look after the security & update status of the site
#package = Core - required
#type = admin
###

class AdminStatus extends Admin {
	function AdminStatus() {
		$this->no_submit = True;
		parent::__construct();
	}

	function printFormAdmin() {
		$this->things_to_check = array('User', 'Data', 'PHP', 'SquidgyCMS', 'WebServer');
		sort($this->things_to_check);
		$this->get_status();
		echo '<dl>';
		foreach($this->data as $thing => $value) {
			echo "<dt>$thing</dt><dd>$value</dd>";
		}
		echo '</dl>';
	}

	function get_status() {
		foreach($this->things_to_check as $thing) {
			$thingStatus = $thing.'Status';
			$this->$thingStatus();
		}
	}

	function UserStatus() {
		$this->DirStatus('Users Folder', $this->site_root.'/Users');
	}

	function DataStatus() {
		$this->DirStatus('Data Folder', $this->data_root);
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

	function PHPStatus() {
		$this->data['PHP Version'] = phpversion();
	}

	function SquidgyCMSStatus() {
		$this->data['SquidgyCMS Version'] = $GLOBALS['version'];
	}

	function WebServerStatus() {
		$this->data['Web server'] = $_SERVER['SERVER_SOFTWARE'];
	}
}

