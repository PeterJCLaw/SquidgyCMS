<?php
class AdminArticle extends Admin {
	function AdminArticle() {
		parent::__construct('Create or change site articles');
	}

	function printFormAdmin() {
		global $GEN_art, $art_req, $debug_info;

		$debug_info .= @"\$art_req = '$art_req', \$GEN_art = '".print_r($GEN_art, true)."'\n<br />\n";

		if(!empty($art_req) && in_array($art_req, $GEN_art)) {
			$content	= file_get_contents("$this->data_root/$art_req.article");
			$art_title	= get_GEN_title($art_req);
			$art_id	= $art_req;
		} else {
			$content	= $art_title	= "";
			$art_id	= "new";
		}
		?>
			<input type="hidden" name="article_id" id="article_id" value="<?php echo $art_id; ?>" />
			<table><tr>
				<th><label for="article_title" title="The title of the article">Article Title:</label></th>
				<td><input value="<?php echo $art_title; ?>" class="text" name="article_title" id="article_title" title="The title of the article" /></td>
			</tr><tr>
				<?php $this->print_select_cells($GEN_art, $art_req); ?>
			</tr></table>
<?php
		$this->printTextarea($content);
		return;
	}

	function submit()
	{
		global $content, $article_title, $article_id, $header_link, $debug_info;

		$error	= "";

		if(empty($content))
			$error	= "\nNo content provided";
		if(!(stripos($content, '<?') === FALSE))
			$error	.= "\nInvalid content provided: PHP is not allowed";
		if(empty($article_title))
			$error	.= "\nNo title provided";

		if(!empty($error))	//if there's an error then bail
			return $error;

		$old_article_id	= urldecode($article_id);

		if($article_id == 'new') {	//if its a new article
			$article_id	= urlencode(get_next_id($this->data_root, ".article")."-$article_title");
		} elseif($this->new_id_needed($old_article_id, $article_title) && is_writable("$this->data_root/$article_id.article")) {	//if we should and can change stuff
			$article_id	= get_next_id($this->data_root, ".article")."-$article_title";
			$error	= $this->change_something_in_all_pages($old_article_id, $article_id);
			if(!empty($error))	//if there's an error then bail
				return $error;
			$error	= $this->delete_file("$this->data_root/".urlencode($old_article_id).".article");
			$article_id	= urlencode($article_id);
		}

		$debug_info .= @"\$old_article_id = '$old_article_id', \$article_id = '$article_id'\n<br />\n";

		$header_link	= "&art_req=$article_id";

		return $error.file_put_stuff("$this->data_root/$article_id.article", $content, 'w');
	}
}
?>