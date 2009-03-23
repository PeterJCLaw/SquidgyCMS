<?php
#name = Files
#description = Allows access separate admin files
#package = Core - optional
#type = content
###

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