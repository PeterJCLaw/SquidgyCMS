<?php

//Peter's Added or adapted functions

/* converts to a valid id tag */
function id_convert($tag)
{
	global $_space, $_slash;
	$tag	= str_replace(" ", $_space, $tag);
	$tag	= str_replace("/", $_slash, $tag);
	return $tag;
}

/* compare the path passed to the current file
 * by shrinking the current file to the same length as the passed file
 * then see if they're the same
 */
function path_compare($p_p)
{
	global $debug_info, $curr_file;

	$p_p	= str_replace(".//", "", $p_p);

	$debug_info .= "\$p_p=$p_p\n<br />\$curr_file=$curr_file\n<br />\n";

	if($p_p == $curr_file || $p_p == "./")
		return TRUE;

	$length		= strlen($p_p);

	$curr_s		= substr($curr_file, 0, $length);

	$debug_info .= "\$curr_s=$curr_s\n<br />\$length=$length\n<br />\n";

	return ($p_p == $curr_s);	//if they match then the folder gets expanded
}

/* recursive function to explore the file / folder structure beneath
 * retruns blank if nothing of note is found
 */
function file_explore($path, $tabs, $ajax)
{
	global $debug_info, $curr_file;

	$path_id = id_convert($path);

	if($path == './') {	//top level
		$path_id = '';
		$retval  = "<div id=\"FE_preload\"><ul><li class=\"collapsed\"></li><li class=\"expanded\"></li><li class=\"FE_empty\"></li></ul></div>";
	} else
		$retval	= "";

	$paths_match = path_compare($path, $curr_file);
	$new_css	= TRUE;

	$display	= ($paths_match || $new_css) ? "" : " style=\"display: none;\"";

	$debug_info	.= "\$paths_match=".($paths_match ? 'TRUE' : 'FALSE' )."\n<br />\$display=$display\n<br />\n";

	$dir_contents = dirAllList($path);

	if($dir_contents) {	//if there's something to show
		if($ajax < 2)	//if not being called from get_file_tree.php
			$retval	.= "\n$tabs<ul id=\"FE_$path_id\" class=\"File_Explore\"$display>\n";

		if($ajax && $tabs != "" && !$paths_match)	//if ajax is enabled and not top level and paths don't match
			$retval	.= "	$tabs<li>Loading File Tree...</li>\n";
		else {
			$ajax	= round($ajax/2);
			foreach($dir_contents as $item_name)	//for each of the results in this folder
			{
				$href		= $item		= ($path_id == "" ? "" : "$path/").$item_name;
				$item_id	= id_convert($item);
				$curr_item	= ($item == $curr_file) ? TRUE : FALSE;
				$item_sub_val = $li_insert = "";
				$debug_info	.= "\$item=$item\n<br />\$item_id=$item_id\n<br />\n";

				if (is_file($item) || is_dir($item)) {
					if (is_dir($item)) {	//if its a folder
						$tit			= "Go to $item_name";
						$li_ins_class	= "FE_empty";
						$item_sub_val	= file_explore($item, $tabs."		", $ajax);
						if($item_sub_val != '') {
							$li_ins_class	= ($paths_match ? (path_compare($item, $curr_file) ? "expanded" : "collapsed") : "collapsed");
							$item_sub_val	= ($curr_item ? "" : "&nbsp;&nbsp;<a href=\"?dir=$href\" title=\"$tit\">=></a>")."$item_sub_val\n	$tabs";
							$tit			= "Expand $item_name";
							$href			= "javascript:toggle_FE('FE_$item_id', 'FE_LI_$item_id');\" class=\"js_link";
						}
						$li_insert		= " id=\"FE_LI_$item_id\" class=\"$li_ins_class\"";
						$debug_info		.= "\$item_sub_val=$item_sub_val\n<br />\$li_insert=$li_insert\n<br />\n";
					} else {
						$item_name	= returnFileName($item_name); //gives us just the name (no extension) of the file
						$tit		= "Go to $item_name";
					}
					$thing_name	= "<a href=\"$href\" title=\"$tit\">$item_name</a>";

					if($curr_item)
						$thing_name	= "<b>$thing_name</b>";

					$retval	.= "	$tabs<li".$li_insert.">".$thing_name . $item_sub_val."</li>\n";
				}
			}
		}

		if($ajax < 2)	//if not being called from get_file_tree.php
			$retval	.= "$tabs</ul>";
	}
	else
		return '';

	$debug_info	.= "\$path=$path\n<br />\$path_id=$path_id\n<br />\$curr_file=$curr_file\n<br />\$tabs=|$tabs|\n<br />\n";

	return $retval;
}
/* This function makes the individual file listing for a file in the file explorer */
function make_file_listing($file, $icon_size, $directory, $request, $folder_icon)
{
	global $SitePath;
	if($icon_size == 2) {	//if we want large icons
		$size_class		= 'large_icons';
		$icon_width		= $icon_height	= 128;
		$sm				= "";
		$line_len		= 19;
	} else {
		$size_class		= 'small_icons';
		$icon_width		= $icon_height	= 64;
		$sm				= "_sm";
		$line_len		= 13;
	}

	$fullpath = $directory . "/" . $file;
	$item	= $request . "/" . $file;
	$href	= str_replace(".//", "", $item);
	$href	= substr($href, strlen($SitePath));

	if(is_dir($item)) {
		$image_stuff	= "FSPHP_Images/".($folder_icon == 1 ? whats_inside($item) : $folder_icon)."_folder$sm.png\" style=\"width: ${icon_width}px; height: ${icon_height}px";
		$href			= "?dir=".$href;
		$alt			= "Folder image";
		$title			= "Click to open folder";
		$link_name		= nameformat($file, 1, $line_len); //formats it for display
	} elseif(is_file($item)) {
		$name		= str_replace("_", " ", returnFileName($file)); //gives us just the name (no extension) of the image, without underscores
		$link_name	= nameformat($name, 1, $line_len); //formats it for display

		if(in_array(returnFileEXT($item), array("png", "jpg")))	//is it an image of the sort we can handle (jpg or png)
		{
			$size_cache = returnPath($item)."/Thumbs/".returnName($item).".dim";

			// if the cache file doesn't exist make it
			if(!file_exists($size_cache))
				writeCacheFile($item, $size_cache);

			// the cache file either already existed or we just made it so use it
			$sizes	= explode(" ", readCacheFile($size_cache));
			$thumb_ratio	= $sizes[2] / $sizes[3];

			if($thumb_ratio > 1)	//if its a landscape image adjust the height or vice versa
				$icon_height	= $icon_width / $thumb_ratio;

			if($thumb_ratio < 1)
				$icon_width		= $icon_height * $thumb_ratio;

			//prep for displaying the image thumbnail
			$image_stuff	= "resize.php?file=$href&amp;width=128&amp;height=128\""	//it gives a non stretched image, which may then be shrunk to half by HTML
								." style=\"width: ${icon_width}px; height: ${icon_height}px;";
			$alt			= $name;
			$title			= "Click image to view larger";
		} else {	//treat it as an ordinary file
			$ext		= strtoupper(returnFileEXT($item));

			$image_stuff	= "FSPHP_Images/".(file_exists("FSPHP_Images/$ext.png") ? $ext : "UnknownFile").".png\" style=\"width: ${icon_width}px; height: ${icon_height}px";
			$alt			= "$ext File";
			$title			= "Click to download this $ext file";
		}
	} else {
		return "Item is not of a valid type";
	}
	$image_stuff	= "\n					<img src=\"$image_stuff\" alt=\"$alt\" title=\"$title\" />";

	if($icon_size == 2)	//if we want large icons
		return "\n				<a href=\"$href\" title=\"$title\">$image_stuff\n					<br />$link_name\n				</a>";
	else
		return <<<ret
		<table><tr><th>
			<a href="$href" title="$title">$image_stuff</a>
		</td><td>
			<a href="$href" title="$title">$link_name</a>
		</td></tr></table>
ret;
}

/* This function prints the icons or images for the file or image browsing pages */
function print_files($type = "file", $icon_size = 3, $return = FALSE)
{
	global $SitePath, $ImagePath, $NewsPath, $FilePath, $itemsPerPage, $itemsInRow, $numberOfRows, $adapted_from, 	$cacheDir, $maxWidth, $maxHeight;
	global $who_copyright, $website_name_short, $copy_email_text, $copy_recipient, $copy_recip_gender, $copy_follow_text, $copy_fol_txt_img;
	global $nostalgic_images_footer, $n_i_f_link, $n_i_f_date_added;

	checkPHP(); //checks for gd support

	switch($type)
	{
		case "image":	//images
			$folder_icon	= "image";
			$Path = $ImagePath;
			$icon_size	= ($icon_size == 3) ? 2 : $icon_size;	//icon_size: 3- file dependant, 2- large, 1- small
			break;
		case "news":	//newsletters
			$folder_icon	= "file";
			$Path = $NewsPath;
			$icon_size	= ($icon_size == 3) ? 1 : $icon_size;	//icon_size: 3- file dependant, 2- large, 1- small
			break;
		case "file":	//files of any type
			$folder_icon	= 1;
			$Path = $FilePath;
			$icon_size	= ($icon_size == 3) ? 1 : $icon_size;	//icon_size: 3- file dependant, 2- large, 1- small
			break;
		default:
			return "Incorrect file type given - should be one of 'image', 'news' or 'file'.";
	}

	if(array_key_exists('dir', $_REQUEST))
		$request = $SitePath.$_REQUEST['dir'];
	else	// no directory was specified so just show the default one from config.php
		$request = $SitePath.$Path;

	// found out if someone is trying to exploit it
	authoriseRequest($request);

	$directory = getcwd() . "/" . removeSlashes($request);

	$results = array();
	if($type == "image")	//get either just the images or all the files
		$results = dirList($request);
	else
		$results = dirAllList($request);

	$split = array();
	$split = split("/", $request);

	$up = "$Path/";
	// FIXME -- this should use returnPath()
	for ($i = 1; $i < count($split) - 1; $i++) {	//don't add the first item (/$imagePath)
		$up = $up . $split[$i] . "/";
	}
	$up = removeSlashes($up);

	if($request != $Path) // we need to decide whether to show the navigation buttons or not
		$toplevel = false;
	else
		$toplevel = true;

	$file_type_mail	= ($type == "image" ? "images" : "files");

	//put in the copyright warning
	$RET_VAL	= '	<p class="center" id="file_image_copy">
		Please remember that all these '."$file_type_mail are copyright $who_copyright.<br />"
		."		If you would like to use any of them on your site please "
		.email_link($copy_email_text, $copy_recip_gender, $copy_recipient,
		$website_name_short.' '.ucwords($file_type_mail), 0, 0, "Sirs,\nI am writing to enquire about the possibility of using some of the "
		.$file_type_mail." on the $website_name_short website within the $request folder externally.\n")
		.$copy_follow_text.($type == "image" ? $copy_fol_txt_img : "" )
		."\n	</p>\n";

	//prep the navigation buttons
	$nav	= '	<p class="center">
		<a href="?"><img src="FSPHP_Images/home.png" style="width: 32px height: 32px" alt="Home" /></a>
		<a href="?dir='.$up.'"><img src="FSPHP_Images/up.png" style="width: 32px height: 32px" alt="Up" /></a>
	</p>
';

	if(!$toplevel)
		$RET_VAL	= $nav;

	$RET_VAL	.= "	<ul class=\"PHPlist $size_class\">\n		";

	$itemsPerPage = $itemsInRow * $numberOfRows;

	foreach($results as $file) {	//spit as many images as there are

		$RET_VAL	.= '			<li>';
		$RET_VAL	.= make_file_listing($file, $icon_size, $directory, $request, $folder_icon);
		$RET_VAL	.= "\n			</li>\n";

/*		$count++;
		if($count == $itemsInRow) {	// && ($i == count(items) - 1))
			$count = 0; //reset for the next row
			$RET_VAL	.= "		</tr>";
		}*/
	}

/*	for(; $count > 0 && $count < $itemsInRow; $count++) {
		$RET_VAL	.= "			<td>&nbsp;</td>\n";
		if($count == ($itemsInRow - 1)) {
			$RET_VAL	.= "		</tr>";
		}
	}
*/
	$RET_VAL	.= "\n	</ul>\n";

	if(!$toplevel)
		$RET_VAL	.= $nav;

	if($request == $ImagePath && $nostalgic_images_footer)
		$RET_VAL	.= "		<br />\n		Feeling nostalgic? Have a <a href=\"$n_i_f_link\" title=\"Takes you to the old style photos index page\">look</a>"
			." at the old $website_name_short Photos index (it contains all the same pre-$n_i_f_date_added photos, just looks older).\n	<br />\n";

	$RET_VAL	.= $adapted_from;
	if($return)
		return $RET_VAL;
	else
		echo $RET_VAL;
	return;
}

/* This function returns the the type of files in the directory passed */
function whats_inside($dir)
{
	global $debug_info;

	$dir_files	= dirAllList($dir);
	$dir_images	= dirList($dir);

	$num_files	= 0;
	$num_images	= 0;

	for($i=0; $item = $dir_files[$i]; $i++) {
		if(is_file($dir."/".$item))	//find the total number of files, not including folders
			$num_files ++;
	}

	for($i=0; $item = $dir_images[$i]; $i++) {
		if(is_file($dir."/".$item))	//find the total number of images, not including folders
			$num_images ++;
	}

	$debug_info	.= "\$dir=$dir\n<br />\$num_images=$num_images\n<br />\$num_files=$num_files\n<br />\n";

	return ((2 * $num_images) > $num_files) ? "image" : "file";
}

/* This function returns the current directory */
function what_dir_am_i_in($dir)
{
	$folder = explode("/", $dir);
	return $folder[count($folder)-1];
}

/* This function gets the name of the object to fit onto lines 13 characters wide */
function nameformat($name, $line, $width)
{
	global $debug_info;
	if(function_exists('wordwrap')) {
		$debug_info	.= "wordwrap exists, yay\n<br />\n";
		return wordwrap($name, $width, "<br />", TRUE);
	}

	$namelen = strlen($name);
	$critlen = $width * $line;

	if($namelen > $critlen) {	//if its too long for this line
		$name = nameformat($name, ($line+1), $width);	//see if it fits on the next
		$namelen = strlen($name);	//how long is it then?

		for($j = $namelen-1 ; $j > $critlen-1 ; $j--) {	//make a space so that the <br /> can be inserted
			$name[$j+6]=$name[$j];
		}

		for($k = 0, $tmp = "<br />"; $k < 6; $k++) {	//insert the <br />
			$name[$critlen + $k] = $tmp[$k];
		}
//			$name[strlen($name)+1] = '';
	}
	return $name;
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

/* This function is used in conjunction with usort - it sorts based on whether a file is a folder, then by extension, then alphabetically */
function PJCL_sort($a, $b)
{
	global $debug_info, $this_dir;

//	$debug_info	.= "\$this_dir=$this_dir\n<br />\$a=$a\n<br />\$b=$b\n<br />\n";
	if($a == $b)
		return 0;

	$a_type	= is_dir($this_dir."/".$a) ? 0 : returnFileEXT($a);	//0 indicates a folder, else you're given the file extension
	$b_type	= is_dir($this_dir."/".$b) ? 0 : returnFileEXT($b);

	$ab_type_cp	= strcasecmp($a_type, $b_type);
	$ab_cp		= strcasecmp($a, $b);

//	$debug_info	.= "\$a_type=$a_type\n<br />\$b_type=$b_type\n<br />\$ab_type_cp=$ab_type_cp\n<br />\$ab_cp=$ab_cp\n<br />\n";

	if($ab_type_cp == 0)
		return $ab_cp;

	return $ab_type_cp;
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

/* This function takes in the file name and retruns it, without the extension */
function returnFileName($nameoffile)
{
	$ext_len	= strlen(returnFileEXT($nameoffile));
	if($ext_len > 0)
		$nameoffile = substr($nameoffile, 0, -1*($ext_len+1));
	return $nameoffile;
}

/* This function takes in the file name and retruns its extention */
function returnFileEXT($nameoffile)
{
	global $debug_info;
	$all	= explode(".", $nameoffile);
	$ext	= $all[count($all)-1];
	return ($ext == $nameoffile) ? '' : strtolower($ext);
}
?>
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
?>