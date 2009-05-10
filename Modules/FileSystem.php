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
		$ext_len	= strlen(FileSystem::returnFileExt($n));
		if($ext_len > 0)
			$n = substr($n, 0, -1*($ext_len+1));
		return $n;
	}

	/* This function takes in the file name and retruns its extention */
	function returnFileExt($n)
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

	/* mask the standard is_dir function, using site_root as a prefix */
	function is_dir($dir)
	{
		return is_dir($GLOBALS['site_root'].'/'.$dir);
	}

	/* mask the standard is_file function, using site_root as a prefix */
	function is_file($file)
	{
		return is_file($GLOBALS['site_root'].'/'.$file);
	}

	/* This function reads ALL the items in a directory and returns an array with this information */
	function Full_Dir_List($dir)
	{
		$results = array();
		if(!is_dir($dir) || !is_readable($dir))
			return $results;

		global $logged_in, $this_dir, $show_all;
		$this_dir = $dir;
		$handler = opendir($dir);

		while($file = readdir($handler)) {
			$file_path	= $dir . "/" . $file;
			if($file != '.' && $file != '..'  && is_readable($file_path) && !FileSystem::ignore_file($file)
							&& (is_file($file_path) || (is_dir($file_path) && $file[0] != ".")))
					array_push($results, $file);
		}
		closedir($handler);
		sort($results);
		reset($results);
		return $results;
	}

	/* This function reads only the items in a directory that containt the strings in $filter and returns an array with this information */
	function Filtered_File_List($dir, $filter)
	{
		$results = array();
		if(!is_dir($dir) || !is_readable($dir))
			return $results;

		if($filter == -1)	//special filter for all files (no dirs)
			$all = true;

		if(!is_array($filter))	//allow either an array or a csv list
			$filter = str_getcsv($filter);

		$handler = opendir($dir);

		while($file = readdir($handler)) {
			$file_path	= $dir . "/" . $file;
			if($file != '.' && $file != '..'  && is_readable($file_path) && is_file($file_path))
				foreach($filter as $f) {
					if($all || stristr($file, $f)) {
						array_push($results, str_replace($f, "", $file));
						break;
					}
				}
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
			$file_block = in_array(FileSystem::returnFileName($file), $ignore_files);
		else
			$file_block = (FileSystem::returnFileName($file) == $ignore_files) ? TRUE : FALSE;

		if(is_array($ignore_exts))			//list of file extensions to ignore, case insesnsitive
			$ext_block = in_array(strtolower(FileSystem::returnFileExt($file)), $ignore_exts);
		else
			$ext_block = (FileSystem::returnFileExt($file) == $ignore_exts) ? TRUE : FALSE;

		if(is_array($ignore_parts))			//list of parts of filenames to ignore, case insesnsitive
			foreach($ignore_parts as $part_val)
				if(stristr($file, $part_val)) {
					$part_block = TRUE;
					break;
				}
		else
			$part_block = stristr($file, $part_val) ? TRUE : FALSE;

	//	$debug_info	.= "ingore_file: $file,	ext_block=".($ext_block ? 'TRUE' : 'FALSE').",	file_block=".($file_block ? 'TRUE' : 'FALSE').",	part_block=".($part_block ? 'TRUE' : 'FALSE')."\n<br />\n";

		return ((($ext_block || $file_block || $part_block) && !$show_all) || FileSystem::secure_dir($file));
	}

	/* get a file with no whitespace on the right, useful as PHP < 5 doesn't have FILE_IGNORE_NEW_LINES */
	function get_file_rtrim($path)
	{
		if(!is_readable($path))
			return array();
		if(floatval(phpversion()) >= 5)
			return file($path, FILE_IGNORE_NEW_LINES);
		return array_map('rtrim', file($path));
	}

	/* read the first $n lines of file $f from $s onwards */
	function get_file_lines($f, $n, $s=0)
	{
		$fh	= fopen($f, 'r');
		for($i=$s; $i<$n; $i++) {
			$file[$i]	= fgets($fh);
		}
		fclose($fh);
		return $file;
	}

	/* grab a 2D array then output it as a file, including colnames if told to, automatically if needed */
	function put_file_assoc($path, $data, $cols=FALSE)
	{
		if($cols !== FALSE && !is_array($cols))	{ //not no, but not valid either
			$cols = array_keys($data[0]);
			$file_string = implode('|:|', $cols)."\n";
		} else
			$file_string = '';

		foreach($data as $line) {
			$file_string .= implode('|:|', $line)."\n";
		}
		return FileSystem::file_put_contents($path, $file_string, 'w');
	}

	/* grab a data file & convert it to a 2D array using the passed column names, or use the ones from the file is none are pased */
	function get_file_assoc($path, $cols=FALSE)
	{
		if(!is_readable($path))	//if we can't read it then bail
			return array();
		$file	= FileSystem::get_file_rtrim($path);	//read the info into an array, one element per line
		$out	= array();
		if($cols === FALSE) {	//automatic cols sorting
			$cols = explode('|:|', $file[0]);
			unset($file[0]);
		}
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
	function file_put_contents($file, $data, $mode)
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