<?php
#name = Log
#description = Enables interactions with the File System
#package = Core - required
#type = system
###

class FileSystem {

	/* This function takes in the file name and retruns it, without the extension */
	function returnFileName($n)
	{
		$ext_len	= strlen(returnFileEXT($n));
		if($ext_len > 0)
			$n = substr($n, 0, -1*($ext_len+1));
		return $n;
	}

	/* This function takes in the file name and retruns its extention */
	function returnFileEXT($n)
	{
		$all	= explode(".", $n);
		$ext	= $all[count($all)-1];
		return ($ext == $n) ? '' : strtolower($ext);
	}

	/* This function returns the current folder */
	function what_dir_am_i_in($dir)
	{
		$folder = explode("/", $dir);
		return $folder[count($folder)-1];
	}

	/* This function reads ALL the items in a directory and returns an array with this information */
	function dirAllList($directory)
	{
		global $logged_in, $this_dir, $show_all;
		$this_dir = $directory;
		$results = array();
		$handler = opendir($directory);

		while($file = readdir($handler)) {
			$file_path	= $directory . "/" . $file;
			if($file != '.' && $file != '..'  && is_readable($file_path) && !ignore_file($file) && (is_file($file_path) || (is_dir($file_path) && $file[0] != ".")))
					array_push($results, $file);
		}
		closedir($handler);
		sort($results);
		usort($results, "PJCL_sort");
		reset($results);
		return $results;
	}

	/* This function reads only the items in a directory that containt the string in $filter and returns an array with this information */
	function Filtered_Dir_List($directory, $filter)
	{
		$results = array();
		if(!is_dir($directory))
			return $results;

		$handler = opendir($directory);

		while($file = readdir($handler)) {
			$file_path	= $directory . "/" . $file;
			if($file != '.' && $file != '..'  && is_readable($file_path) && is_file($file_path) && stristr($file, $filter))
				array_push($results, str_replace($filter, "", $file));
		}
		closedir($handler);
		sort($results);
		reset($results);
		return $results;
	}

	/* This function takes in the file name and retruns true or false depending on whether it's been declared secure (in config.inc) */
	function secure_dir($file)
	{
		global $secure_folders, $logged_in;

		if(!is_dir($file) || $logged_in)	//if its not a directory or they're logged in
			return FALSE;

		if(is_array($secure_folders))
			return in_array($file, $secure_folders);
		else
			return ($file == $secure_folders) ? TRUE : FALSE;
	}

	/* This function takes in the file name and retruns true or false depending on whether it's been declared as a file to ignore (in config.inc) */
	function ignore_file($file)
	{
		global $ignore_files, $ignore_exts, $ignore_parts, $debug_info, $show_all;

		if(is_array($ignore_files))			//list of filenames to ignore
			$file_block = in_array(returnFileName($file), $ignore_files);
		else
			$file_block = (returnFileName($file) == $ignore_files) ? TRUE : FALSE;

		if(is_array($ignore_exts))			//list of file extensions to ignore, case insesnsitive
			$ext_block = in_array(strtolower(returnFileEXT($file)), $ignore_exts);
		else
			$ext_block = (returnFileEXT($file) == $ignore_exts) ? TRUE : FALSE;

		if(is_array($ignore_parts))			//list of parts of filenames to ignore, case insesnsitive
			foreach($ignore_parts as $part_val)
				if(stristr($file, $part_val)) {
					$part_block = TRUE;
					break;
				}
		else
			$part_block = stristr($file, $part_val) ? TRUE : FALSE;

	//	$debug_info	.= "ingore_file: $file,	ext_block=".($ext_block ? 'TRUE' : 'FALSE').",	file_block=".($file_block ? 'TRUE' : 'FALSE').",	part_block=".($part_block ? 'TRUE' : 'FALSE')."\n<br />\n";

		return ((($ext_block || $file_block || $part_block) && !$show_all) || secure_dir($file));
	}
	
	/* get a file with no whitespace on the right, useful as PHP < 5 doesn't have FILE_IGNORE_NEW_LINES */
	function file_rtrim($path)
	{
		if(floatval(phpversion()) >= 5)
			return file($path, FILE_IGNORE_NEW_LINES);
		return array_map('rtrim', file($path));
	}

	/* read the first $n lines of file $f from $s onwards */
	function read_file_lines($f, $n, $s=0)
	{
		$fh	= fopen($f, 'r');
		for($i=$s; $i<$n; $i++) {
			$file[$i]	= fgets($fh);
		}
		fclose($fh);
		return $file;
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

}
?>