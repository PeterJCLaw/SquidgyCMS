<?php
class Newsletter extends Block {
	function Newsletter() {
		parent::__construct();
	}

	/* This function returns the date of the first Sunday of the calendar year */
	function firstDay($args)
	{
		list($year, $day)	= $args;

		$day_list['Sun']	= $day_list['Sunday']	= 0;
		$day_list['Mon']	= $day_list['Monday']	= 1;
		$day_list['Tue']	= $day_list['Tueday']	= 2;
		$day_list['Wed']	= $day_list['Wednesday']	= 3;
		$day_list['Thu']	= $day_list['Thursday']	= 4;
		$day_list['Fri']	= $day_list['Friday']	= 5;
		$day_list['Sat']	= $day_list['Saturday']	= 6;

		if(is_string($day)) {
			if(array_key_exists($day, $day_list))
				$day	= $day_list[$day];
			else
				$day	= 0;
		}

		$date = strtotime(date("01/01/$year"));
		for($i=0; $i<7; $i++)
		{
			if($i > 0)
				$date	= strtotime("+1 day", $date);
			if(date("w", $date) == $day)
				return $date;
		}
	}

	/* WANRING - RECURSIVE FUNCTION */
	/* This function generates the link to the latest newsletter in the folder */
	function date($args)
	{
		global $debug_info, $SitePath, $NewsPath;
		list($when, $prefix, $postfix, $day)	= $args;
		
		if(empty($when) || empty($prefix) || empty($postfix) || empty($day) || !is_dir($SitePath.$NewsPath)) {
			if(!is_dir($SitePath.$NewsPath))
				log_info('Directory does not exist', $SitePath.$NewsPath);
			else
				log_info('Insufficient information given to locate file', $args);
			return "None";
		}

		$stamp		= (!is_int($when) ? strtotime($when) : $when );
		$date		= date('Y-m-d', $stamp);
		$year		= date('Y', $stamp);
		$folder		= $SitePath."$NewsPath/$year";
		$file		= $folder."/".$prefix.$date.$postfix;

		$debug_info	.= "\n\$date=$date\n<br />\$when=$when\n<br />\$year=$year\n<br />\$file=$file\n<br />\$stamp=$stamp\n<br />\n";

		if(!file_exists($file))
		{
			$first_day	= $this->firstDay(array($year, $day));
			if(is_dir($folder))
				for($stamp = strtotime("-1 week", $stamp); $stamp >= $first_day; $stamp = strtotime("-1 week", $stamp))
				{
					$date	= date('Y-m-d', $stamp);
					$file	= $folder."/".$prefix.$date.$postfix;
					$debug_info	.= "\n\$file=$file\n<br />\$stamp=$stamp\n<br />\n";
					if(file_exists($file))
						return $file;
				}
			$file	= $this->date(array(strtotime("-1 week", $first_day), $prefix, $postfix, $day));
		}

		return $file;
	}
}
?>