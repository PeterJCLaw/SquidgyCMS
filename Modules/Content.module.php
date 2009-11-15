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

		$chunks = FileSystem::Filtered_File_List($this->data_root, '.chunk');
		natsort($chunks);

		if(!empty($page_req) && in_array($page_req, $chunks)) {
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
				<?php $this->print_select_cells($chunks, $page_req); ?>
			</tr></table>
<?php
		$this->printTextarea($content);
		return;
	}

	function submit($content=0) {
		list($chunk_title, $chunk_id, $header_link) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global $debug_info;

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
			$error	= $this->change_something_in_all_chunks($old_chunk_id, $chunk_id);
			if(!empty($error))	//if there's an error then bail
				return $error;
			$error	= $this->delete_file("$this->data_root/".urlencode($old_chunk_id).".chunk");
			$chunk_id	= urlencode($chunk_id);
		}

		$debug_info .= @"\$old_chunk_id = '$old_chunk_id', \$chunk_id = '$chunk_id'\n<br />\n";

		$header_link	= "&p=$chunk_id";

		return FileSystem::file_put_contents("$this->data_root/".$chunk_id.".chunk", $content);
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

class Content {
	/* parses the squidgyCMS block arguments from a string into an array, associative if applicable */
	function SquidgyParseArgs($argString) {
		if(empty($argString) || ereg('^\s+$', $argString) !== FALSE)
			return array();

		if( ereg('^\{.*\}$', $argString) !== FALSE )
			$argArray	= explode('||', substr($argString, 1, -1));	//throw them into an array
		else
			return array($argString);

		if(strpos($argString, ':') !== FALSE) {
			foreach($argArray as $arg) {
				list($name, $value) = explode(':', $arg);
				$argDict[$name] = $value;
			}
			return $argDict;
		} else
			return $argArray;
	}

	/* parses the squidgyCMS wiki-style pages and makes html */
	function SquidgyParser($page_file, $start = 0, $finish = 0) {
		global $debug, $debug_info;
		$page	= file_get_contents($page_file);
		$len	= strlen($page);

		$debug_info	.= "Parser: start = $start, finish = $finish, ";

		if(!empty($start) || !empty($finish)) {	//then we need to shorten it
			if(!empty($start))
				$start_pos	= strpos($page, $start);
			else
				$start_pos	= -1;

			if(!empty($finish))
				$finish_pos	= strpos($page, $finish);
			else
				$finish_pos	= -1;

			$debug_info	.= "start_pos = $start_pos, finish_pos = $finish_pos<br />\n";

			if($start_pos >= $len || ($start_pos >= $finish_pos && $finish_pos != -1) || $start_pos === $finish_pos)
				return FALSE;

			if($finish_pos == -1)
				$page	= substr($page, (int)$start_pos);
			elseif($start_pos == -1)
				$page	= substr($page, 0, (int)$finish_pos);
			else
				$page	= substr($page, (int)$start_pos, ((int)$finish_pos)-((int)$start_pos));
		}

		$debug_info	.= "<br />\n";

		$enabled_modules	= Module::list_enabled(true);
		$len	= strlen($page);
		$i	= 0;
		while(!(strpos($page, '[[Block::') === FALSE || strpos($page, ']]') === FALSE) && $i<$len) {	//keep going until you run out of custom bits

			$block_call	= substr($page, strpos($page, '[[Block::')+9, (strpos($page, ']]') - strpos($page, '[[Block::') - 9) );	//grab the call from the source

			list($type)	= explode("::", $block_call);	//get the type
			list($module, $method)	= explode("-", $type);

			$args	= substr($block_call, strlen($type)+2);	//grab the arguments
			$args	= Content::SquidgyParseArgs($args);	//turn them into a useful array

			$debug_info	.= "block_call = '$block_call', i = '$i', type = '$type', args = '".print_r($args, true)."'\n<br />\n";

			$block_html	= '';

			$module_path = Module::get_path($module);

			if($module_path !== FALSE && in_array($module, $enabled_modules)) {
				require_once($module_path);

				$block	= "Block$module";

				if(class_exists($block)) {
					$block_obj	= new $block();

					if(method_exists($block_obj, $method))
						$block_html	= $block_obj->$method($args);
					else
						log_info("Block '$block' has no method '$method'");

					if(empty($block_html))
						log_info("Block method '${block}->$method' returned nothing");
				} else
					log_info("Module '$module' has no block '$block'");
			} else
				log_info("Module '$module' does not exist or is not enabled");

			$page	= str_replace("[[Block::$block_call]]", $block_html, $page);

			$i+=9;
		}
		return $page;
	}

	function edit_URL($id=FALSE) {
		if(empty($id))
			$id = $GLOBALS['page_id'];
		if($id == 'admin' || empty($id))
			return FALSE;
		return "admin?p=$id#Content";
	}

	function get_file_from_id($id) {
		if($id === FALSE)
			return FALSE;
		$path = $GLOBALS['data_root'].'/'.$id.'.chunk';
		if(is_file($path) && is_readable($path))
			return $path;
		return FALSE;
	}

	function get_title_from_id($id) {
		return urldecode(substr($id, strpos($id, "-")+1));
	}
}
?>