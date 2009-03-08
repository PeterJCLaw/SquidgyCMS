<?php
	$_Admin_list["News"]	= "Add an news article to the news section of the site";
	$_Admin_list["Event"]	= "Add an Event to the Events list";
	$_Admin_list["Music"]	= "Edit the Music Page";
	$_Admin_list["Manage"]	= "Manage website pages and articles";
	$_Admin_list["Article_based_Page"]	= "Create or change a page on the website";
	$_Admin_list["Article"]	= "Manage website pages and articles";
	$_Admin_list["Profile"]	= "Change your personal profile";
	$_Admin_list["Webmaster"]	= "Reset committee passwords or make changes to the Admin area of the site";
	$_Admin_list["Files"]	= "View restricted committee files";

	$toclist	= array("Webmaster");

	include 'functions_FSPHP.inc.php';
	include 'Head.inc.php';
	if(!(isset($content_disable[$page_n]) && $content_disable[$page_n]) || $debug)
	{
?>
<?php
function committee_photo_select($curr_img)
{
	global $debug_info;
	$img_list	= Filtered_Dir_List("Site_Images", "comm_");
	$selected	= "\" selected=\"selected";
	$debug_info .= "\$curr_img=$curr_img\n<br />\n";
	foreach($img_list as $image)
	{
		$debug_info .= "\$image=$image\n<br />\n";
		$image	= str_replace(".jpg", "", $image);
		$sel2	= "";
		if($image == str_replace(array("comm_", ".jpg"), "", $curr_img))
		{
			$sel2	= $selected;
			$selected	= "";
		}
		echo "<option value=\"$image$sel2\">$image</option>\n				";
	}
	echo "<option value=\"none$selected\">None</option>\n";
	return;
}

/* This function generates the News item part of the Admin Page */
function News()
{
	global $debug, $debug_info;
?>
			<table><tr>
			<th>Valid Until:</th><td>
				<?php
				list($day, $month, $year) = split("[./-]",date("j.n.Y", strtotime('+7 days')));

				$debug_info .= "\$day=$day,	\$month=$month,	\$year=$year\n<br />\n";

				genDateSelector("", $day, $month, $year);
				?>
				<script type="text/javascript">
					<!--
					// fix number of days for the $month/$year that you start with\n"
					ChangeOptionDays(document.forms['News_form'], ''); // Note: The 2nd arg must match the first in the call to genDateSelector above.\n"
					// -->
				</script>
				</td></tr></table>
<?php return;
}

/* This function generates the Event part of the Admin Page */
function Event()
{
	global $debug, $debug_info;
?>
			<table><tr>
			<th>Event Start:</th><td>
				<?php
				genTimeSelector("start_");
				echo "			&nbsp;&nbsp;\n";
				genDateSelector("start_", $day, $month, $year);
				?>
				<script type="text/javascript">
					<!--
					// fix number of days for the $month/$year that you start with\n"
					ChangeOptionDays(document.forms['Event_form'], 'start_'); // Note: The 2nd arg must match the first in the call to genDateSelector above.\n"
					// -->
				</script>
				</td></tr><tr>
				<th>Event End:</th><td>
				<?php
				genTimeSelector("finish_");
				echo "			&nbsp;&nbsp;\n";
				genDateSelector("finish_", $day, $month, $year);
				?>
				<script type="text/javascript">
					<!--
					// fix number of days for the $month/$year that you start with\n"
					ChangeOptionDays(document.forms['Event_form'], 'finish_'); // Note: The 2nd arg must match the first in the call to genDateSelector above.\n"
					// -->
				</script>
				</td></tr><tr>
				<th><label for="event_title">Event Title:</label></th>
					<td><input type="text" name="event_title" id="event_title" /></td>
				</tr></table>
<?php return;
}

/* This function generates the Whole Page HTML editing part of the Admin Page */
function Music()
{ }

/* This function generates the Site Page Managment part of the Admin Page */
function Manage()
{
global $GEN_art, $GEN_pages, $debug, $debug_info;
?>
			<table id="admin_manage_tbl"><tr>
				<th>Edit Page:</th><th class="R">Delete:</th>
				<th>Edit Article:</th><th class="R">Delete:</th>
				</tr><?php
$check	= '<input type="checkbox" class="tick" name="del[';
for($i=0; isset($GEN_pages[$i]) || isset($GEN_art[$i]); $i++)
{
	$del_box_p = $page_link = $del_box_a = $del_box_p = $page_name_tmp = $art_name_tmp = '';
	if(!empty($GEN_pages[$i]))
	{
		$page_name_tmp	= substr(stristr($GEN_pages[$i], "-"), 1);
		$del_box_p	= $check.$GEN_pages[$i].'_page]" />';
		$page_link	= "?page_req=".$GEN_pages[$i];
	}
	if(!empty($GEN_art[$i]))
	{
		$art_name_tmp	= substr(stristr($GEN_art[$i], "-"), 1);
		$del_box_a	= $check.$GEN_art[$i].'_art]" />';
		$art_link	= "?art_req=".$GEN_art[$i];
	}

	if($page_name_tmp == "index")
	{
		$page_name_tmp	= "Home";
		$del_box_p		= "";
	}

	echo '
	<tr>
		<td><a href="'.$page_link.'#Page" title="Edit the \''.$page_name_tmp.'\' page">'.$page_name_tmp.'</a></td>
		<td class="R">'.$del_box_p.'</td>
		<td><a href="'.$art_link.'#Article" title="Edit the \''.$art_name_tmp.'\' article">'.$art_name_tmp.'</a></td>
		<td class="R">'.$del_box_a.'</td>
	</tr>';
}
?>
			</table>
<?php return;
}
/* This function generates the Page Editing part of the Admin Page */
function Article_based_Page()
{
	global $GEN_art, $GEN_pages, $page_req, $page_n, $debug, $debug_info;
	$i = $num_rows = 1;
	$OUT_P = $out = $page_head_title = "";

	foreach($GEN_pages as $tmpval)
	{
		$file	= $tmpval;
		$tmpval = substr(stristr($tmpval, "-"), 1);
		$OUT_P	.= "\n						<option value=\"$file\">$tmpval</option>";
	}

	foreach($GEN_art as $tmpval)
	{
		$file	= $tmpval;
		$tmpval = substr(stristr($tmpval, "-"), 1);
		$out	.= "\n						<option value=\"$file\">$tmpval</option>";
	}
	$last	= "\n						<option value=\"0\" selected=\"selected\">None</option>\n					";


	if(isset($page_req) && in_array($page_req, $GEN_pages))
	{
		include "Site_Files/".$page_req."_page.inc.php";

		$page_head_title	= substr(stristr($page_req, "-"), 1);
		$page_id	= $page_req;
		$page_num	= str_replace(stristr($page_req, "-"), "", $page_req);
		$num_rows	= round(count($page_layout)/2);
		$last		= str_replace(" selected=\"selected\"", "", $last);

		foreach($page_layout as $tmpval)
		{
			$out_p[$i]	= str_replace("value=\"$tmpval\"", " value=\"$tmpval\" selected=\"selected\"", $out.$last);
			$i++;
		}
	} else {
		$out_p[1]	= $out_p[2]	= $out.$last;
		$page_id	= $page_num	= 1+count($GEN_pages);
	}
?>
			<table id="admin_page_tbl"><tr>
				<th><label for="page_title" title="The title of the page">Page Title:</label></th>
				<td><input type="<?php echo ($page_head_title == "index" ? "hidden" : "text"); ?>" name="page_title" title="The title of the page" value="<?php
					 echo ($page_head_title != "" ? $page_head_title : "New Page"); ?>" /><?php echo ($page_head_title == "index" ? "Home" : "")."\n"; ?>
					<input type="hidden" name="num_rows" id="num_rows" value="<?php echo $num_rows; ?>" />
					<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
					</td>
			</tr><tr>
				<th><label for="page_sel_td" title="Select an page to edit">Select Page:</label></th>
				<td id="page_sel_td">
					<span id="page_change" style="display: none;">
						<?php echo $page_req."\n"; ?>
						<a onclick="show_change('page', 0);">Change Page</a>
					</span>
					<span id="page_sel_span"><select id="page_select">
				<?php $OUT_P	= str_replace("value=\"$page_req\"", "value=\"$page_req\" selected=\"selected\"", $OUT_P);	//select the appropriate article
					$OUT_P		= str_replace(">index<", ">Home<", $OUT_P);
					echo $OUT_P."\n						<option".(!$page_req ? " selected=\"selected\"" : "").">New Page</option>\n					"; ?>
					</select>
					<input type="button" onclick="redir('Page', <?php echo $page_num.", '$page_n'"; ?>);" value="Go" id="Go1" />
					</span>
				</td>
			</tr><tr>
				<th>Page Articles:</th><td class="center"><a onclick="add_article_row()">Add a row of articles</a></td>
			</tr><tr>
				<td colspan="2" class="center">
					<div id="article_select_div">
						<p id="art_sel_p">
<?php for($i=1; $i <= 2*$num_rows; $i+=2) { $k = $i+1;
				echo ($i>1 ? "<p>":"")."<select name=\"article_id_$i\" id=\"article_id_$i\">".$out_p[$i]."</select>"
						."<select name=\"article_id_$k\" id=\"article_id_$k\">".$out_p[$k]."</select>"
						."\n						</p>";
} ?>
					</div>
				</td>
			</tr></table>
<?php return;
}
/* This function generates the Article Editing part of the Admin Page */
function Article()
{
	global $GEN_art, $GEN_pages, $art_req, $page_n, $debug, $debug_info, $content;
	$num_articles	= 1+count($GEN_art);
	$out	= "";

	foreach($GEN_art as $tmpval)
	{
		$file	= $tmpval;
		$tmpval = substr(stristr($tmpval, "-"), 1);
		$out	.= "\n						<option value=\"$file\">$tmpval</option>";
	}

	if(isset($art_req) && in_array($art_req, $GEN_art))
		include "Site_Files/".$art_req."_art.inc.php"; ?>
			<table id="admin_article_tbl"><tr>
				<th class="L"><label for="article_title" title="The title of the article">Article Title:</label></th>
				<td><input type="hidden" name="article_id" value="<?php echo ($art_req ? $art_req : "new$num_articles"); ?>" />
					<input type="text" class="text" name="article_title" id="article_title" title="The title of the article" value="<?php
					 echo (!isset($title) || empty($title) ? "New Article" : $title); ?>" /></td>
				</tr><tr>
				<th><label for="article_sel_td" title="Select an article to edit">Article:</label></th>
				<td id="article_sel_td">
					<span id="article_change" style="display: none;">
						<?php echo $art_req."\n"; ?>
						<a onclick="show_change('article', 0);">Change Article</a>
					</span>
					<span id="article_sel_span"><select id="article_select">
				<?php echo str_replace("value=\"$art_req\"", "value=\"$art_req\" selected=\"selected\"", $out)	//select the appropriate article
						."\n						<option".(!$art_req ? " selected=\"selected\"" : "").">New Article</option>\n					"; ?>
					</select>
					<input type="button" onclick="redir('Article', <?php echo (isset($art_req) ? str_replace(stristr($art_req, "-"), "", $art_req) : $num_articles).", '$page_n'"; ?>);" value="Go" id="Go2" />
					</span>
				</td>
			</tr></table>
<?php return;
}
/* This function generates the Profile part of the Admin Page */
function Profile()
{
	global $name, $image_path, $gender;
?>
			<table><tr>
				<th><label for="new_name" title="Your name as you would like it to appear on the site">Name:</label></th>
					<td colspan="2">
						<input type="text" id="new_name" name="new_name" value="<?php echo stripslashes($name); ?>" title="Your name as you would like it to appear on the site" />
					</td>
					<td rowspan="5" id="pic_cell">
						<a id="pic_preview_link" href="Site_Images/<?php echo comm_pic($image_path); ?>" title="Your image preview">
						<img id="pic_preview" src="Thumbs/<?php echo comm_pic($image_path); ?>" title="Your image preview, click to view larger" alt="Your image preview" width="75px" height="90px" />
						</a>
					</td>
				</tr><tr>
					<th><label for="photo_change" title="Change your picture">Picture:</label></th>
					<td>
						<select id="photo_change" name="photo" title="Change your picture" onchange="change_pic(this.value)">
						<?php committee_photo_select($image_path); ?>
						</select>
					</td>
					<td rowspan="4" id="gender_cell">
						<input type="radio" class="radio" id="gender_male" name="new_gender" value="him" <?php echo ($gender == "him" ? "checked=\"checked\" " : ""); ?>/>
						<label for="gender_male">Male</label>
						<br />
						<input type="radio" class="radio" id="gender_female" name="new_gender" value="her" <?php echo ($gender == "her" ? "checked=\"checked\" " : ""); ?>/>
						<label for="gender_female">Female</label>
					</td>
				</tr><tr id="pass_0" style="display: none;">
					<th>Password:</th>
					<td><a class="js_link" onclick="pass_change('show');">Change your password</a></td>
				</tr><tr id="pass_1">
					<th><label for="old_pass" title="Only fill in if changing your password">Old Password:</label></th>
					<td><input type="password" id="old_pass" name="old_pass"  title="Only fill in if changing your password" /></td>
				</tr><tr id="pass_2">
					<th><label for="new_pass" title="Only fill in if changing your password">New Password:</label></th>
					<td><input type="password" id="new_pass" name="new_pass"  title="Only fill in if changing your password" /></td>
				</tr><tr id="pass_3">
					<th><label for="confirm_pass" title="Only fill in if changing your password">Confirm Password:</label></th>
					<td><input type="password" id="confirm_pass" name="confirm_pass"  title="Only fill in if changing your password" /></td>
				</tr></table>
<?php return;
}
/* This function generates the Webmaster's part of the Admin Page */
function Webmaster()
{
	global $target, $job_list, $_Admin_list, $toclist;
	$target = "";
?>
			<h4>Reset Passwords:</h4>
<?php print_tickboxes($job_list, "right"); ?>
			<h4>Change Available Admin Sections:</h4>
			<table id="admin_manage_sect_tbl"><tr>
				<th class="L">Section Name:</th><th class="M">Section Description:</th><th class="R">Enabled:</th>
				</tr><?php
foreach($_Admin_list as $section => $sect_title)
{
	if($section != "Webmaster")
	{
		if(in_array($section, $toclist))
			$checked	= " checked=\"checked\"";
		else
			$checked	= "";

		if(in_array($section, array("Manage", "Page", "Article")))
			$jsonchange	= " onChange=\"manage_group_tick(this)\"";
		else
			$jsonchange	= "";

		$sect_box	= "<input type=\"checkbox\" class=\"tick\" name=\"sect[".$section."]\"$jsonchange id=\"_enable_$section\"$checked />";

		echo "<tr>\n					<td class=\"L\"><label for=\"_enable_$section\">$section</label></td><td class=\"M\">$sect_title</td>"
			."\n					<td class=\"R\">$sect_box</td>\n				</tr>";
	}
}
?>
			</table>
<?php


	return;
}


/* This is the actual page below this point */
if(!$logged_in)
	print_logon_form();
else
{
	if(isset($page_req) && !$page_req)
		unset($page_req);
	if(isset($art_req) && !$art_req)
		unset($art_req);

	if(is_readable("Site_Files/admin.inc.php"))
		include "Site_Files/admin.inc.php";

	include "Users_Info/".info_name($username).".inc.php";
	$firstname = first_name($name);

	echo '
	<div id="admin">
		<div class="admin_head">
			<span class="f_left" id="welcome">Welcome, '.$firstname.'.</span>
';
	if(isset($success)) echo print_success($success)."\n"; ?>
		</div>
		<p>Please note that only changes to the one form that you submit will be saved.</p>
		<table id="admin_TOC"><tr><?php

			if(strtolower($username) != "webmaster" && in_array("Webmaster", $toclist))
				unset($toclist[array_search("Webmaster", $toclist)]);
			elseif(strtolower($username) == "webmaster" && !in_array("Webmaster", $toclist))
				array_push($toclist, "Webmaster");

			$tocwidth = 100 / count($toclist);
			$script_val = implode('", "', $toclist);

			if(function_exists('Filtered_Dir_List') && is_readable("Site_Files"))
			{
				$GEN_pages	= Filtered_Dir_List("Site_Files", "_page.inc.php");
				$GEN_art	= Filtered_Dir_List("Site_Files", "_art.inc.php");
			} else {
				$GEN_pages	= array();
				$GEN_art	= array();
			}

			foreach($toclist as $value)
				echo "\n			<td style=\"width: $tocwidth%;\"><a id=\"${value}_link\" href=\"#$value\" onclick=\"switch_tabs('$value');\">$value</a></td>";
?>

		</tr></table>
		<script type="text/javascript">
		<!--
			divList	= ["<?php echo $script_val; ?>"];
			function load_admin(cur_div, form_name, reset)
			{
				pass_change("hide");
				switch_tabs(cur_div);
				if(form_name == '<?php echo $admin_form_id; ?>')
					load(form_name, reset);
				return;
			}
		//-->
		</script>
		<div class="gen_txt">
<?php
// loop through all the posible sections, only act on those that are selected
foreach($_Admin_list as $section => $sect_title)
{
	if(in_array($section, $toclist))
	{
?>
<div class="admin_div" id="<?php echo $section."\"><h3 id=\"${section}_h3\">".$section; ?></h3>
<?php	if($section != "Files")
		{?>
			<form id="<?php echo $section ?>_form" action="admin_handler.php" method="post" onsubmit="return Validate_On_Admin_Submit('<?php echo $section; ?>')">
			<div class="admin_form_head">
<?php if($debug)	echo "<input type=\"hidden\" name=\"debug\" value=\"$debug\" />\n"; ?>
				<span class="f_left"><?php echo $sect_title; ?>:</span>
				<span class="f_right">
					<input type="submit" name="submit" value="Save - <?php echo $section; ?>" />
					<br />
					<input type="reset" value="Reset - <?php echo $section; ?>" />
				</span>
 			</div>
 			<div class="admin_form">
<?php
	if(function_exists($section))
		$section();
	else
		echo "The requested Section does not exist!";

	unset($textarea);
	switch($section)
	{
		case "News":
		case "Social":
			$textarea	= "";
			break;
		case "Music":
			$textarea	= file_get_contents($music_file);
			break;
		case "Article":
			$textarea	= stripslashes($content);
			break;
		case "Profile":
			$textarea	= stripslashes($spiel);
			break;
		default:
			break;
	}
?>
<input type="hidden" name="type" value="<?php echo $section; ?>" />
<?php if(isset($textarea)) {	//not all page bits need textareas ?>
				<textarea name="<?php echo $section; ?>_content" id="<?php echo $section; ?>_content" rows="12" cols="71"><?php echo $textarea;?></textarea>
				<?php } ?>
			</div>
			</form>
			</div>
<?php } else { ?>
				View restricted files by clicking <a href="<? echo $Admin_Files_link; ?>" title="View Restricted Files">here</a>.
		</div>
<?php }	//end if not Files
	} //end if in toclist
}	//end foreach section in Admin-list
?>
			</div><!-- end gen_txt div -->
			</div><!-- end admin div -->
<?php }	// end if logged in
} else { ?>
		This page is under development, come back later to see the vast improvemnts being implemented!
<?php }
	include 'Foot.inc.php';
?>