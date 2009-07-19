<?php
/* add a biit of JS to the page */
function add_script($type, $src) {
	global $script_files, $script_code;
	if($type == 'file' && !in_array($src, $script_files))
		array_push($script_files, $src);
	elseif($type == 'code')
		$script_code .= "\n$src";
	return;
}

/* This function groups array elements by one property of those elements */
function has_method($class, $method) {
	if(!class_exists($class))
		return FALSE;
	$methods = get_class_methods($class);
	$methods = array_map('strtolower', $methods);
	return in_array($method, $methods);
}

/* This function groups array elements by one property of those elements */
function group_array_by_key($arr, $col) {
	$out = array();
	foreach($arr as $key => $val) {
		$out[$val[$col]][] = $val;
	}
	return $out;
}

/* This function gets basic info abouot the current page */
function get_page_basics() {
	$SN_arr	= explode("/", $_SERVER['SCRIPT_NAME']);	//explode so I can deal with the bits individually
	$RU_arr	= explode("/", $_SERVER['REQUEST_URI']);

	$page	= basename($_SERVER['SCRIPT_NAME']);	//get the name of this page from its path
	unset($SN_arr[count($SN_arr)-1]);	//unset the last value - that's the page name - as I don't want it in the absolute ref

	if($SN_arr[1] == "~".$RU_arr[1])
		$SN_arr[1] = $RU_arr[1];	//dump the tilde if we don't want it NB: check this on other systems

	return array(implode("/", $SN_arr)."/", $page, FileSystem::returnFileName($page));
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

/* add this PHP5.3 function if needed */
if(!function_exists('str_getcsv')) {
function str_getcsv($s)
{
	return explode(',', str_replace(', ', ',', $s));
}
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
function multi2dSortAsc(&$arr, $sort_key)
{
	if(empty($sort_key) || empty($arr))
		return;
	$sort_col	= array();
	foreach($arr as $key => $sub)
		$sort_col[$key]	= $sub[$sort_key];
	array_multisort($sort_col, &$arr);
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
	global $debug_info;

	$list	= FileSystem::Filtered_File_List($where, $filter);

	$id_list	= array();

	foreach($list as $val) {
		array_push($id_list, get_GEN_id($val));
	}

	if(count($id_list) == 0)
		return 1;
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

/* This function returns the first word in a string using split as a delimiter */
function first_word($str, $split=' ')
{
	$arr = explode($split, $str, 2);
	return $arr[0];
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
			$address	= $committee_email;
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

	if($gender == "0")
	{
		$gender = 'them';
	}

	$out_val	.=  "\" title=\"Send $gender an email\">$text</a>";
	return	$out_val;
}
?>