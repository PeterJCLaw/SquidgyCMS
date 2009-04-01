<?php
#name = Page
#description = Allows pages to be created and edited
#package = Core - optional
#type = content
###

class AdminPage extends Admin {
	function AdminPage() {
		parent::__construct(-1);
	}

	function printFormAdmin() {
		global $GEN_pages, $page_req;

		if(!empty($page_req) && in_array($page_req, $GEN_pages)) {
			$content	= file_get_contents("$this->data_root/$page_req.page");
			$page_title	= get_GEN_title($page_req);
			$page_id	= $page_req;
		} else {
			$content	= $page_title	= "";
			$page_id	= "new";
		}
		?>
			<input type="hidden" name="page_id" id="page_id" value="<?php echo $page_id; ?>" />
			<table><tr>
				<th><label for="page_title" title="The title of the page">Page Title:</label></th>
				<td><input  value="<?php echo $page_title; ?>" class="text" name="page_title" id="page_title" title="The title of the page" /></td>
			</tr><tr>
				<?php $this->print_select_cells($GEN_pages, $page_req); ?>
			</tr></table>
<?php
		$this->printTextarea($content);
		return;
	}

	function submit()
	{
		global $content, $page_title, $page_id, $header_link, $debug_info;

		$error	= "";

		if(empty($content))
			$error	.= "\nNo content provided";
		if(!(strpos($content, '<?') === FALSE))
			$error	.= "\nInvalid content provided: PHP is not allowed";
		if(empty($page_title))
			$error	.= "\nNo title provided";

		if(!empty($error))	//if there's an error then bail
			return $error;

		$old_page_id	= urldecode($page_id);

		if($page_id == 'new') {	//if its a new page
			$page_id	= urlencode(get_next_id($this->data_root, ".page")."-$page_title");
		} elseif($this->new_id_needed($old_page_id, $page_title) && is_writable("$this->data_root/$page_id.page")) {	//if we should and can change stuff
			$page_id	= get_next_id($this->data_root, ".page")."-$page_title";
			$error	= $this->change_something_in_all_pages($old_page_id, $page_id);
			if(!empty($error))	//if there's an error then bail
				return $error;
			$error	= $this->delete_file("$this->data_root/".urlencode($old_page_id).".page");
			$page_id	= urlencode($page_id);
		}

		$debug_info .= @"\$old_page_id = '$old_page_id', \$page_id = '$page_id'\n<br />\n";

		$header_link	= "&p=$page_id";

		return file_put_stuff("$this->data_root/".$page_id.".page", $content, 'w');
	}
}
?>