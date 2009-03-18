<?php
/* This function gets basic info abouot the current page */
function get_page_basics() {
	$SN_arr	= explode("/", $_SERVER['SCRIPT_NAME']);	//explode so I can deal with the bits individually
	$RU_arr	= explode("/", $_SERVER['REQUEST_URI']);

	$page	= $SN_arr[count($SN_arr)-1];	//get the name of this page from its path, then lose the extension
	unset($SN_arr[count($SN_arr)-1]);	//unset the last value - that's the page name - as I don't want it in the absolute ref

	if($SN_arr[1] == "~".$RU_arr[1])
		$SN_arr[1] = $RU_arr[1];	//dump the tilde if we don't want it NB: check this on other systems

	return array(implode("/", $SN_arr)."/", $page, returnFileName($page));
}

/* prints an admin section, if it exsists */
function get_TOClist() {
	global $pages_file;
	unset($Site_TOClist);

	if(is_readable($pages_file))
		include $pages_file;	//Site_TOClist

	if(!isset($Site_TOClist))
		$Site_TOClist	= array();

	unset($Site_TOClist['1-Home']);

	if(!isset($Site_TOClist['Home']))	//if neither is set
		$Site_TOClist	= array_merge(array('Home' => array('weight' => -10)), $Site_TOClist);

	$Site_TOClist	= array_merge($Site_TOClist, array('Admin'  => array('weight' => 100)));

	return $Site_TOClist;
}

/* prints an admin section, if it exsists */
function print_Admin_Section($val) {
	if(!empty($val['obj']) && method_exists($val['obj'], 'submit') && method_exists($val['obj'], 'printFormAdmin')) {
		$val['obj']->printFormHeader();
		$val['obj']->printFormAdmin();
		$val['obj']->printFormFooter();
	} elseif(!empty($val['no_submit']) && $val['no_submit'])	//if there's no submission - eg just providing a link somewhere
		$val['obj']->printFormAdmin();
	else
		echo "	<p>The requested section does not exist!</p>";

	return;
}

/* parses the squidgyCMS wiki-style pages and makes html */
function SquidgyParser($page_file, $start = 0, $finish = 0) {
	global $debug, $debug_info;
	if(is_readable("Modules/Module.php"))
		require_once("Modules/Module.php");
	else
		return FALSE;

	$page	= file_get_contents($page_file);
	$len	= strlen($page);

	$debug_info	.= "Parser: start = $start, finish = $finish, ";

	if(!empty($start) || !empty($finish)) {	//then we need to shorten it
		if(!empty($start))
			$start_pos	= strpos($page, $start);
		else
			$start_pos	= -1;

		if(!empty($finish))
			$finish_pos	= strpos($page, $finish);
		else
			$finish_pos	= -1;

		$debug_info	.= "start_pos = $start_pos, finish_pos = $finish_pos<br />\n";

		if($start_pos >= $len || ($start_pos >= $finish_pos && $finish_pos != -1) || $start_pos === $finish_pos)
			return FALSE;

		if($finish_pos == -1)
			$page	= substr($page, (int)$start_pos);
		elseif($start_pos == -1)
			$page	= substr($page, 0, (int)$finish_pos);
		else
			$page	= substr($page, (int)$start_pos, ((int)$finish_pos)-((int)$start_pos));
	}

	$debug_info	.= "<br />\n";

	$len	= strlen($page);
	$i	= 0;
	while(!(strpos($page, '[[Block::') === FALSE || strpos($page, ']]') === FALSE) && $i<$len) {	//keep going until you run out of custom bits

		$block_call	= substr($page, strpos($page, '[[Block::')+9, (strpos($page, ']]') - strpos($page, '[[Block::') - 9) );	//grab the call from the source

		list($type)	= explode("::", $block_call);	//get the type
		list($module, $method)	= explode("-", $type);

		$args	= substr($block_call, strlen($type)+2);	//grab the arguments
		if(!empty($args))
			$args	= explode("||", $args);	//sort them into an array
		else
			$args	= array();	//or make a blank array

		$debug_info	.= "block_call = '$block_call', i = '$i', type = '$type', args = '".implode(", ", $args)."'\n<br />\n";
		if($debug > 1) {
			echo "args:\n<br />\n".print_r($args, true)."\n<br />\n";
		}

		$block_html	= '';

		if(is_readable("Modules/$module.block.class.php") || is_readable("Sites/Custom_Modules/$module.block.class.php")) {
			if(is_readable("Modules/$module.block.class.php"))	//grab the class, try the standard place first, then the custom one
				require_once("Modules/$module.block.class.php");
			else
				require_once("Sites/Custom_Modules/$module.block.class.php");

			$block	= "Block$module";

			if(class_exists($block)) {
				$block_obj	= new $block();

				if(method_exists($block_obj, $method))
					$block_html	= $block_obj->$method($args);
				else
					log_info("Block '$block' has no method '$method'");

				if(empty($block_html))
					log_info("Block method '${block}->$method' returned nothing");
			}
			else
				log_info("Module '$module' has no block '$block'");
		}
		else
			log_info("Module '$module' does not exist");

		$page	= str_replace("[[Block::$block_call]]", $block_html, $page);

		$i+=9;
	}
	return $page;
}

/* grab a data file & convert it to a 2D array using the passed column names */
function get_file_assoc($path, $cols)
{
	if(!is_readable($path))	//if we can't read it then bail
		return array();
	$file	= file_rtrim($path);	//read the info into an array, one element per line
	$out	= array();
	foreach($file as $line) {
		$line_data	= array_combine($cols, explode('|:|', $line));
		if(!empty($line_data))
			array_push($out, $line_data);
		else
			log_info("Line ($line) is empty ($line_data)");
	}
	return $out;
}

/* get a file with no whitespace on the right, useful as PHP < 5 doesn't have FILE_IGNORE_NEW_LINES */
function file_rtrim($path)
{
	if(floatval(phpversion()) >= 5)
		return file($path, FILE_IGNORE_NEW_LINES);
	return array_map('rtrim', file($path));
}

/* add this PHP5 function if needed */
if(!function_exists('array_combine')) {
function array_combine($keys, $vals)
{
	if(!is_array($keys) || !is_array($vals) || empty($keys) || empty($vals) || count($keys) != count($vals))
		return FALSE;
	$r	= array();
	reset($vals);
	reset($keys);
	$val = current($vals);
	foreach($keys as $key) {
		$r[print_r($key, true)] = $val;
		$val = next($vals);
	}
	return $r;
}
}

/* get the id number of a generated page from its fileid (ie without the extension) */
function get_GEN_id($name)
{
	return substr($name, 0, strpos($name, "-"));
}

/* get the id number of a generated page from its fileid (ie without the extension) */
function get_GEN_title($name)
{
	$name	= urldecode($name);
	return substr($name, strpos($name, "-")+1);
}

/* function adapted from http://www.prodevtips.com/2008/01/06/sorting-2d-arrays-in-php-anectodes-and-reflections/ */
function multi2dSortAsc(&$arr, $key)
{
	if(empty($key) || empty($arr))
		return;
	$sort_col	= array();
	foreach($arr as $sub)
		$sort_col[]	= $sub[$key];
	array_multisort($sort_col, $arr);
}

/* compare items by filename id */
function cmp_by_filename_id($a, $b)
{
	$Aid	= get_GEN_id($a);
	$Bid	= get_GEN_id($b);

	if($Aid == $Bid)
		return 0;
	else
		return $Aid > $Bid ? 1 : -1;
}

/* get the next id for the appropriate item */
function get_next_id($where, $filter)
{
	global $page_file, $logged_in, $debug_info;

	$list	= Filtered_Dir_List($where, $filter);

	$id_list	= array();

	foreach($list as $val) {
		array_push($id_list, get_GEN_id($val));
	}

	return max($id_list)+1;
}

/* This function sends emails to the appropriate recipient
 *	to:	array or string contaning recipient identifiers - full email address OR
	short email that can be converted using $comm_email_prefix.email($recip).$comm_email_postfix
*/
function send_mail($to, $subject, $message, $headers)
{
	global $committee_email, $comm_email_postfix, $website_name, $debug_info;

	$to_string	= is_array($to) ? implode(", ", $to) : $to;	//convert it to a string for analysis if needed
	$bed_to	= !strpos($to_string, "@");	//find if the passed email has an @ char
	$separate_emails	= (strpos($committee_email, "@") ? FALSE : TRUE);

	if($separate_emails && $comm_email_postfix == "" && $bad_to)
		return FALSE;	//if the email passed is bad, and no general emails are specified, prefix is optional

	$to_list	= array();

	if(is_string($to))	//if it's a string convert it to an array
		$to	= array($to);

	if(!is_array($to))
		return FALSE;

	foreach($to as $recip)
	{
		if($separate_emails && (strpos($recip, "@") === FALSE))	//if separate emails and there's no @ char: make a real email
			$recip	= "$website_name $recip <".email_addr($recip).">";

		array_push($to_list, $recip);	//make list of ppl to put in subj line
	}

	if($separate_emails)	//if separate emails
		$_to_	= implode(", ", $to_list);
	else {
		$_to_	= $committee_email;
		sort($to_list);
		$subject	= "[FAO: ".implode(", ", $to_list)."]".$subject;
	}

	$debug_info	.="\n[send_mail]\n<br />\$to=$to\n<br />\$to_string=$to_string\n<br />\$_to_=$_to_\n<br />\$subject=$subject\n<br />\$message=$message\n<br />"
		."\$headers=$headers\n<br />\$separate_emails=".($separate_emails ? "True" : "False")."\n<br />\n[/send_mail]\n";

	return mail($_to_, $subject, $message, $headers);
}

/* This function determines if the committee picture passed is valid, if it is it returns it, else returns a standin image */
function comm_pic($image_path)
{
	if($image_path == "" || !is_readable("Site_Images/$image_path"))
		$image_path	= "Unknown.jpg";

	return $image_path;
}

/* This function prints the tickboxes */
function print_tickboxes($item_list, $tick_side = '')
{
	global $target, $debug_info, $whole_com_elem_id;
	$left = FALSE;
	if($tick_side == "left")	//if in doubt it goes on the right
		$left = TRUE;

	echo "	<ul class=\"tick_list ".($left ? 'left' : 'right')."\">\n";

	$mini_group	= in_array("Committee", $item_list);

	for($i = 0, $count = 0; $i < count($item_list); $i++)	//spit as many items as there are
	{
		if($item_list[$i] == "Committee")
			$item	= "Whole Committee";
		else
			$item	= $item_list[$i];

		$check_it	= (($target == $item || ($target == "Whole Committee" && $i < $whole_com_elem_id)) ? ' checked="checked"' : "");
		$onclick	= ($i <= $whole_com_elem_id ? ' onchange="group_tick_2(this)" onclick="group_tick_2(this)"' : "");
		$w_style	= ($i == $whole_com_elem_id ? ' style="font-weight: bold;" id="_Whole_label" for="_Whole"' : "");
		$_whole	= ($i == $whole_com_elem_id ? ' id="_Whole"' : "");

		$debug_info	.= "\$check_it = $check_it\n<br />\$item = $item\n<br />\$target = $target\n<br />\$i = $i\n<br />\$whole_com_elem_id = $whole_com_elem_id\n<br />\n";

		echo "		<li><label$w_style>".($left ? '' : $item).'
			<input class="'.($onclick != ''? 'tick_1 ' : '').'tick" type="checkbox" name="target['.$item."]\"$onclick$check_it$_whole />"
				.($left ? $item : "")."\n		</label></li>\n";

	}

	echo "	</ul>\n";
	return;
}

/* This function writes a string passed to the filename given */
function file_put_stuff($file, $data, $mode)
{
	$error	= '';
	if(empty($file))
		$error	= "\nCannot write data: no file specifed";

	if(empty($mode))
		$error	.= "\nCannot write data: no write mode specified";

	if(is_readable($file) && !is_writable($file))
		$error	.= "\nCannot write data: File ($file) not writeable";

	if(!empty($error))
		return $error;

	$handle = fopen($file, $mode);

	if(!fwrite($handle, $data))
		$error	.= "\nFile write failed";

	if(!fclose($handle))
		$error	.= "\nFailed to close file";

	return $error;
}

/* This function prints the success item on the admin page */
function print_success($success)
{
	global $page_n;

	$out	= '<span class="f_right" id="success">Your ';

	if($success != 1)
		$suc	= ' <span style="color: blue;">not</span>';
	else
		$suc	= "";

	if($page_n == "Admin")
		$out	.= "changes were$suc saved";
	else
		$out	.= "email was$suc sent";

	$out	.= " successfully.</span>";
	return $out;
}

/* This function returns the first word in a string */
function first_name($name)
{
	global $debug_info;
	$tmp = explode(" ", $name);
		$debug_info .= "(first_name)\$tmp=$tmp,	\$name=$name\n<br />\n";
	if($tmp == $name)
		return $name;
	else
		return $tmp[0];
}

/* This function returns the name of the persons info file */
function info_name($name)
{
	return email(ucwords(strtolower(str_replace(".", " ", $name))));	//email copes with webmaster and those with spaces in
}

/* This function converts a job title into an email add-in */
function email($job)
{
	return str_replace(" ", ".", $job);
}

/* This function finds the email of a user */
function email_addr($job)
{
	global 	$comm_email_prefix, $comm_email_postfix, $webmaster_email;

	if(!empty($webmaster_email) && $job == "webmaster")
		return $webmaster_email;

	return $comm_email_prefix.email($job).$comm_email_postfix;
}

/* This function prints an email to link */
function email_link($text, $gender, $address, $subject, $cc, $bcc, $body)
{
	global $committee_email, $site_root;

	if(!stristr($address,"@"))	//if they didn't specify an actual address assume the meant a committee member (dangerous, please fix)
	{
		$job	= email($address);
		if(strpos($committee_email, "@"))	//if all via one email address
		{
			$subject	= "[FAO:$job] ".$subject;
			$address	= "ACCF@soton.ac.uk";
		} else
			$address	= email_addr($address);
	}

	$out_val	=  "<a href=\"mailto:$address";

	if($subject != "0")
	{
		$subject = htmlspecialchars($subject);	//fix the spaces into html encoded spaces (%20)
		$out_val	.=  "?subject=$subject";
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

	if($gender == "0" && isset($job))
	{
		include "$site_root/Users/".info_name($job).".comm.php";
	}

	$out_val	.=  "\" title=\"Send $gender an email\">$text</a>";
	return	$out_val;
}
?>