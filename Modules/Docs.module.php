<?php
#name = Docs
#description = Display and manage site documents
#package = Core - optional
#type = content
###

class BlockDocs extends Block {
	function BlockDocs() {
		parent::__construct();
	}

	function Explore($args) {
		$browse = $this->Browse($agrs);
		$tree = $this->Tree($agrs);
		return <<<EXP
<div id="Docs-Explore">
$tree
$browse
</div>
EXP;
	}

	function Browse($args) {
		if(empty($args)) {
			$type	= 'auto';
			$size	= 3;
			$path	= '';
		} else
			$path = $args['path'];

		return '<div id="Docs-Browse">'.Docs::file_grid($path, $type, $size).'</div>';
	}

	function Tree($args) {
		global $ajax;
		if(empty($args))
			$path	= '';
		else
			$path = $args['path'];

		return '<div id="Docs-Tree">'.Docs::file_tree($path, $ajax).'</div>';
	}

	function ajax() {
		if(stristr($_GET['folder'], "FE_") === FALSE)
			return "<li>Folder does not exist!</li>";
		else
			return $this->Tree(array(substr($_GET['folder'], 3)));
	}
}

class AdminDocs extends Admin {
	function AdminDocs() {
		parent::__construct();
	}

	function printFormAdmin() {
	//somehow let them choose which files are publicly viewable

	}
}

class Docs {	//parent class for useful functions

	/* This function reads ALL the items in a directory and returns an array with this information */
	function Full_Dir_List($dir)
	{
		$results = FileSystem::Full_Dir_List($dir);
		usort($results, array("Docs", "PJCL_sort"));
		reset($results);
		return $results;
	}

	/* recursive function to explore the file / folder structure beneath
	 * retruns blank if nothing of note is found
	 */
	function file_tree($path, $ajax, $tabs='')
	{
		global $debug_info, $curr_file;

		$path_id = Docs::id_convert($path);

		if($path == './') {	//top level
			$path_id = '';
			$retval  = '<div id="FE_preload"><ul class="file_tree"><li class="collapsed"></li><li class="expanded"></li><li class="FE_empty"></li></ul></div>';
		} else
			$retval	= "";

		$paths_match = Docs::path_compare($path, $curr_file);
		$new_css	= TRUE;

		$display	= ($paths_match || $new_css) ? "" : " style=\"display: none;\"";

		$debug_info	.= "\$paths_match=".($paths_match ? 'TRUE' : 'FALSE' )."\n<br />\$display=$display\n<br />\n";

		$dir_contents = Docs::Full_Dir_List($path);

		if($dir_contents) {	//if there's something to show
			if($ajax < 2)	//if not being called from get_file_tree.php
				$retval	.= "\n$tabs<ul id=\"FE_$path_id\" class=\"file_tree\"$display>\n";

			if($ajax && $tabs != "" && !$paths_match)	//if ajax is enabled and not top level and paths don't match
				$retval	.= "	$tabs<li>Loading File Tree...</li>\n";
			else {
				$ajax	= round($ajax/2);
				foreach($dir_contents as $item_name)	//for each of the results in this folder
				{
					$href		= $item		= ($path_id == "" ? "" : "$path/").$item_name;
					$item_id	= Docs::id_convert($item);
					$curr_item	= ($item == $curr_file) ? TRUE : FALSE;
					$item_sub_val = $li_insert = "";
					$debug_info	.= "\$item=$item\n<br />\$item_id=$item_id\n<br />\n";

					if (is_file($item) || is_dir($item)) {
						if (is_dir($item)) {	//if its a folder
							$title			= "Go to $item_name";
							$li_ins_class	= "FE_empty";
							$item_sub_val	= Docs::file_tree($item, $ajax, $tabs."		");
							if($item_sub_val != '') {
								$li_ins_class	= ($paths_match ? (Docs::path_compare($item, $curr_file) ? "expanded" : "collapsed") : "collapsed");
								$item_sub_val	= ($curr_item ? "" : "&nbsp;&nbsp;<a href=\"?dir=$href\" title=\"$tit\">=></a>")."$item_sub_val\n	$tabs";
								$title			= "Expand $item_name";
								$href			= "javascript:toggle_FE('FE_$item_id', 'FE_LI_$item_id');\" class=\"js_link";
							}
							$li_insert		= " id=\"FE_LI_$item_id\" class=\"$li_ins_class\"";
							$debug_info		.= "\$item_sub_val=$item_sub_val\n<br />\$li_insert=$li_insert\n<br />\n";
						} else {
							$item_name	= FileSystem::returnFileName($item_name); //gives us just the name (no extension) of the file
							$title		= "Go to $item_name";
						}
						$thing_name	= "<a href=\"$href\" title=\"$title\">$item_name</a>";

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
	function make_file_listing($type, $file, $icon_size, $request)
	{

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

		if(!empty($request))
			$item	= $request . "/" . $file;
		else
			$item	= $file;
		$href	= Docs::fix_slashes($item);

		if(FileSystem::is_dir($item)) {
			$image_stuff	= "FSPHP_Images/".($type == 'auto' ? Docs::whats_inside($item) : $type)."_folder$sm.png\" style=\"width: ${icon_width}px; height: ${icon_height}px";
			$href			= "?dir=".$href;
			$alt			= "Folder image";
			$title			= "Click to open folder";
			$link_name		= Docs::nameFormat($file, 1, $line_len); //formats it for display
		} elseif(FileSystem::is_file($item)) {
			$name		= str_replace("_", " ", FileSystem::returnFileName($file)); //gives us just the name (no extension) of the image, without underscores
			$link_name	= Docs::nameFormat($name, 1, $line_len); //formats it for display

			if(in_array(FileSystem::returnFileExt($item), array("png", "jpg")))	//is it an image of the sort we can handle (jpg or png)
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
				$ext		= strtoupper(FileSystem::returnFileExt($item));

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

	/* This function shows the icons or images for the file or image browsing pages */
	function file_grid($Path, $type = "auto", $icon_size = 3)
	{
		global $ImagePath, $NewsPath, $FilePath, $itemsPerPage, $itemsInRow, $numberOfRows, $adapted_from, 	$cacheDir, $maxWidth, $maxHeight;
		global $who_copyright, $website_name_short, $copy_email_text, $copy_recipient, $copy_recip_gender, $copy_follow_text, $copy_fol_txt_img;
		global $nostalgic_images_footer, $n_i_f_link, $n_i_f_date_added;

		FSPHP::checkPHP(); //checks for gd support

		switch($type)
		{
			case "image":	//images
				$icon_size	= ($icon_size == 3) ? 2 : $icon_size;	//icon_size: 3- file dependant, 2- large, 1- small
				break;
			case "file":	//not images
				$icon_size	= ($icon_size == 3) ? 1 : $icon_size;
				break;
			case "auto":	//files of any type
			default:
				$type = "auto";
				$icon_size	= ($icon_size == 3) ? 1 : $icon_size;
				break;
		}

		if(array_key_exists('dir', $_REQUEST))
			$request = $_REQUEST['dir'];
		else	// no directory was specified so just show the default one from config.php
			$request = $Path;

		// found out if someone is trying to exploit it
		FSPHP::authoriseRequest($request);

		if($type == "image")	//get either just the images or all the files
			$results = FileSystem::Filtered_File_List($request, '.jpg, .png');
		else
			$results = Docs::Full_Dir_List($request);

		$split = array();
		$split = split("/", $request);

		$up = "$Path/";
		// FIXME -- this should use returnPath()
		for ($i = 1; $i < count($split) - 1; $i++) {	//don't add the first item (/$imagePath)
			$up = $up . $split[$i] . "/";
		}
		$up = Docs::fix_slashes($up);

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
			$RET_VAL	.= Docs::make_file_listing($type, $file, $icon_size, $request);
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
		return $RET_VAL;
	}

	/* This function returns the the type of files in the directory passed */
	function fix_slashes($path)
	{	//strip precending .// and change any // to /
		$path	= str_replace(array(".//", '//'), array("", '/'), $path);

		if($path[0] == '/')	//remove preceding /
			$path	= substr($path, 1);

		if($path[strlen($path)-1] == '/')	//remove trailing /
			$path	= substr($path, 0, -1);

		return $path;
	}

	/* This function returns the the type of files in the directory passed */
	function whats_inside($dir)
	{
		$dir_files	= FileSystem::Filtered_File_List($dir, -1);
		$dir_images	= FileSystem::Filtered_File_List($dir, '.jpg, .png');

		return (2 * count($dir_images)) > count($dir_files) ? "image" : "file";
	}

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
	function path_compare($path, $file)
	{
		global $debug_info;

		$path	= str_replace(".//", "", $path);

		$debug_info .= "\$path=$path\n<br />\$file=$file\n<br />\n";

		if($path == $file || $path == "./")
			return TRUE;

		$length		= strlen($path);

		$curr_s		= substr($file, 0, $length);

		$debug_info .= "\$curr_s=$curr_s\n<br />\$length=$length\n<br />\n";

		return ($path == $curr_s);	//if they match then the folder gets expanded
	}

	/* This function gets the name of the object to fit onto lines 13 characters wide */
	function nameFormat($name, $line, $width)
	{
		global $debug_info;
		if(function_exists('wordwrap')) {
			$debug_info	.= "wordwrap exists, yay\n<br />\n";
			return wordwrap($name, $width, "<br />", TRUE);
		}

		$namelen = strlen($name);
		$critlen = $width * $line;

		if($namelen > $critlen) {	//if its too long for this line
			$name = Docs::nameFormat($name, ($line+1), $width);	//see if it fits on the next
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

	/* This function is used in conjunction with usort - it sorts based on whether a file is a folder, then by extension, then alphabetically */
	function PJCL_sort($a, $b)
	{
		global $debug_info, $this_dir;

	//	$debug_info	.= "\$this_dir=$this_dir\n<br />\$a=$a\n<br />\$b=$b\n<br />\n";
		if($a == $b)
			return 0;

		$a_type	= is_dir($this_dir."/".$a) ? 0 : FileSystem::returnFileExt($a);	//0 indicates a folder, else you're given the file extension
		$b_type	= is_dir($this_dir."/".$b) ? 0 : FileSystem::returnFileExt($b);

		$ab_type_cp	= strcasecmp($a_type, $b_type);
		$ab_cp		= strcasecmp($a, $b);

	//	$debug_info	.= "\$a_type=$a_type\n<br />\$b_type=$b_type\n<br />\$ab_type_cp=$ab_type_cp\n<br />\$ab_cp=$ab_cp\n<br />\n";

		if($ab_type_cp == 0)
			return $ab_cp;

		return $ab_type_cp;
	}

}
?>