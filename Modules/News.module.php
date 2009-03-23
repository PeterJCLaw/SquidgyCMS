<?php
#name = News
#description = Enables the creation of short items of news which have an expiry date
#package = Core - optional
#type = content
###

class AdminNews extends Admin {
	function AdminNews() {
		parent::__construct('Add News Article to the News section of the site');
	}

	function printFormAdmin() {
		global $debug_info;	?>
			<table><tr>
			<th>Valid Until:</th><td>
				<?php
				list($day, $month, $year) = split("[./-]",date("j.n.Y", strtotime('+7 days')));

				$debug_info .= "\$day=$day,	\$month=$month,	\$year=$year\n<br />\n";

				$this->genDateSelector("", 'News_form', $day, $month, $year);
				?>
				</td>
			</tr></table>
<?php
		$this->printTextarea();
		return;
	}

	function submit() {
		global $debug_info, $content, $day, $month, $year;

		$content	= str_replace(array("\n", "\r"), '', nl2br($content));	//fix the slashes and newlines
		$timestamp	= mktime(0,0,0,$month, $day + 1, $year);
		$output = "\n".time()."|:|$timestamp|:|$content";

		return file_put_stuff($this->data_file, $output, 'a');
	}
}

class BlockNews extends Block {
	function BlockNews() {
		parent::__construct();
	}
	function block($args) {

		if(!is_readable($this->data_file))
			return '<span id="news" style="display: none;"> (The file was not readable)</span>';

		$news	= get_file_assoc($this->data_file, array('added', 'expires', 'content'));	//read the info into an array, one element per line

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