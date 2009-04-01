<?php
#name = Docs
#description = Display and manage site documents
#package = Core - optional
#type = content
###

class BlockFile extends Block {
	function BlockFile() {
		parent::__construct();
	}

	function Explore($args) {
		if(empty($args)) {
			$type	= 'file';
			$size	= 3;
		} else
			list($type, $size)	= $args;

		return print_files($type, $size, TRUE);
	}

	function Tree($args) {
		global $ajax;
		if(empty($args))
			$path	= './';
		else
			list($path)	= $args;

		return file_explore($path, '', $ajax);
	}
	
	function ajax() {
		if(stristr($_GET['folder'], "FE_") === FALSE)
			return "<li>Folder does not exist!</li>";
		else
			return $this->Tree(array(substr($_GET['folder'], 3)));
	}
}

class AdminFiles extends Admin {
	function AdminFiles() {
		$this->no_submit	= TRUE;
		parent::__construct('View restricted committee files');
	}
	
	function printFormAdmin() {
		global $Admin_Files_link;
		if(!empty($Admin_Files_link)) { ?>
			View restricted files by clicking <a href="<? echo $Admin_Files_link; ?>" title="View Restricted Files">here</a>.
		<?php } else { ?>
			There are no rerestricted files to view.
			<br />
			If you think that there should be please contact the <a href="Committee.php#Webmaster" title="Who?">Web Master</a>.
		<?php }
		return;
	}
}
?>