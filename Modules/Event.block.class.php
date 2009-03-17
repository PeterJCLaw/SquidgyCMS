<?php
class Event extends Block {
	function Event() {
		parent::__construct();
	}

	function block($args)
	{
		global $debug_info;

		$events	= get_file_assoc($this->data_file, array('time', 'start', 'finish', 'title', 'content'));

		if(empty($events))
			return '<div id="events" class="gen_txt">No Upcomoing Events<span style="display: none;"> (The file was not readable)</span>.</div>';

		if(empty($args))
			$date_format	= "g:i a D, j, F Y";
		else
			list($date_format)	= $args;

		multi2dSortAsc($events, 'start');	//uses array_multisort

		$out	= '';
		$debug_info	.= "args = $args, event_file = '$this->data_file', date_format = '$date_format'\n<br />\n";

		foreach($events as $val)
		{
			if($val['start'] > time())
				$out	.= '	<h3 title="Added: '.date($date_format, $val['time']).'">'.$val['title'].'</h3>
	<p class="time"><strong>Event Start:</strong>&nbsp;'.date($date_format, $val['start']).'</p>
	<p class="time"><strong>&nbsp;Event End:</strong>&nbsp;'.date($date_format, $val['finish']).'</p>
	'.$val['content']."
	<br />\n";
		}

		if(empty($out))
			$out	= "No Upcomoing Events.";

		return "<div id=\"events\" class=\"gen_txt\">\n$out\n</div>";
	}
}
?>