<?php
	require_once("Global.inc.php");	//contains the File System PHP Gallery functions (both mine and the original ones)

$header_link	=	$error	= "";

if(!$logged_in) {
	include "Head.inc.php";
	print_logon_form();
	include "Foot.inc.php";
	exit();
}

$type	= str_replace(" ", "_", ucwords($type));

//strip slashes and convert php opening or closing tags to their html equivalents to prevent malicious code from running
if(!empty($content))
	$content	= stripslashes(str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $content));

$debug_info .= @"\$type=$type\n<br />\$content='$content'\n<br />\n";

if($debug) {
	echo "\n<br />POST:\n<br />";
	print_r($_POST);
	echo "\n<br />GET:\n<br />";
	print_r($_GET);
}

	require_once("Modules/Module.php");

if(is_readable("Modules/$type.module.php"))
	require_once("Modules/$type.module.php");

$class_name	= "Admin$type";

if(!class_exists($class_name))
	$error	= "\nUnrecognised edit type!\n<br />\n";
else {
	$type_obj	= new $class_name();

	$debug_info	.= "\nClass $type exists!\n<br />\ntype:".$type_obj->get_my_class()."\n<br />\n";

	$error	= $type_obj->submit();

	$header_link	.= "#$type";
}

if(!empty($error) || $debug)
	include "handler.php";
else
	header("Location: Admin.php?success=1$header_link");
?>