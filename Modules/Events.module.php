<?php
#name = Events
#description = Enables the creation and display of events
#package = Core - optional
#type = content
###

class AdminEvents extends Admin {
	function AdminEvents() {
		parent::__construct();
		$this->complex_data = true;
	}

	function printFormAdmin() { ?>
			<table><tr>
				<th>Event Start:</th><td>
				<?php
				list($d, $m, $y)	= explode("-", date("d-m-Y", strtotime("+7 days")));
				$this->genTimeSelector("start_", 19);
				echo "			&nbsp;&nbsp;\n";
				$this->genDateSelector("start_", 'Event_form', $d, $m, $y);
				?>
				</td>
			</tr><tr>
				<th>Event End:</th><td>
				<?php
				$this->genTimeSelector("finish_", 21, 30);
				echo "			&nbsp;&nbsp;\n";
				$this->genDateSelector("finish_", 'Event_form', $d, $m, $y);
				?>
				</td>
			</tr><tr>
				<th><label for="event_title">Event Title:</label></th>
				<td><input class="text" type="text" name="event_title" id="event_title" /></td>
			</tr></table>
<?php
		$this->printTextarea();
		return;
	}

	function submit($content=0) {
		list($start_hour, $start_minute, $start_day, $start_month, $start_year) = array();
		list($finish_hour, $finish_minute, $finish_day, $finish_month, $finish_year, $event_title, $header_link) = array();
		extract($_POST, EXTR_IF_EXISTS);
		global $debug_info, $mail_webmsater_on_event;

		$this->get_data();
		$error	= "";

		if(empty($content))
			$error	.= "\nNo content provided";
		if(!(strpos($content, '<?') === FALSE))
			$error	.= "\nInvalid content provided: PHP is not allowed";
		if(empty($event_title))
			$error	.= "\nNo title provided";

		if(!empty($error))	//if there's an error then bail
			return $error;

		$content	= str_replace(array("\n", "\r"), '', nl2br(stripslashes($content)));	//fix the slashes and newlines
		$start_time		= mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year);
		$finish_time	= mktime($finish_hour, $finish_minute, 0, $finish_month, $finish_day, $finish_year);

		array_push($this->data , array('time'=>time(), 'start'=>$start_time, 'finish'=>$finish_time, 'title'=>htmlspecialchars($event_title), 'content'=>$content));

		$debug_info .= "\$start_time=$start_time\n<br />\$finish_time=$finish_time\n<br />\n";

		if($mail_webmsater_on_event) {
			$body	= "Title = $event_title\nDescription = ".strip_tags($content)."\n\n"
					."Start_hour = $start_hour\nStart_minute = $start_minute\nStart_month = $start_month\nStart_day = $start_day\nStart_year = $start_year\n\n"
					."Finish_hour = $finish_hour\nFinish_minute = $finish_minute\nFinish_month = $finish_month\nFinish_day = $finish_day\nFinish_year = $finish_year\n"
					."\n--\n"."X-Mailer: PHP/".phpversion();	//mail signature, including php version

		
			mail(email_addr("webmaster"), "New $website_name_short Event by $username: '$event_title'", $body, "From: $website_name_short Admin Form <$website_form_email>");
		}

		//now we output the stuff we just organised & return
		return $this->put_data();
	}

}

class BlockEvents extends Block {
	function BlockEvents() {
		parent::__construct();
		$this->complex_data = true;
	}

	function block($args)
	{
		global $debug_info;

		$this->get_data();

		if(empty($this->data))
			return '<div id="events" class="gen_txt">No Upcomoing Events<span style="display: none;"> (The file was not readable)</span>.</div>';

		if(empty($args))
			$date_format	= "g:i a D, j, F Y";
		else
			list($date_format)	= $args;

		multi2dSortAsc($this->data, 'start');	//uses array_multisort

		$out	= '';
		$debug_info	.= "args = $args, event_file = '$this->data_file', date_format = '$date_format'\n<br />\n";

		foreach($this->data as $val) {
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