<?php
require_once('Global.inc.php');
require_once("Modules/Module.php");

if(!is_readable("Modules/$class.module.php")) {
	print_Admin_Section(array('fail'));
	exit();
}

require_once("Modules/$class.module.php");

$class_name	= ucwords($type).$class;

if($type == 'admin') {
	if(class_exists($class_name)) {
		$thisobj	= new $class_name();

		$host_arr	= $thisobj->get_info();
		$host_arr['obj']	= $thisobj;

		print_Admin_Section($host_arr);
	}
} elseif($type == 'block') {
	if(class_exists($class_name) && method_exists($class_name, 'ajax')) {
		$thisobj	= new $class_name();
		echo $thisobj->ajax();
	} else {
		echo "Ajax method not defined in block '$class_name'.";
	}
	
} elseif($type == 'preview') {
} else {
	echo "Request type not recognised.";
}

if($debug)
	echo "\$debug_info = <br />$debug_info<br />";
?>