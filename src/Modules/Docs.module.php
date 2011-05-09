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
		$browse = $this->Browse($args);
		$tree = $this->Tree($args);
		return <<<EXP
<div id="Docs-Explore">
$tree
$browse
</div>
EXP;
	}

	function Browse($args) {
		list($path, $size, $type) = array();
		extract($args, EXTR_IF_EXISTS);

		if(empty($type))
			$type	= 'auto';

		if(empty($size))
			$size	= 3;

		$path = $this->get_path($path);
		return '<div id="Docs-Browse">'.Docs::file_grid($path, $type, $size).'</div>';
	}

	function Tree($args) {
		global $ajax;

		return '<div id="Docs-Tree">'.Docs::file_tree($args['path'], $this->get_path($args['path']), $ajax).'</div>';
	}

	function get_path($path) {
		if(!empty($_GET['dir']))
			return $_GET['dir'];
		else
			return $path;
	}

	function ajax() {
		if(stristr($_GET['folder'], "FE_") === FALSE)
			return "<li>Folder does not exist!</li>";
		else
			return $this->Tree(array('path' => substr($_GET['folder'], 3)));
	}
}

class AdminDocs extends Admin {
	function AdminDocs() {
		parent::__construct();
	}

	function select_box($id, $options, $default=FALSE) {
		$selector_box = "<select id=\"$id\"";
		foreach($options as $name => $value) {
			if($default !== FALSE && $name == $default)
				$value .= '" selected="selected';
			$selector_box .= "\n	<option value=\"$value\">$name</option>";
		}
		$selector_box .= "\n</select>";
	}

	function printFormAdmin() { ?>
<table><tr>
	<th>Path:</th>
	<!-- th title="The authorisation level required to view the files in that folder">Auth. level</th -->
	<th title="Whether this path can be seen">Enabled</th>
	</tr>
	<?php
		$this->get_data();
		$paths = Docs::recursive_get_folders('');
		//$options = array('Everyone'=>USER_GUEST, );
		foreach($paths as $path) {
			$path  = Docs::fix_slashes($path);
			if(!Docs::is_dir($path) || Docs::reserved_dir($path))
				continue;
			$input = '<input type="checkbox"';
			if(in_array($path, $this->data))
				$input .= ' checked="checked"';
			$input .= " name=\"enable[$path]\" />";

		//	$select = $this->select_box($options, $path_auth_level);
			echo "<tr>\n	<td>$path</td><td>$input</td>\n</tr>";
		}
	?>
</table>
<?php }

	function submit($content=0) {
		foreach($_POST['enable'] as $path => $enabled) {
			if($enabled)
				array_push($this->data, $path);
		}
		$this->put_data();
	}
}

class Docs {	//parent class for useful functions

	/* This function tells if the path is a reserved one */
	function reserved_dir($path)
	{
		foreach(array('Data', 'Users') as $reserved)
			if(Docs::path_compare($reserved, $path))
				return true;
		$bits = explode('/',$path);
		return in_array('Thumbs', $bits);
	}

	/* This function recursively gets all the subfolders of a folder */
	function recursive_get_folders($orig_dir)
	{
		$dir = $GLOBALS['site_root'].'/'.$orig_dir;

		$results = array();
		if(!is_dir($dir) || !is_readable($dir))
			return $results;

		$this_dir = $dir;
		$handler = opendir($dir);

		while($file = readdir($handler)) {
			if($file != '.' && $file != '..'  && is_dir("$dir/$file")) {
				array_push($results, "$orig_dir/$file");
			//	print_r(Docs::recursive_get_folders("$orig_dir/$file"));
				$results = array_merge($results, Docs::recursive_get_folders("$orig_dir/$file"));
			}
		}
		closedir($handler);
		sort($results);
		reset($results);
		return $results;
	}

	/* This function checks the paths in an array against the list of allowed ones */
	function check_paths($query_paths, $prefix='')
	{
		$allowed_paths = FileSystem::get_file_rtrim($GLOBALS['data_root'].'/docs.data');
		$out_paths = array();
		if(!in_array($prefix, $allowed_paths))
			return array();
		foreach($query_paths as $path)
			if( in_array($prefix.'/'.$path, $allowed_paths) || Docs::is_file($prefix.'/'.$path) )
				array_push($out_paths, $path);
		return $out_paths;
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
		$results = Docs::check_paths(FileSystem::Full_Dir_List($GLOBALS['site_root'].'/'.$dir), $dir);
		usort($results, array("Docs", "tree_sort"));
		reset($results);
		return $results;
	}

	/* This function reads only the items in a directory that containt the strings in $filter and returns an array with this information */
	function Filtered_Dir_List($dir)
	{
		return Docs::check_paths(FileSystem::Filtered_Dir_List($GLOBALS['site_root'].'/'.$dir), $dir);
	}

	/* recursive function to explore the file / folder structure beneath
	 * retruns blank if nothing of note is found
	 * base is the current folder to use as a base
	 * path is the position in the file tree to be shown as open
	 * ajax is a signal of the ajax status - whether this is an ajax call or not
	 * tabs is the level of tabs to include in the source to make it nice
	 */
	function file_tree($base, $path, $ajax, $tabs='')
	{
		$base = Docs::fix_slashes($base);
		$path = Docs::fix_slashes($path);
		$base_id = Docs::id_convert($base);
		print_r(array($base, $path, $base_id));

		if(in_array($base, array('', './'))) {	//top level <- fix this
			$base_id = '';
			$retval  = '<div id="FE_preload"><ul class="file_tree"><li class="collapsed"></li><li class="expanded"></li><li class="FE_empty"></li></ul></div>';
		} else
			$retval	= "";

		$paths_match = Docs::path_compare($base, $path);
	//	$new_css	= TRUE;

		$display	= ($paths_match || $new_css) ? '' : ' style="display: none;"';

		log_info('file_tree', array('paths_match' => $paths_match, 'display' => $display));

		$dir_contents = Docs::Full_Dir_List($base);

		if(!empty($dir_contents)) {	//if there's something to show
			if($ajax < 2)	//if not an ajax call
				$retval	.= "\n$tabs<ul id=\"FE_$base_id\" class=\"file_tree\"$display>\n";

			if($ajax && $tabs != "" && !$paths_match)	//if ajax is enabled and not top level and paths don't match
				$retval	.= "	$tabs<li>Loading File Tree...</li>\n";
			else {
				$ajax	= round($ajax/2);
				foreach($dir_contents as $item_name)	//for each of the results in this folder
				{
					$href		= $item		= ($base_id == "" ? "" : "$path/").$item_name;
					$item_id	= Docs::id_convert($item);
					$curr_item	= ($item == $curr_file) ? TRUE : FALSE;
					$item_sub_val = $li_insert = "";
					log_info('file_tree.foreach', array('item' => $item, 'item_id' => $item_id));

					if (Docs::is_file($item) || Docs::is_dir($item)) {
						if (Docs::is_dir($item)) {	//if its a folder
							$title			= "Go to $item_name";
							$li_ins_class	= "FE_empty";
							$item_sub_val	= Docs::file_tree($item, $path, $ajax, $tabs."		");
							if($item_sub_val != '') {
								$li_ins_class	= ($paths_match ? (Docs::path_compare($item, $curr_file) ? "expanded" : "collapsed") : "collapsed");
								$item_sub_val	= ($curr_item ? "" : "&nbsp;&nbsp;<a href=\"?dir=$href\" title=\"$tit\">=></a>")."$item_sub_val\n	$tabs";
								$title			= "Expand $item_name";
								$href			= "javascript:toggle_FE('FE_$item_id', 'FE_LI_$item_id');\" class=\"js_link";
							}
							$li_insert		= " id=\"FE_LI_$item_id\" class=\"$li_ins_class\"";
							log_info('item_sub_val' => $item_sub_val, 'li_insert' => $li_insert));
						} else {
							$item_name	= FileSystem::returnFileName($item_name); //gives us just the name (no extension) of the file
							$title		= "Go to $item_name";
						}
						$thing_name	= "<a href=\"$href\" title=\"$title\">$item_name</a>";

						if($curr_item)
							$thing_name	= "<strong>$thing_name</strong>";

						$retval	.= "	$tabs<li".$li_insert.">".$thing_name . $item_sub_val."</li>\n";
					}
				}
			}

			if($ajax < 2)	//if not being called from get_file_tree.php
				$retval	.= "$tabs</ul>";
		}
		else
			return '<span style="display: none;">No files to display</span>';

		log_info('path' => $path, 'base_id' => $base_id, 'tabs' => "/$tabs/"));

		return $retval;
	}
	/* This function makes the individual file listing for a file in the file explorer */
	function make_file_listing($item, $file, $type, $icon_size)
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

		$href	= Docs::fix_slashes($item);

		if(Docs::is_dir($item)) {
			$image_stuff	= "Modules/Docs/".($type == 'auto' ? Docs::whats_inside($item) : $type)."_folder$sm.png\" style=\"width: ${icon_width}px; height: ${icon_height}px";
			$href			= "?dir=".$href;
			$alt			= "Folder image";
			$title			= "Click to open folder";
			$link_name		= Docs::nameFormat($file, 1, $line_len); //formats it for display
		} elseif(Docs::is_file($item)) {
			$real_path	= FileSystem::joinPath($GLOBALS['site_root'], $item);
			$name		= str_replace("_", " ", FileSystem::returnFileName($file)); //gives us just the name (no extension) of the image, without underscores
			$link_name	= Docs::nameFormat($name, 1, $line_len); //formats it for display

			if(in_array(FileSystem::returnFileExt($item), array("png", "jpg")))	//is it an image of the sort we can handle (jpg or png)
			{
				$size_cache = FileSystem::joinPath(FSPHP::returnPath($real_path), 'Thumbs', FSPHP::returnName($item).".dim");

				// if the cache file doesn't exist make it
				if(!file_exists($size_cache))
					FSPHP::writeCacheFile($real_path, $size_cache);

				// the cache file either already existed or we just made it so use it
				$sizes	= explode(" ", FSPHP::readCacheFile($size_cache));
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

				$image_stuff	= "Modules/Docs/".(file_exists("Modules/Docs/$ext.png") ? $ext : "UnknownFile").".png\" style=\"width: ${icon_width}px; height: ${icon_height}px";
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
	function file_grid($request, $type = "auto", $icon_size = 3)
	{
		global $ImagePath, $itemsPerPage, $itemsInRow, $numberOfRows, $adapted_from, 	$cacheDir, $maxWidth, $maxHeight;
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
		<a href="?"><img src="Modules/Docs/home.png" style="width: 32px height: 32px" alt="Home" /></a>
		<a href="?dir='.$up.'"><img src="Modules/Docs/up.png" style="width: 32px height: 32px" alt="Up" /></a>
	</p>
';

		if(!$toplevel)
			$RET_VAL	.= $nav;

		$RET_VAL	.= "	<ul class=\"PHPlist $size_class\">\n		";

		$itemsPerPage = $itemsInRow * $numberOfRows;

		foreach($results as $file) {	//spit as many images as there are
			if(!empty($request))
				$item	= $request . "/" . $file;
			else
				$item	= $file;

			$RET_VAL	.= '			<li>';
			$RET_VAL	.= Docs::make_file_listing($item, $file, $type, $icon_size);
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

	/* compare the base passed to the current path by cylcing through the
	 * directory structure until we either run out of folders or they differ
	 */
	function path_compare($base, $path)
	{
		$base	= Docs::fix_slashes($base);
		$path	= Docs::fix_slashes($path);

		log_info('path_compare', array('base' => $base, 'path' => $path));

		if(in_array($base, array($path, "./", '')))
			return TRUE;

		$B	= explode('/', $base);
		$P	= explode('/', $path);

		if( count($B) >= count($P) )	//base shouldn't be longer than path
			return FALSE;

		for($i=0; $i<count($B); $i++) {	//keep going until they differ
			if($B[$i] != $P[$i])
				return FALSE;
		}
		return TRUE;	//else they're the same
	}

	/* This function gets the name of the object to fit onto lines 13 characters wide */
	function nameFormat($name, $line, $width)
	{
		if(function_exists('wordwrap')) {
			log_info('wordwrap exists, yay');
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
	function tree_sort($a, $b)
	{
	//	global $this_dir;

	//	log_info(null, array('this_dir' => $this_dir, 'a' => $a, 'b' => $b));
		if($a == $b)
			return 0;

		$a_type	= Docs::is_dir($a) ? 0 : FileSystem::returnFileExt($a);	//0 indicates a folder, else you're given the file extension
		$b_type	= Docs::is_dir($b) ? 0 : FileSystem::returnFileExt($b);

		$ab_type_cp	= strcasecmp($a_type, $b_type);
		$ab_cp		= strcasecmp($a, $b);

	//	log_info(null, array('a_type' => $a_type, 'b_type' => $b_type, 'ab_type_cp' => $ab_type_cp, 'ab_cp' => $ab_cp));

		if($ab_type_cp == 0)
			return $ab_cp;

		return $ab_type_cp;
	}

}
