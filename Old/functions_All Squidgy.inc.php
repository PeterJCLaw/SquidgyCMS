<?php function email($a) { 	return $a; } ?>
<?php
	//Peter's functions

/* This function aliases the one immediately below */
function email_them($text, $gender, $address, $subject, $cc, $bcc, $body)
{
	return email_them_PJCL($text, $gender, $address, $subject, $cc, $bcc, $body, 'mailto');
}

/* This function prints an email to link */
function email_them_PJCL($text, $gender, $address, $subject, $cc, $bcc, $body, $link_type)
{
	global $my_email_addr;

	if($link_type == "mailto")
	{
		if(!stristr($address,"@"))	//if they didn't specify an actual address assume that its for me
			$address	= $my_email_addr;

		$out_val	=  "<a href=\"mailto:$address".((($subject != "0") || ($cc != "0") || ($bcc != "0") || ($body != "0")) ? "?" : "");
;
	}
	if($link_type == "form")
		$out_val	=  "<a href=\"?mode=contact".((($subject != "0") || ($cc != "0") || ($bcc != "0") || ($body != "0")) ? "&amp;" : "");

	if($subject != "0")
	{
		$subject = htmlspecialchars($subject);	//fix the spaces into html encoded spaces (%20)
		$out_val	.=  "subject=$subject";
		if(($cc != "0") || ($bcc != "0") || ($body != "0"))
			$out_val	.=  "&amp;";
	}

	if($cc != "0")
	{
		$out_val	.=  "cc=$cc";
		if(($bcc != "0") || ($body != "0"))
			$out_val	.=  "&amp;";
	}

	if($bcc != "0")
	{
		$out_val	.=  "bcc=$bcc";
		if($body != "0")
			$out_val	.=  "&amp;";
	}

	if($body != "0")
	{
		$body = htmlspecialchars($body);
		$out_val	.=  "body=$body";
	}

	$out_val	.=  "\" title=\"Send $gender an email\">$text</a>";
	return	$out_val;
}

//converts to a valid id tag
function id_convert($tag)
{
	global $_space, $_slash;
	$tag	= str_replace(" ", $_space, $tag);
	$tag	= str_replace("/", $_slash, $tag);
	return $tag;
}

//compare the path passed to the current file by shrinking the current file to the same length as the passed file, then see if they're the same
function path_compare($p_p)
{
	global $debug_info, $curr_file;

	$p_p	= str_replace(".//", "", $p_p);

	$debug_info .= "\$p_p=$p_p\n<br />\$curr_file=$curr_file\n<br />\n";

	if($p_p == $curr_file || $p_p == "./")
		return TRUE;

	$length		= strlen($p_p);

	$curr_s		= substr($curr_file, 0, $length);

	$debug_info .= "\$curr_s=$curr_s\n<br />\$length=$length\n<br />\n";

	return ($p_p == $curr_s);	//if they match then the folder gets expanded
}

//recursive function to explore the file / folder structure beneath retruns blank if nothing of note is found
function file_explore($path, $tabs, $ajax)
{
	global $debug_info, $curr_file;

	$path_id = id_convert($path);

	if($path == './') {	//top level
		$path_id = '';
		$retval  = "<div id=\"FE_preload\"><ul><li class=\"collapsed\"></li><li class=\"expanded\"></li><li class=\"FE_empty\"></li></ul></div>";
	} else
		$retval	= "";

	$paths_match = path_compare($path, $curr_file);
	$new_css	= TRUE;

	$display	= ($paths_match || $new_css) ? "" : " style=\"display: none;\"";

	$debug_info	.= "\$paths_match=".($paths_match ? 'TRUE' : 'FALSE' )."\n<br />\$display=$display\n<br />\n";

	$dir_contents = dirAllList($path);

	if($dir_contents) {	//if there's something to show
		if($ajax < 2)	//if not being called from get_file_tree.php
			$retval	.= "\n$tabs<ul id=\"FE_$path_id\" class=\"File_Explore\"$display>\n";

		if($ajax && $tabs != "" && !$paths_match)	//if ajax is enabled and not top level and paths don't match
			$retval	.= "	$tabs<li>Loading File Tree...</li>\n";
		else {
			$ajax	= round($ajax/2);
			foreach($dir_contents as $item_name)	//for each of the results in this folder
			{
				$href		= $item		= ($path_id == "" ? "" : "$path/").$item_name;
				$item_id	= id_convert($item);
				$curr_item	= ($item == $curr_file) ? TRUE : FALSE;
				$item_sub_val = $li_insert = "";
				$debug_info	.= "\$item=$item\n<br />\$item_id=$item_id\n<br />\n";

				if (is_file($item) || is_dir($item)) {
					if (is_dir($item)) {	//if its a folder
						$tit			= "Go to $item_name";
						$li_ins_class	= "FE_empty";
						$item_sub_val	= file_explore($item, $tabs."		", $ajax);
						if($item_sub_val != '') {
							$li_ins_class	= ($paths_match ? (path_compare($item, $curr_file) ? "expanded" : "collapsed") : "collapsed");
							$item_sub_val	= ($curr_item ? "" : "&nbsp;&nbsp;<a href=\"?dir=$href\" title=\"$tit\">=></a>")."$item_sub_val\n	$tabs";
							$tit			= "Expand $item_name";
							$href			= "javascript:toggle_FE('FE_$item_id', 'FE_LI_$item_id');\" class=\"js_link";
						}
						$li_insert		= " id=\"FE_LI_$item_id\" class=\"$li_ins_class\"";
						$debug_info		.= "\$item_sub_val=$item_sub_val\n<br />\$li_insert=$li_insert\n<br />\n";
					} else {
						$item_name	= returnFileName($item_name); //gives us just the name (no extension) of the file
						$tit		= "Go to $item_name";
					}
					$thing_name	= "<a href=\"$href\" title=\"$tit\">$item_name</a>";

					if($curr_item)
						$thing_name	= "<b>$thing_name</b>";

					$retval	.= "	$tabs<li".$li_insert.">".$thing_name . $item_sub_val."</li>\n";
				}
			}
		}

		if($ajax < 2)	//if not being called from get_file_tree.php
			$retval	.= "$tabs</ul>";
	}
	else
		return '';

	$debug_info	.= "\$path=$path\n<br />\$path_id=$path_id\n<br />\$curr_file=$curr_file\n<br />\$tabs=|$tabs|\n<br />\n";

	return $retval;
}
?>