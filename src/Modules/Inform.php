<?php
#name = Inform
#description = Simple interface to inform the user about something
#package = Core - required
#type = system
###

class Inform
{
	var $infos;
	var $errors;

	function Inform()
	{
		$this->infos =& $_SESSION['inform-infos'];
		$this->errors =& $_SESSION['inform-errors'];
	}

	function getInstance()
	{
		static $instance;
		if ($instance == null)
		{
			$instance = new Inform();
		}
		return $instance;
	}

	function error($message)
	{
		if (!empty($message))
		{
			$this->errors[] = $message;
		}
	}

	function info($message)
	{
		if (!empty($message))
		{
			$this->infos[] = $message;
		}
	}

	function show()
	{
		$out = '';
		foreach (array('info', 'error') as $type)
		{
			$pType = $type.'s';
			if (!empty($this->$pType))
			{
				$out .= '<ul class="inform '.$type."\">\n\t<li>".implode("</li>\n<li>", $this->$pType)."</li>\n</ul>";
			}
		}
		$this->reset();
		return $out;
	}

	function reset()
	{
		$this->infos = $this->errors = array();
	}
}
