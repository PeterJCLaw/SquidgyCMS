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
		echo '<ul>';
		foreach($this->data as $thing => $value) {
			if (is_array($value))
			{
				$class = $value['ok?'] ? 'good' : 'bad';
				$comment = $value['comment'];
			}
			else
			{
				$class = 'info';
				$comment = $value;
			}
			$class = ' class="'.$class.'"';
			echo "<li$class><span class=\"t\">$thing</span><span class=\"d\">$comment</span></li>";
		}
		echo '</ul>';
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
		// init the array, assume failure
		$this->data[$store] = array(
				'ok?' => False,
				'comment' => 'Folder (<em class="path">'.$path.'</em>) '
			);

		if(!file_exists($path) || !is_dir($path)) {
			$this->data[$store]['comment'] .= 'does not exist!';
			return;
		}
		if(!is_readable($path)) {
			$this->data[$store]['comment'] .= 'is not readable!';
			return;
		}
		if(!is_writeable($path)) {
			$this->data[$store]['comment'] .= 'is not writable!';
			return;
		}

		$this->data[$store]['ok?'] = True;
		$this->data[$store]['comment'] = 'Folder OK';
		return;
	}

	function PHPStatus() {
		$this->data['PHP Version'] = phpversion();
	}

	function SquidgyCMSStatus() {
		$this->data['SquidgyCMS Version'] = $GLOBALS['SquidgyCMS_version'];
	}

	function WebServerStatus() {
		$this->data['Web server'] = $_SERVER['SERVER_SOFTWARE'];
	}
}

