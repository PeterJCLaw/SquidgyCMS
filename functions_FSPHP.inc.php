<?php
/*
	Chris' Original functions (the ones below here) are
	Copyright (C) 2004 Chris Howells <howells@kde.org>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

 */
?>
<?php
class FSPHP{
//Chris' orignial functions, which may have been tweaked by me, just a little

/* This function reads all the images (jpg or png) in a directory and returns an array with this information
   Tweaked by PJCL so that it encompasses the secure dir and ignore dir bits
 */
function dirList ($directory)
{
	global $logged_in, $show_all;

	$results = array();
	$handler = opendir($directory);

	while($file = readdir($handler))
	{
		$file_path	= $directory . "/" . $file;
		if($file != '.' && $file != '..'  && is_readable($file_path))
		{
			if(is_file($file_path) && ((returnExtension($file) == "jpg") || (returnExtension($file) == "png")) && (!ignore_file($file) || $show_all))
			{
				array_push($results, $file);
			}
			else
			if(is_dir($file_path) && ($file[0] != ".") && (!ignore_file($file) || $show_all) && (!secure_dir($file) || $logged_in))
			{
				array_push($results, $file);
			}
		}
	}
	closedir($handler);
	sort($results);
	reset($results);
	return $results;
}

/* This function looks at what the GET request is and decides if someone is trying to exploit the script or not */
function authoriseRequest($request)
{
	$illegal = false;

	// First have a look if the string contains a .. characters
		$pos = strpos($request, "..");
	if($pos === 0 || $pos > 0) //strpos can return boolean false or non boolen 0 which evaulates to false
	{
		$illegal = true;
	}

	if($request[0] == "/")
	{
		$illegal = true;
	}

	if($illegal)
	{
		echo "<p>This request has been denied to prevent potential abuse of FsPHPGallery (providing listing of arbitrary directories under the file system). If you are attempting to set up FsPHPGallery for the first time, please do not set \$Path (in config.inc.php) to a value starting with \"/\" or containing \"..\" -- it is advisable to use symbolic links to get around this.</p>";
		echo "</body></html>\n";
		global $abuseReports;
		global $email;
		if($abuseReports)
		{
			mail($email, "Script abuse", $_SERVER["REMOTE_ADDR"] . " tried to abuse " . $_SERVER['REQUEST_URI']);
		}
		exit();
	}
}

/* This function returns true if the image is portrait, otherwise returns false */
function isLandscape($path)
{
	list($width, $height, $type, $attr) = getimagesize($path);

	if($width > $height)
	{
		return true;
	}
	else
	{
		return false;
	}
}

/* Removes the trailing or preceeding slash from a string */
function removeSlashes($string)
{
	if($string[0] == "/")
	{
		$string = substr($string, 1); //return all but first char
	}
	if($string[strlen($string) - 1] == "/")
	{
		$string = substr($string, 0, strlen($string) - 1); //return all but last char
	}
	return $string;
}

/* Recursive creates directories (e.g. mkdir -p) */
function recursiveMkdir($directory)
{
	$dir = split("/", $directory);
	$create = "";
	for ($i = 0; $i < count($dir); $i++)
	{
		$final = ($i == count($dir) - 1) ? true : false;
		$create = $create . $dir[$i] . "/";
		if(file_exists($create))
		{
			if($final)
			{
				return true;
			}
		}
		else
		if(file_exists($create) && !is_dir($create))
		{
			return false;
		}
		else
		{
			if(mkdir($create))
			{
				if($i == (count($dir) - 1))
				{
					return true;
				}
			}
			else
			{
				return false;
			}
		}
	}
}

/* This takes a path and filename and returns just the path (not the filename) */
function returnPath($string)
{
	$string = removeSlashes($string);
	$split = split("/", $string);
	$return = "";
	for ($i = 0; $i < count($split) - 1; $i++)
	{
		$return = $return . $split[$i] . "/";
	}
	return removeSlashes($return);
}

/* Takes a path and filename and returns just the filename (not the path) */
function returnName($string)
{
	$string = removeSlashes($string);
	$split = split("/", $string);
	return $split[count($split) - 1];
}

/* Checks whether the PHP has gd support */
function checkPHP()
{
	if(!function_exists("imagejpeg"))
	{
		echo "<p>Your version of PHP does not appear to be compiled with gd support and therefore needs to be re-compiled with gd support before FsPHPGallery will work.</p>";
		exit();
	}
}

/* This function returns the extension of a file name */
function returnExtension($string)
{
	return(strtolower(substr($string, strlen($string) - 3)));
}

function readCacheFile($file)
{
	$fp = fopen($file,'r');

	// dunno if this is a PHP bug or what but fgets() reads in the terminating new line
	// character as well so we have to manually cast it to an int due to PHP's type looseness
	// actually i think it's because George used \n as the terminating character on each line
	$width	= (int)fgets($fp);
	$height	= (int)fgets($fp);
	$widthT	= (int)fgets($fp);
	$heightT		= (int)fgets($fp);
	$width_size1	= (int)fgets($fp);
	$height_size1	= (int)fgets($fp);
	$width_size2	= (int)fgets($fp);
	$height_size2	= (int)fgets($fp);
	$full_width		= (int)fgets($fp);
	$full_height	= (int)fgets($fp);

	fclose($fp);

	return ("$width $height $widthT $heightT $width_size1 $height_size1 $width_size2 $height_size2 $full_width $full_height");
}

function writeCacheFile($item, $size_cache)
{
	// Grab images for dimensions
	$extension = returnExtension($item);

	if($extension == "jpg")
	{
		$source = imagecreatefromjpeg($item);
	}
	else if($extension == "png")
	{
		$source = imagecreatefrompng($item);
	}

	$real_height = imagesy($source);
	$real_width = imagesx($source);

	imagedestroy($source);

	$ratio = ($real_width / $real_height);
	$ratio2 = ($real_height / $real_width);

	if(isLandscape($item))
	{
		$width = round(600 * $ratio);
		$height = 600;
		$widthT = round(120 * $ratio);
		$heightT = 120;
	}
	else
	{
		$height = 600;
		$width = round(600 * $ratio);
		$heightT = 120;
		$widthT = round(120 * $ratio);
	}

	if(($real_height < 960) || ($real_width < 960))
	{
		// For images with height < 960
		// So that they don't scale UP
		$height_size1	= round($real_height * 0.5);
		$width_size1	= round($height_size1 * $ratio);
		$height_size2	= round($real_height * 0.75);
		$width_size2	= round($height_size2 * $ratio);
	}
	else
	{
		if(isLandscape($item))
		{
			// For all landscape images (ie - with height > 960)
			$width_size1	= round(768 * $ratio);
			$height_size1	= 768;
			$width_size2	= round(960 * $ratio);
			$height_size2	= 960;
		}
		else
		{
			// For all portrait images (ie - with width > 960)
			$width_size1	= 768;
			$height_size1	= round(768 * $ratio2);
			$width_size2	= 960;
			$height_size2	= round(960 * $ratio2);
		}
	}

	recursiveMkdir(returnPath($size_cache));
	$fp = fopen($size_cache,'w');

	fwrite($fp, $width . "\n");
	fwrite($fp, $height . "\n");
	fwrite($fp, $widthT . "\n");
	fwrite($fp, $heightT . "\n");
	fwrite($fp, $width_size1 . "\n");
	fwrite($fp, $height_size1 . "\n");
	fwrite($fp, $width_size2 . "\n");
	fwrite($fp, $height_size2 . "\n");
	fwrite($fp, $real_width . "\n");
	fwrite($fp, $real_height . "\n");

	fclose($fp);
	clearstatcache();
}
}
?>