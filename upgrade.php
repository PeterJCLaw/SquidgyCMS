<?php
$opt_list	= array('articles', 'a_cont', 'pages', 'p_cont', 'committee', 'do_events', 'events_content');
	include 'Head.inc.php';
?>
<form action="" method="get"><div>
<?php print_tickboxes($opt_list); ?>
<input type="hidden" name="debug" value="<?php echo $debug; ?>" />
<input type="submit" name="upgrade" value="Upgrade" />
</div></form>
<?php
if(!empty($upgrade)) {

	if(!empty($_GET['target']))
		extract($_GET['target'], EXTR_OVERWRITE);

//names first
	$GEN_art_old	= Filtered_Dir_List("Site_Files", "_art.inc.php");
	$GEN_pages_old	= Filtered_Dir_List("Site_Files", "._page.inc.php");
	$GEN_comm_old	= Filtered_Dir_List("Users_Info", ".inc.php");

	$fail_list	= array();
	$rename	= 0;

	if($articles)
		foreach($GEN_art_old as $val) {
			$orig	= "Site_Files/".$val."_art.inc.php";
			if(!is_readable($orig) || !is_writeable($orig) || !rename($orig, "Site_Files/$val.article"))
				array_push($fail_list, $orig);
			else
				$rename++;
		}

	if($pages)
		foreach($GEN_pages_old as $val) {
			$orig	= "Site_Files/".$val."_page.inc.php";
			if(!is_readable($orig) || !is_writeable($orig) || !rename($orig, "Site_Files/$val.page"))
				array_push($fail_list, $orig);
			else
				$rename++;
		}

	if($committee)
		foreach($GEN_comm_old as $val) {
			$orig	= "Users_Info/$val.inc.php";
			if(!is_readable($orig) || !is_writeable($orig) || !rename($orig, "Users_Info/$val.comm.php"))
				array_push($fail_list, $orig);
			else
				$rename++;
		}

	if($do_events) {
		$orig	= "Site_Files/events.inc.php";
		if(!is_readable($orig) || !is_writeable($orig) || !rename($orig, $event_file))
			array_push($fail_list, $orig);
		else
			$rename++;
	}

	if(!empty($fail_list))
		echo '<p id="error" style=" margin: 3px; padding: 7px; background-color: #FFB6C1;">
	The following files were not renamed - please make them world writeable then run this script again or rename them maunally:<br />
'.implode(",<br />\n", $fail_list)."</p>";
	elseif($rename)
		echo "<p>$rename file(s) successfully renamed.</p>";
	else
		echo "<p>All filenames are already correct.</p>";

//content second
	$GEN_art	= Filtered_Dir_List("Site_Files", ".article");
	$GEN_pages	= Filtered_Dir_List("Site_Files", ".page");

	$fail_list	= array();
	$cont_up	= 0;

	if($articles && $a_cont)
		foreach($GEN_art as $val) {
			$file_path	= "Site_Files/$val.article";
			$content	= file_get_contents($file_path);
			$debug_info	.= "\$content($file_path)=$content\n<br />\n";
			if(FALSE === strpos($content, "\$title"))
				continue;
			include $file_path;
			$id	= get_GEN_id($val);
			$title	= urlencode($title);	//remove bad characters
			if(!rename($file_path, "Site_Files/$id-$title.article"))	//convert to a full-title name
				array_push($fail_list, "Site_Files/$id-$title.article");
			elseif(file_put_stuff("Site_Files/$id-$title.article", $content, 'w'))	//change the content
				array_push($fail_list, $file_path);
			else
				$cont_up++;
		}

	if($pages && $p_cont)
		foreach($GEN_pages as $val) {
			$file_path	= "Site_Files/$val.page";
			$content	= file_get_contents($file_path);
			$debug_info	.= "\$content($file_path)=$content\n<br />\n";
			if(FALSE === strpos($content, "\$page_layout"))
				continue;
			include $file_path;
			if(file_put_stuff($file_path, "[[Block::Article-table::".implode("||", $page_layout)."]]", 'w'))	//change the content
				array_push($fail_list, $file_path);
			else
				$cont_up++;
		}

	if($do_events && $events_content) {
		$content	= file_get_contents($event_file);
		$debug_info	.= "\$content($event_file)=$content\n<br />\n";
		if(FALSE !== strpos($content, "<?php")) {
			include $event_file;
			file_put_stuff($event_file, '', 'w');	//clear the file
			foreach($events as $key => $val) {
				if($key == 'index')
					continue;
				if(file_put_stuff($event_file, "\n".time()."|:|".$val['start']."|:|".$val['finish']."|:|".htmlspecialchars($val['title'])."|:|".$val['descr'], 'a'))	//change the content
					array_push($fail_list, "$events_file-$key");
				else
					$cont_up++;
			}
		}
	}

	if(!empty($fail_list))
		echo '<p id="error" style="margin: 3px; padding: 7px; background-color: #FFB6C1;">
	The following files were not content upgraded - please make them world writeable then run this script again or content upgraded them maunally:<br />
'.implode(",<br />\n", $fail_list)."</p>";
	elseif($cont_up)
		echo "<p>$cont_up file(s) successfully content upgraded.</p>";
	else
		echo "<p>All files' content is already correct.</p>";

//done
}
	include 'Foot.inc.php';
?>