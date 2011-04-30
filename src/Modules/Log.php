<?php
#name = Log
#description = Enables the logging of debug informatin and errors
#package = Core - required
#type = system
###

class Log {

	function __construct() {
		$this->error_log	= array();
		$this->info_log	= array();
	}

	function Log() {
		$this->__construct();
	}

	function getInstance() {
		static $instance;
		if ($instance == null)
		{
			$instance = new Log();
		}
		return $instance;
	}

	/* log an error */
	function error($text, $vars)
	{
		array_push($this->error_log, $this->combine($text, $vars));
	}

	/* log an error */
	function info($text, $vars)
	{
		array_push($this->info_log, $this->combine($text, $vars));
	}

	/* log an error */
	function combine($text, $vars)
	{
		if(!empty($vars))
			return $text." Vars: {".print_r($vars, true)."}";
		return $text;
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
	Log::getInstance()->error($text, $vars);
}

function log_info($text, $vars = '')
{
	if(empty($GLOBALS['debug']))
		return;
	Log::getInstance()->info($text, $vars);
}

function show_log($type = 'all')
{
	return Log::getInstance()->show($type);
}

