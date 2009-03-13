<?php
class News extends Block {
	function News() {
		parent::__construct();
	}
	function block($args) {
		global $news_file;

		if(!is_readable($news_file))
			return '<span id="news" style="display: none;"> (The file was not readable)</span>';

		$news	= get_file_assoc($news_file, array('added', 'expires', 'content'));	//read the info into an array, one element per line

		if(empty($news))
			return FALSE;

		if(!empty($args))
			list($key, $add_format, $exp_format, $all)	= $args;

		if(empty($key) || !in_array($key, array('added', 'expires')))	//sanity check
			$key	= "added";

		if(empty($add_format))
			$add_format	= "D, j, F Y";

		if(empty($exp_format))
			$exp_format	= $add_format;

		multi2dSortAsc($news, $key);	//uses array_multisort

		log_info('News Block, news = ', $news);
		$news_out	= '';
		foreach($news as $val) {
			if($val['expires'] > time())
				$news_out	.= '		<li title="Added: '.date($add_format, $val['added']).', Expires: '.date($exp_format, $val['expires']).'">'.$val['content']."</li>\n";
		}

		if(empty($news_out))
			return;

		return '<div id="news">
	<span id="news_title">News:</span>
	<ul>'."\n$news_out	</ul>\n</div>";
	}
}
?>