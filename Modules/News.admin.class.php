<?php
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
		global $debug_info, $content, $day, $month, $year, $news_file;

		$content	= str_replace(array("\n", "\r"), '', nl2br(stripslashes($content)));	//fix the slashes and newlines
		$timestamp	= mktime(0,0,0,$month, $day + 1, $year);
		$output = "\n".time()."|:|$timestamp|:|$content";

		return file_put_stuff($news_file, $output, 'a');
	}
}
?>