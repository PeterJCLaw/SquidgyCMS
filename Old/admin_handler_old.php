<?php
if (!empty($_GET))
	extract($_GET, EXTR_OVERWRITE);

if (!empty($_POST))
	extract($_POST, EXTR_OVERWRITE);

if($debug)	//debug stuff
{
	if(!isset($username) || $username == "") $username = "president";
}
$logged_in	= FALSE;	//just in case

	require_once("functions_FSPHP.inc.php");	//contains the File System PHP Gallery functions (both mine and the original ones)
	require_once("functions_login.inc.php");	//contains the login functions
	require_once("functions_General.inc.php");	//contains my general functions - the file system gallery ones are now separate
	require_once("config.inc.php");			//these files are now included in all the cathsoc pages since I'm using lots of functions
	require_once("functions_$website_name_short.inc.php");	//contains ACCF specific functions

// local functions	---------------------------------------------------------------

function reset_pass($who)
{
	global $debug_info, $job_list, $error;

	$file	= "Users_Info/".info_name($who).".inc.php";	//convert to a filename type and include
	include $file;
	$error .= change_commiittee_files($pass_hash, md5("password"), $file);
	send_mail(email($who), "$who: $website_name Website Password Reset", "Dear ".$who
		.",\n\nYour password for the $website_name website has been reset to 'password' (without the quotes)."
		."\n\nIf you did not request this and you have not just been elected to the committee then please email the Webmaster ($webmaster_email) and report this error."
		."\n\n$website_name Webmaster", "From: $website_name Webmaster <$webmaster_email>");
	return;
}

function change_commiittee_files($old_val, $new_val, $file)
{
	global $debug_info;

	$old_val	= "= \"".$old_val;
	$new_val	= "= \"".$new_val;

	$old_file_contents = file_get_contents($file);

	$new_file_contents = str_replace($old_val, $new_val, $old_file_contents);

	$debug_info .= "\$file=$file\n<br />\$new_val=$new_val\n<br />\$old_val=$old_val\n<br />"
			."\$old_file_contents=$old_file_contents\n<br />\$new_file_contents=$new_file_contents\n<br />\n";

	if(!file_put_contents($file, $new_file_contents))
		return "\nFile write failed\n<br />\n";

	return;
}

// end local functions	-----------------------------------------------------------

$logged_in	= user_login();	//only allow them to make changes if they are actually logged in

$debug_info .= "\$new_name=$new_name\n<br />\$name=$name\n<br />\$type=$type\n<br />\$content=$content\n<br />\n";

$type	= ucwords($type);

if(($content == "" || $content ==  " " || !isset($content)) && !in_array($type, array("Webmaster", "Page", "Manage")))
{
	switch($type)
	{
		case "Article":
			$content = $Article_content;
			break;
		case "News":
			$content = $News_content;
			break;
		case "Social":
			$content = $Social_content;
			break;
		case "Music":
			$content = $Music_content;
			break;
		case "Profile":
			$content = $Profile_content;
			break;
		default:
			break;
	}
}
if(($content == "" || $content ==  " " || !isset($content)) && !in_array($type, array("Webmaster", "Page", "Manage")))
{
	$error	.= "You have not specified any content";
	$debug_info	.= "\nno content suplied\n<br />";
} else {
$debug_info .= "\$content=$content\n<br />\n";
$content	= addslashes(stripslashes($content));	//they get added when sent
$debug_info .= "\$content=$content\n<br />\n";
if($type == "Manage")
{
	$GEN_pages	= Filtered_Dir_List("Site_Files", "_page.inc.php");
	$GEN_art	= Filtered_Dir_List("Site_Files", "_art.inc.php");

	foreach($GEN_pages as $tmpval)
	{
		if($del[$tmpval."_page"])
			if(!unlink("Site_Files/".$tmpval."_page.inc.php"))
				$error	.= "\nFailed to delete page $tmpval\n<br />\n";
	}
	foreach($GEN_art as $tmpval)
	{
		if($del[$tmpval."_art"])
			if(!unlink("Site_Files/".$tmpval."_art.inc.php"))
				$error	.= "\nFailed to delete article $tmpval\n<br />\n";
	}
}
elseif($type == "Page")
{
	$debug_info .= "\$page_id=$page_id\n<br />\n";
	$out	= "<?php\n\$page_layout	= array(";

	for($i=1; $i <= 2*$num_rows; $i+=2)
	{
		$article_id[$i]	= $_POST["article_id_$i"];		//get the data into an array
		$article_id[$i+1]	= $_POST["article_id_".($i+1)];

		if($article_id[$i+1] != 0 || $article_id[$i] != 0)	//if one of them isn't zero
			$out	.= "\"".$article_id[$i]."\", \"".$article_id[$i+1]."\"".($i + 1 < 2*$num_rows ? ", ": ");\n?>");
	}
	if($out[strlen($out)-2] == "," && $out[strlen($out)-1] == " ")
	{
		$out[strlen($out)-2]	= ")";
		$out[strlen($out)-1]	= ";";
		$out	.= "\n?>";
	}

	if(!is_writable("Site_Files/".$page_id."_page.inc.php"))	//if its not writeable (ie new)
		$page_id	= $page_id."-".ucwords($page_head_title);		//create an id form number and title
	elseif(substr(stristr($page_id, "-"), 1) != $page_head_title)	//elseif the name has changed
	{
		if(!unlink("Site_Files/".$page_id."_page.inc.php"))	// remove the old file
			$error	.= "\nFailed to delete page $tmpval\n<br />\n";

		$page_id	= str_replace(stristr($page_id, "-"), "", $page_id)."-".ucwords($page_head_title);	//reuse the id number and use the new title
	}

	if(!file_put_contents("Site_Files/".$page_id."_page.inc.php", $out))	//put the file
		$error	.= "\nFile write failed\n<br />\n";

	$debug_info .= "\$page_id=$page_id\n<br />\$out=$out\n<br />\n";
	$header_link	= "&page_req=$page_id";

}
elseif($type == "Article")
{
	$out	= "<?php\n\$title = \"$article_title\";\n\$content	= \"$content\";\n?>";

	if(stristr($article_id, "new"))		//if its new generate a new id
		$article_id	= substr($article_id, 3)."-".ucwords(first_name($article_title));

	if(!file_put_contents("Site_Files/".$article_id."_art.inc.php", $out))
		$error	.= "\nFile write failed\n<br />\n";

	$debug_info .= "\$article_id=$article_id\n<br />\$out=$out\n<br />\n";
	$header_link	= "&art_req=$article_id";

}
elseif($type == "News" || $type == "Social")
{
	if($type == "News")
	{
		$out_file	= $news_file;
		$timestamp	= mktime(0,0,0,$month, $day + 1, $year);
		$output = "\n\n<?php	if(\$now <= $timestamp)	\$news_out .= \"$content\\n<br />\"; ?>";

		$debug_info .= "\$out_file=$out_file\n<br />\n";
	}
	else	//	Social ---------
	{
		include $social_file;
		$index = (isset($social_out['index']) ? $social_out['index'] : 0);

		$out_file		= $social_file;
		$start_time		= mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year);
		$finish_time	= mktime($finish_hour, $finish_minute, 0, $finish_month, $finish_day, $finish_year);

		if(filesize($social_file))
			$output	= "\n";

		$output		.= "<?php\n"
					."\$social_out[$index]['start']=$start_time;\n"
					."\$social_out[$index]['finish']=$finish_time;\n"
					."\$social_out[$index]['title']=\"".htmlspecialchars($event_title)."\";\n"
					."\$social_out[$index]['descr']=\"$content\";\n"
					."\$social_out['index']=".($index + 1).";\n?>";

		$debug_info .= "\$start_time=$start_time\n<br />\$finish_time=$finish_time\n<br />\$name=$name\n<br />\$index=$index\n<br />\$out_file=$out_file\n<br />\n";

		$body			= "Title = $event_title\nDescription = ".strip_tags($content)."\n\n"
						."Start_hour = $start_hour\nStart_minute = $start_minute\nStart_month = $start_month\nStart_day = $start_day\nStart_year = $start_year\n\n"
						."Finish_hour = $finish_hour\nFinish_minute = $finish_minute\nFinish_month = $finish_month\nFinish_day = $finish_day\nFinish_year = $finish_year\n"
						."\n--\n"."X-Mailer: PHP/".phpversion();	//mail signature, including php version

		mail(email_addr("webmaster"), "New $website_name Event by $username: '$event_title'", $body, "From: $website_name Admin Form <$website_form_email>");

	}
	//now we output the stuff we just organised
	if(!file_append($out_file, $output))
		$error	.= "\nFile append failed\n<br />\n";
}
elseif($type == "Music")
{
	if(!file_put_contents($music_file, $content))
		$error	.= "\nFile write failed\n<br />\n";
	$debug_info .= "\$music_file=$music_file\n<br />\n";

}
elseif($type == "Profile")
{
	$new_name	= addslashes(stripslashes($new_name));	//they get added when sent

	$file = "Users_Info/".info_name($username).".inc.php";

	if(!is_writable($file))
		$error	.= "The file $file was not writeable!";

	include $file;

	$out_hash	= $pass_hash;
	$image_path	= "comm_$photo.jpg";

	if($old_pass != "" && $new_pass != "")	//if they want to change their password and the new password isn't blank
		if(check_pass($username, $old_pass) && ($new_hash = md5($new_pass)) == md5($confirm_pass))	//if the old password is valid & correclty confirmed
			$out_hash	= $new_hash;

	$debug_info	.= "<b>Password</b>\$out_hash=$out_hash\n<br />\$new_hash=$new_hash\n<br />\$pass_hash=$pass_hash\n<br />\n";
	$debug_info	.= "<b>Spiel</b>\Spiel=$content\n<br />\n";
	$debug_info	.= "<b>Photo</b>\Photo=$image_path\n<br />\n";
	$debug_info	.= "<b>Gender</b>\$new_gender=$new_gender\n<br />\n";
	$debug_info	.= "<b>Name</b>\$new_name=$new_name\n<br />\n";

	$out_val	= "<?php\n\n\$pass_hash	= \"$out_hash\";\n\n\$image_path	= \"$image_path\";\n\n\$gender	= \"$new_gender\";"
				."\n\n\$spiel	= \"$content\";\n\n\$name	= \"$new_name\";\n\n?>";

	if(!file_put_contents($file, $out_val))
		$error	.= "\nFile write failed\n<br />\n";
}
elseif($type == "Webmaster" && $username == "webmaster")
{
	if(isset($target) && $target != "")
	{
		for($i = 0; $job = $job_list[$i]; $i++)	//cycle through everyone you might want to reset the password of
		{
			if(($target[$job] || $target["Whole Committee"]) && !in_array(strtolower($job), array("chaplain", "committee")))	//if theres a match then include them on the to line
			{
				reset_pass($job);
				$reset_list	.= "$job\n";
			}
			$debug_info	.= "\$target[$job]=$target[$job]\n<br />";
		}
		if(!(isset($error) && $error != "") || $debug)
			send_mail("Webmaster", "$website_name Website Password Reset", "The following passwords have been reset successfully:\n\n$reset_list",
				"From: $website_name Webmaster <$webmaster_email>");
	}
	$debug_info	.= "\$target=$target\n<br />\$sect=$sect\n<br />";
	if(isset($sect) && $sect != "")
	{
		$toclist	= array();
		foreach($sect as $sect_key => $tmpval)
		{
			if($tmpval)
				array_push($toclist, $sect_key);
				
			$debug_info	.= "\$sect[$sect_key]=$sect[$sect_key],	\$tmpval	= $tmpval\n<br />";
		}
		if(!file_put_contents("Site_Files/admin.inc.php", "<?php\n\$toclist	= array('".implode("', '", $toclist)."');\n?>"))
			$error	.= "\nFile write failed\n<br />\n";
	}
}
else	//something somewhere went horribly wrong
{
	$error	.= "\nUnrecognised edit type!\n<br />\n";
}
}	//end if content != ''

if($debug) {
	echo "\n<br />POST:\n<br />";
	print_r($_POST);
	echo "\n<br />GET:\n<br />";
	print_r($_GET);
}

$debug_info	.= "\$timestamp=$timestamp\n<br />\$content=$content\n<br />\$output=$output\n<br />\n";
$header_link	.= "#$type";

if((isset($error) && $error != "") || $debug)
	include "handler.php";
else
	header("Location: Admin.php?success=1$header_link");
?>