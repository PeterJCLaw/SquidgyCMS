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
				<th title="Click on the page title link to edit the chunk">Edit Chunk:</th>
				<th title="Tick the box to enable the chunk" class="M">Enable:</th>
				<th title="Tick the box to delete the chunk, this cannot be undone" class="R">Delete:</th>
			</tr><?php
		$Chunks = FileSystem::Filtered_File_List($this->data_root, '.chunk');
		natsort($Chunks);
		$check	= '<input type="checkbox" class="tick" name="';
		foreach($Chunks as $Chunk) {
			$del_box	= $enable_box	= $view_link	= '&nbsp;';
			$title	= get_GEN_title($Chunk);
			$link	= '<a href="?p='.$Chunk.'#Page" title="Edit the \''.$title.'\' chunk">'.$title.'</a>';

			if($Chunk != '1-Home') {
				$del_box	= $check.'del['.$Chunk.'.chunk]" title="delete this chunk, cannot be undone"/>';
				if(in_array($chunk, $enabled_chunks))
					$on = ' checked="checked"';
				else
					$on = '';
				$enable_box = $check.'enable['.$chunk.'.chunk]"'.$on.' />';
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
		global $debug_info, $del, $enable, $links, $FSCMS_pages, $pages_file, $GEN_pages;
		$error	= "";

		if(!empty($del)) {
			foreach($del as $file => $val) {
				$err	= $this->delete_file("$this->data_root/$file");	//checks the readability then deletes the file, or returns an error
				$error	.= (!empty($err) ? "$err Links to ($file) have not been changed" : $this->change_something_in_all_pages($file, 'File Deleted'));	//change the links to the file, if it was deleted
			}
		}

		if(!empty($enable)) {
			$toclist	= array();
			foreach($enable as $enable_key => $val) {
				$w	= (int) $weight[$enable_key];

				if($enable_key == 'Home') {
					$href	= './';
					$title	= 'Home';
				} elseif(in_array($enable_key, $FSCMS_pages)) {
					$href	= $enable_key;
					$title	= str_replace("_", " ", substr($enable_key, 0, -4));
					if(!is_readable($href))
						continue;
				} elseif(in_array($enable_key, $GEN_pages)) {
					$href	= "?p=$enable_key";
					$title	= get_GEN_title($enable_key);
					if(!is_readable("$this->data_root/$enable_key.page"))
						continue;
				} else {
					$title	= $enable_key;
					$href	= $links[$enable_key];
				}

				if(!empty($val))	//if its enabled get its weight & build the output
					array_push($toclist, "'$enable_key'	=> array('title' => '$title',	'href' => '$href',	'weight' => ".(empty($w) ? 0 : $w).')');

				$debug_info	.= "\$enable[$enable_key]=$enable[$enable_key],	\$val	= $val\n<br />";
			}
			$error	.= file_put_stuff($pages_file, "<?php\n\$Site_TOClist	= array(\n	".implode(",\n	", $toclist)."\n	);\n?>", 'w');
		}

		return $error;
	}//*/
}
?>