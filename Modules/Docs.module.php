<?php
#name = Docs
#description = Display and manage site documents
#package = Core - optional
#type = content
###

class BlockDocs extends Block {
	function BlockDocs() {
		parent::__construct();
	}

	function Explore($args) {
		$browse = $this->Browse($agrs)
		$tree = $this->Tree($agrs)
		return <<<EXP
<div id="Docs-Explore">
$browse
$tree
</div>
EXP;
	}

	function Browse($args) {
		if(empty($args)) {
			$type	= 'file';
			$size	= 3;
		} else
			list($type, $size)	= $args;

		return '<div id="Docs-Browse">'.print_files($type, $size, TRUE).'</div>';
	}

	function Tree($args) {
		global $ajax;
		if(empty($args))
			$path	= './';
		else
			list($path)	= $args;

		return '<div id="Docs-Tree">'.file_explore($path, '', $ajax).'</div>';
	}
	
	function ajax() {
		if(stristr($_GET['folder'], "FE_") === FALSE)
			return "<li>Folder does not exist!</li>";
		else
			return $this->Tree(array(substr($_GET['folder'], 3)));
	}
}

class AdminDocs extends Admin {
	function AdminDocs() {
		parent::__construct();
	}
	
	function printFormAdmin() {
	//somehow let them choose which files are publicly viewable
	
	}
}
?>