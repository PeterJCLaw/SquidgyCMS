<?php
#name = Publish
#description = Manages which bits of content are published
#package = Core - required
#type = content
###

class AdminPublish extends Admin {
	function AdminPublish() {
		parent::__construct();
		$this->complex_data = true;
		$this->data_key_column = 'id';
	}

	function printFormAdmin() {
		$home_found = FALSE;
		$output = <<<OUT
<table class="admin_tbl"><tr>
	<th title="Click on the page title link to edit the chunk">Edit chunk:</th>
	<th title="A short name for the chunk" class="M">Alias:</th>
	<th title="Tick the box to enable the chunk" class="M">Enable:</th>
	<th title="Tick the box to delete the chunk, this cannot be undone" class="R">Delete:</th>
</tr>
OUT;
		$this->get_data();
		$chunks = FileSystem::Filtered_File_List($this->data_root, '.chunk');
		natsort($chunks);
		$check	= '<input type="checkbox" class="tick" name="';
		foreach($chunks as $chunk_id) {
			$del_box	= $enable_box	= $alias_box	= $view_link	= '&nbsp;';
			$title	= get_GEN_title($chunk_id);
			$alias_box = '<input name="alias['.$chunk_id.']" value="'.$this->data[$chunk_id]['alias'].'" />';
			$link	= '<a href="'.Content::edit_URL($chunk_id).'" title="Edit the \''.$title.'\' chunk">'.$title.'</a>';

			$del_box	= $check.'del['.$chunk_id.']" title="delete this chunk, cannot be undone"/>';
			if($this->data[$chunk_id]['enable'])
				$on = ' checked="checked"';
			else
				$on = '';
			$enable_box = $check.'enable['.$chunk_id.']"'.$on.' />';

			if($this->data[$chunk_id]['alias'] == '<home>')
				$home_found = true;

			$output .= '
<tr>
	<td class="L">'.$link.'<input type="hidden" name="publish['.$chunk_id.']" value="1" /></td>
	<td class="M">'.$alias_box.'</td>
	<td class="M">'.$enable_box.'</td>
	<td class="R">'.$del_box.'</td>
</tr>';
		}
		if(!$home_found)
			echo '<p id="error" style="margin: 3px; padding: 7px; background-color: #FFB6C1;">No homepage was detected! Please set the alias of one chunk to <em>&lt;home&gt;</em>.</p>';
		echo $output;
?>
</table>
<?php return;
	}

	function submit($content=0) {
		list($del, $publish, $enable, $alias) = array();
		extract($_POST, EXTR_IF_EXISTS);
		$error	= "";

		if(!empty($del)) {
			$this->get_data();
			foreach($del as $chunk => $val) {
				$err	= $this->delete_file("$this->data_root/$chunk.chunk");	//checks the readability then deletes the file, or returns an error
				unset($this->data[$chunk]);
				$this->put_data();
				$error	.= (!empty($err) ? "$err Links to ($chunk) have not been changed" : $this->change_something_everywhere($chunk, 'File Deleted'));	//change the links to the file, if it was deleted
			}
		}

		if(!empty($publish)) {
			$this->data = array();
			foreach($publish as $chunk_id => $v) {
				array_push($this->data, array('id' => $chunk_id, 'enable' => empty($enable[$chunk_id]) ? 0 : 1, 'alias' => $alias[$chunk_id]));
			}
			$error	.= $this->put_data();
		}

		return $error;
	}

	function change_something_everywhere($old, $new) {
		$chunks = array();
		foreach($this->data as $chunk)
			array_push($chunks, $this->data_root.'/'.$chunk['id'].'.chunk');

		array_push($chunks, $this->data_file);

		$error	= "";
		foreach($chunks as $chunk) {	//check if we can modify all the chunks
			if(!is_writable($chunk))
				return "\nUnable to change file id as file ($chunk) is not writeable - please inform the Webmaster\n<br />\n";
		}
		foreach($chunks as $chunk) {	//go through all the pages, replacing the old id with the new one, if its present
			$content	= file_get_contents($chunk);
			if(strpos($content, $old))
				$error	.= FileSystem::file_put_contents($chunk, str_replace($old, $new, $content));
		}
		return $error;
	}

}

class Publish {
	function get_alias_from_id($id) {
		$info = FileSystem::get_file_assoc($GLOBALS['data_root'].'/publish.data', 'id');
		return $info[$id]['alias'];
	}
}
