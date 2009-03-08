<?php
class Article extends Block {
	function Article() {
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