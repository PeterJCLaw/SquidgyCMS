<?php
#name = Log
#description = Enables the logging of debug informatin and errors
#package = Core - required
#type = system
###

class log {
	
	function __construct() {
		$this->error_log	= array();
		$this->info_log	= array();
	}

	function log() {
		$this->__construct();
	}

	/* log an error */
	function error($text)
	{
		array_push($this->error_log, $text);
	}

	/* log an error */
	function info($text)
	{
		array_push($this->info_log, $text);
	}

	/* show the log */
	function show($type)
	{
		switch($type) {
			case 'error':
				return $this->show_errors();
			case 'info':
				return $this->show_info();
			case 'all':
			default:
				return $this->show_info().$this->show_errors();
		}
	}

	/* show the errors that have been logged */
	function show_errors()
	{
		if(!empty($this->error_log) && is_array($this->error_log))
			return "\n<ul id=\"errorLog\">\n<li>".implode($this->error_log, "</li>\n<li>")."</li>\n</ul>";
	}

	/* show the infos that have been logged */
	function show_info()
	{
		if(!empty($this->info_log) && is_array($this->info_log))
			return "\n<ul id=\"infoLog\">\n<li>".implode($this->info_log, "</li>\n<li>")."</li>\n</ul>";
	}
}

function log_error($text, $vars_arr = '')
{
	global $_Log;
	if(is_object($_Log) && get_class($_Log) == 'log')
		$_Log->error($text.print_r($vars_arr, true));
	else
		$_Log	= new Log();
}

function log_info($text, $vars_arr = '')
{
	if(empty($GLOBALS['debug']))
		return;
	global $_Log;
	if(is_object($_Log) && get_class($_Log) == 'log')
		$_Log->info($text.print_r($vars_arr, true));
	else
		$_Log	= new Log();
}

function show_log($type = 'all')
{
	global $_Log;
	if(is_object($_Log) && get_class($_Log) == 'log')
		return $_Log->show($type);
}

?>