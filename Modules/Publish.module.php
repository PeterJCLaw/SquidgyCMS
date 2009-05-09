<?php
#name = Publish
#description = Manages which bits of content are published
#package = Core - required
#type = content
###

class AdminPublish extends Admin {
	function AdminPublish() {
		parent::__construct('Manage website content', -1, -20);
	}

	function printFormAdmin() { ?>
<table class="admin_tbl"><tr>
	<th title="Click on the page title link to edit the chunk">Edit chunk:</th>
	<th title="Tick the box to enable the chunk" class="M">Enable:</th>
	<th title="Tick the box to delete the chunk, this cannot be undone" class="R">Delete:</th>
</tr><?php
		$chunks = FileSystem::Filtered_File_List($this->data_root, '.chunk');
		$published_chunks = FileSystem::file_rtrim($this->data_file);
		natsort($chunks);
		$check	= '<input type="checkbox" class="tick" name="';
		foreach($chunks as $chunk) {
			$del_box	= $enable_box	= $view_link	= '&nbsp;';
			$title	= get_GEN_title($chunk);
			$link	= '<a href="?p='.$chunk.'#Content" title="Edit the \''.$title.'\' chunk">'.$title.'</a>';

			if($chunk != '1-Home') {
				$del_box	= $check.'del['.$chunk.'.chunk]" title="delete this chunk, cannot be undone"/>';
				if(in_array($chunk, $published_chunks))
					$on = ' checked="checked"';
				else
					$on = '';
				$enable_box = $check.'publish['.$chunk.']"'.$on.' />';
			} else
				$enable_box = $check.'" disabled="disabled" checked="checked" />';

			echo '
<tr>
	<td class="L">'.$link.'</td>
	<td class="M">'.$enable_box.'</td>
	<td class="R">'.$del_box.'</td>
</tr>';
		} ?>
</table>
<?php return;
	}

	function submit() {
		global $debug_info, $del, $publish, $links, $FSCMS_pages, $pages_file, $GEN_pages;
		$error	= "";

		if(!empty($del)) {
			foreach($del as $file => $val) {
				$err	= $this->delete_file("$this->data_root/$file");	//checks the readability then deletes the file, or returns an error
				$error	.= (!empty($err) ? "$err Links to ($file) have not been changed" : $this->change_something_in_all_pages($file, 'File Deleted'));	//change the links to the file, if it was deleted
			}
		}

		if(!empty($publish)) {
			$published_chunks	= array();
			foreach($publish as $chunk => $val) {
				if(!empty($val))
					array_push($published_chunks, $chunk);
			}
			$error	.= FileSystem::file_put_stuff($this->data_file, implode("\n", $published_chunks), 'w');
		}

		return $error;
	}//*/
}
?>