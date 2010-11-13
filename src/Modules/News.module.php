<?php
#name = News
#description = Enables the creation of short items of news which have an expiry date
#package = Core - optional
#type = content
###

class AdminNews extends Admin {
	function AdminNews() {
		parent::__construct();
		$this->complex_data = true;
	}

	function printFormAdmin() {
		global $debug_info;	?>
			<table><tr>
			<th>Valid Until:</th><td>
				<?php
				list($day, $month, $year) = split(".",date("j.n.Y", strtotime('+7 days')));

				$debug_info .= "\$day=$day,	\$month=$month,	\$year=$year\n<br />\n";

				$this->genDateSelector("", 'News_form', $day, $month, $year);
				?>
				</td>
			</tr></table>
<?php
		$this->printTextarea();
		return;
	}

	function submit($content=0) {
		list($day, $month, $year) = array();
		extract($_POST, EXTR_IF_EXISTS);
		$this->get_data();

		$content	= str_replace(array("\n", "\r"), '', nl2br($content));	//fix the slashes and newlines
		$timestamp	= mktime(0,0,0,$month, $day + 1, $year);
		array_push($this->data, array('added'=>time(), 'expires'=>$timestamp, 'content'=>$content));

		return $this->put_data();
	}
}

class BlockNews extends Block {
	function BlockNews() {
		parent::__construct();
		$this->complex_data = true;
		$this->get_data();
	}
	function block($args) {

		if(!is_readable($this->data_file))
			return '<span id="news" style="display: none;"> (The file was not readable)</span>';

		if(empty($this->data))
			return FALSE;

		if(!empty($args)) {
			list($key, $add_format, $exp_format, $all)	= $args;
			extract($args, EXTR_IF_EXISTS);
		}

		if(empty($key) || !in_array($key, array('added', 'expires')))	//sanity check
			$key	= "added";

		if(empty($add_format))
			$add_format	= "D, j, F Y";

		if(empty($exp_format))
			$exp_format	= $add_format;

		multi2dSortAsc($this->data, $key);	//uses array_multisort

		log_info('News Block, news = ', $this->data);
		$news_out	= '';
		foreach($this->data as $val) {
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