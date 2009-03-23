<?php
#name = Article
#description = Enables the editing and display in pages of articles, which can be re-used in pages
#package = Core - optional
#type = content
###

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
		if(!(strpos($content, '<?') === FALSE))
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

class BlockArticle extends Block {
	function BlockArticle() {
		parent::__construct();
	}

	function table($article_list)
	{
		if(empty($article_list))
			return FALSE;

		$article_list_flag	= FALSE;
		$out	= "\n<table>\n";

		for($i = 0; isset($article_list[$i]) && isset($article_list[$i+1]); $i += 2)
		{
			$retval	= $this->row(array($article_list[$i], $article_list[$i+1]));
			if(!(FALSE === $retval))
				$out	.= $retval;
		}
		$out	.= "\n</table>\n";

		return $out;
	}

	function row($articles_in)
	{
		global $logged_in, $debug_info;

		if(empty($articles_in))
			return FALSE;

		$articles	= array();

		foreach($articles_in as $id)
		{
			$path	= "$this->data_root/".urlencode($id).".article";
			$val['id']	= $id;

			if(is_readable($path)) {
				$val['title']	= get_GEN_title($id);
				$val['content']	= file_get_contents($path);
				if($logged_in)
					$val['edit']	= '<span class="edit f_right"><a href="Admin.php?art_req='.$id.'#Article" title="Edit '.$val['title'].'">Edit</a></span>';
				else
					$val['edit']	= '';
				$val['head']	= $val['edit'].'<h4>'.$val['title'].'</h4>';
			} else {
				$debug_info	.= "path '$path' not readable\n<br />\n";
				$val['edit']	= $val['title']	= $val['content']	= $val['head']	= '&nbsp;';
				$val['id']	= -1;
			}
			array_push($articles, $val);
		}

		if(empty($articles) || ($articles[0]['id'] == -1 && $articles[1]['id'] == -1))	//if there's nothing there
			return FALSE;

		if($articles[0]['id'] == $articles[1]['id']) {
			return '	<tr class="art_row">
			<th class="left">'.$articles[0]['title'].'</th>
			<th>'.$articles[0]['edit'].'</th>
		</tr><tr class="art_row">
			<td colspan="2" style="width: 100% !important;">'.$articles[0]['content'].'</td>
		</tr>';
			} else
				return '	<tr class="art_row">
			<th class="left">'.$articles[0]['head'].'</th>
			<th>'.$articles[1]['head'].'</th>
		</tr><tr class="art_row">
			<td>'.$articles[0]['content'].'</td>
			<td>'.$articles[1]['content'].'</td>
		</tr>';
	}
}
?>