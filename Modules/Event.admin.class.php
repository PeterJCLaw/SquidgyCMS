<?php
class AdminEvent extends Admin {
	function AdminEvent() {
		parent::__construct('Add Events to the Events list');
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

	function submit()
	{
		global $start_hour, $start_minute, $start_day, $start_month, $start_year, $finish_hour, $finish_minute, $finish_day, $finish_month, $finish_year;
		global $content, $event_title, $header_link, $debug_info, $mail_webmsater_on_event;

		$error	= "";

		if(empty($content))
			$error	.= "\nNo content provided";
		if(!(strpos($content, '<?') === FALSE))
			$error	.= "\nInvalid content provided: PHP is not allowed";
		if(empty($event_title))
			$error	.= "\nNo title provided";

		if(!empty($error))	//if there's an error then bail
			return $error;

		$content	= addslashes(stripslashes($content));	//they get added when sent

		if(!is_readable($this->data_file))
			return "\nEvent file not readable";

		if(!is_writable($this->data_file))
			return "\nEvent file not writeable";

		$content	= str_replace(array("\n", "\r"), '', nl2br(stripslashes($content)));	//fix the slashes and newlines
		$start_time		= mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year);
		$finish_time	= mktime($finish_hour, $finish_minute, 0, $finish_month, $finish_day, $finish_year);

		$output = "\n".time()."|:|$start_time|:|$finish_time|:|".htmlspecialchars($event_title)."|:|$content";


		$debug_info .= "\$start_time=$start_time\n<br />\$finish_time=$finish_time\n<br />\n";

		if($mail_webmsater_on_event) {
			$body	= "Title = $event_title\nDescription = ".strip_tags($content)."\n\n"
					."Start_hour = $start_hour\nStart_minute = $start_minute\nStart_month = $start_month\nStart_day = $start_day\nStart_year = $start_year\n\n"
					."Finish_hour = $finish_hour\nFinish_minute = $finish_minute\nFinish_month = $finish_month\nFinish_day = $finish_day\nFinish_year = $finish_year\n"
					."\n--\n"."X-Mailer: PHP/".phpversion();	//mail signature, including php version

		
			mail(email_addr("webmaster"), "New $website_name_short Event by $username: '$event_title'", $body, "From: $website_name_short Admin Form <$website_form_email>");
		}

		//now we output the stuff we just organised & return
		return file_put_stuff($this->data_file, $output, 'a');
	}

}
?>