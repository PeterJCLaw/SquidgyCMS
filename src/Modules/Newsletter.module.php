<?php
#name = Newsletter
#description = Generates a link to the most recent newsletter
#package = Core - optional
#type = content
###

class BlockNewsletter extends Block {
	function BlockNewsletter() {
		parent::__construct();
	}

	/* This function returns the date of the first Sunday of the calendar year */
	function firstDay($args)
	{
		$year	= $args['year'];
		$day	= $args['day'];

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
		for($i=0; $i<7; $i++) {
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
		global $site_root, $NewsPath;
		list($when, $prefix, $postfix, $day)	= array();
		extract($args, EXTR_IF_EXISTS);

		if(empty($when) || empty($prefix) || empty($postfix) || empty($day) || !is_dir($site_root.'/'.$NewsPath)) {
			if(!is_dir($site_root.'/'.$NewsPath))
				log_info('Directory does not exist', $site_root.'/'.$NewsPath);
			else
				log_info('Insufficient information given to locate file', $args);
			return "None";
		}

		$stamp		= (!is_int($when) ? strtotime($when) : $when );
		$date		= date('Y-m-d', $stamp);
		$year		= date('Y', $stamp);
		$folder		= $site_root."/$NewsPath/$year";
		$file		= $folder."/".$prefix.$date.$postfix;

		log_info('Newsletter.date', array('date' => $date, 'when' => $when, 'year' => $year, 'file' => $file, 'stamp' => $stamp));

		if(file_exists($file))
			return $file;

		$first_day	= $this->firstDay(array('year'=>$year, 'day'=>$day));
		if(is_dir($folder)) {
			for($stamp = strtotime("-1 week", $stamp); $stamp >= $first_day; $stamp = strtotime("-1 week", $stamp))
			{
				$date	= date('Y-m-d', $stamp);
				$file	= $folder."/".$prefix.$date.$postfix;
				log_info('Newletter.date is_dir', array('file' => $file, 'stamp' => $stamp));
				if(file_exists($file))
					return $file;
			}
		} else
			return $this->date(array('when'=>strtotime("-1 week", $first_day), 'prefix'=>$prefix, 'postfix'=>$postfix, 'day'=>$day));
	}
}
