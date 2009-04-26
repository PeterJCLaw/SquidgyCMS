<?php
require_once('Global.inc.php');
require_once("Modules/Module.php");

$module_path = Module::get_path($module);

if($module_path === FALSE) {
	print_Admin_Section(array('fail'));
	exit();
}

require_once($module_path);

$class	= ucwords($type).$module;

switch($type) {
	case 'admin':
		if(class_exists($class)) {
			$thisobj	= new $class();

			$host_arr	= $thisobj->get_info();
			$host_arr['obj']	= $thisobj;

			print_Admin_Section($host_arr);
		} else
			echo "Admin class not defined in module '$class'.";
		break;
	
	case 'block':
		if(class_exists($class) && method_exists($class, 'ajax')) {
			$thisobj	= new $class();
			echo $thisobj->ajax();
		} else
			echo "Ajax method not defined in block '$class'.";
		break;
		
	case 'preview':
	default:
		echo "Request type not recognised.";
}

if($debug)
	echo "\$debug_info = <br />$debug_info<br />";
?>