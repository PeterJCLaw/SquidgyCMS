<?php
#name = File
#description = Handles the display of file trees and of file exploring pages
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
?>