<?php
#name = Content
#description = Allows site content to be created and edited
#package = Core - required
#type = content
###

class AdminContent extends Admin {
	function AdminContent() {
		parent::__construct('Create or change content chunks made from html and site blocks', -1);
	}

	function printFormAdmin() {
		global $page_req;

		$Chunks = FileSystem::Filtered_File_List($this->data_root, '.chunk');
		natsort($Chunks);

		if(!empty($page_req) && in_array($page_req, $Chunks)) {
			$content	= file_get_contents("$this->data_root/$page_req.chunk");
			$chunk_title	= get_GEN_title($page_req);
			$chunk_id	= $chunk_req;
		} else {
			$content	= $chunk_title	= "";
			$chunk_id	= "new";
		}
		?>
			<input type="hidden" name="chunk_id" id="chunk_id" value="<?php echo $chunk_id; ?>" />
			<table><tr>
				<th><label for="chunk_title" title="The title of the chunk">Chunk Title:</label></th>
				<td><input  value="<?php echo $chunk_title; ?>" class="text" name="chunk_title" id="chunk_title" title="The title of the page" /></td>
			</tr><tr>
				<?php $this->print_select_cells($Chunks, $page_req); ?>
			</tr></table>
<?php
		$this->printTextarea($content);
		return;
	}

	function submit()
	{
		global $content, $chunk_title, $chunk_id, $header_link, $debug_info;

		$error	= "";

		if(empty($content))
			$error	.= "\nNo content provided";
		if(strpos($content, '<?') !== FALSE)
			$error	.= "\nInvalid content provided: PHP is not allowed";
		if(empty($chunk_title))
			$error	.= "\nNo title provided";

		if(!empty($error))	//if there's an error then bail
			return $error;

		$old_chunk_id	= urldecode($chunk_id);

		if($chunk_id == 'new') {	//if its a new page
			$chunk_id	= urlencode(get_next_id($this->data_root, ".chunk")."-$chunk_title");
		} elseif($this->new_id_needed($old_chunk_id, $chunk_title) && is_writable("$this->data_root/$chunk_id.chunk")) {	//if we should and can change stuff
			$chunk_id	= get_next_id($this->data_root, ".chunk")."-$chunk_title";
			$error	= $this->change_something_in_all_pages($old_chunk_id, $chunk_id);
			if(!empty($error))	//if there's an error then bail
				return $error;
			$error	= $this->delete_file("$this->data_root/".urlencode($old_chunk_id).".chunk");
			$chunk_id	= urlencode($chunk_id);
		}

		$debug_info .= @"\$old_chunk_id = '$old_chunk_id', \$chunk_id = '$chunk_id'\n<br />\n";

		$header_link	= "&p=$chunk_id";

		return FileSystem::file_put_stuff("$this->data_root/".$chunk_id.".chunk", $content, 'w');
	}
}

class BlockContent extends Block {
	function BlockContent() {
		parent::__construct();
	}
	
	function replace_one($old, $new, $target) {
		$pos = strpos($target, $old);
		if($pos === FALSE)
			return $target;
		$before = substr($target, 0, $pos);
		$after = substr($target, $pos+strlen($old));
		return $before.$new.$after;
	}

	function getTemplate($n) {
		$Chunk = $this->Chunk($n);
		if($Chunk !== FALSE)
			return $Chunk;
		$themefile = "use the default theme one";
		$defaultFile = "Use the default SquidgyCMS one";
		foreach(array($themefile. $defaultFile) as $file) {
			if(is_readable($file))
				return file_get_contents($file);
		}
		return 'Foo[[Block::Content-Chunk]]GaFoo[[Block::Content-Chunk]]Gamm';
	}
	
	function UseTemplate($args) {
		$template = $this->getTemplate($args['template']);
		if($template == FALSE)
			return FALSE;
		$chunks = explode(',',$args['chunks']);
		foreach($chunks as $chunk) {
			$template = $this->replace_one('[[Block::Content-Chunk]]', "[[Block::Content-Chunk::$chunk]]", $template);
		}
		return $template;
	}
	
	function Chunk($args) {
		list($chunk) = $args;
		$file = "$this->data_root/".$chunk.".chunk";
		if(!empty($chunk) && is_readable($file))
			return file_get_contents($file);
		return FALSE;
	}
}
?>