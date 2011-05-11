<?php
#name = Files
#description = Completely rewritten replacement for Docs
#package = Core - optional
#type = content
###

/*
class AdminFiles extends Admin {
	function AdminFiles() {
		parent::__construct();
		$this->complex_data = true;
		$this->data_key_column = 'id';
		$this->get_data();
	}

	function printFormAdmin() {
	}

	function submit($content=0) {
	}
}
//*/

class BlockFiles extends Block {

	var $sizes = array(128, 64);
	var $size = 128;

	var $pathOffset; // "Sites/$site/Files/$whateverTheySpecInThePage";

	// TODO: do we even want to spec 'Files' here?
	function BlockFiles() {
		parent::__construct();
		$this->pathOffset = "Sites/$GLOBALS[site]/Files";
	}

	function Listing($args)
	{
		if (!empty($args[0]))
		{
			$folder = $args[0];
			$folder = Path::tidy($folder);
			if (!Path::isBelow($folder))
			{
				log_error('BlockFiles->Listing : Invalid folder specified', array('folder' => $folder, 'args' => $args));
				return False;
			}

			$this->pathOffset = "$this->pathOffset/$folder";
		}

		$dir = $this->getRequestedFolder();
		if (!Path::isBelow($dir))
		{
			log_error('BlockFiles->Listing : Blocked an attempt to view an external folder', array('dir' => $dir, 'args' => $args));
			// TODO: do we want to 403 here?
			return '<strong>Access to paths not below the root are not allowed!</strong>';
		}

		$files = Folder::scan("$this->pathOffset/$dir");

		$out = '<ul class="files-listing">';
		foreach ($files as $file)
		{
			// TODO: filtering
			$out .= $this->getTemplate("$dir/$file");
		}
		$out .= '</ul>';
		return $out;

	}

	/**
	 * Wraps the given item in the template for display.
	 * @param relativePath The path to the item, relative to our localised filesystem root.
	 */
	function getTemplate($relativePath)
	{
		$itemName = basename($relativePath);
		$itemName_wrap = wordwrap($itemName, 13, "<br />\n");
		$link = $this->getLinkFor($relativePath);
		$image = $this->getImageFor($itemName);
		return <<<TPL
<li>
	<img src="$image" title="Click to download/view $itemName" />
	<p>$itemName_wrap</p>
</li>
TPL;
	}

	/**
	 * Returns the real path to the item.
	 */
	function getRealPath($itemName)
	{
		return "$this->pathOffset/$itemName";
	}

	/**
	 * Returns the link to the item, be it a file or folder, adjusted for immediate use.
	 */
	function getLinkFor($itemName)
	{
		// TODO: do we want to make these links absolute?
		$link = $this->getRealPath($itemName);
		if (is_dir($link))
		{
			// TODO: make this work, this is wrong
			return $itemName;
		}
		return $link;
	}

	/**
	 * Returns the folder that the user requested, tidied.
	 */
	function getRequestedFolder()
	{
		// TODO: support $page/path/to/folder
		$dir = empty($_GET['dir']) ? '.' : $_GET['dir'];
		$tidy = Path::tidy($dir);
		return $tidy;
	}

	/**
	 * Returns the type that this extension represents.
	 * Several extensions are grouped together into one type,
	 *  for instance jpg, jpeg and png are all images.
	 */
	function getIconTypeFor($ext)
	{
		if (empty($ext))
		{
			return 'folder-blue';
		}

		// TODO: does this want to be external, in a config file perhaps?
		$typesMap = array(
			'application-pdf'               => array('pdf'),
			'application-vnd.ms-excel'      => array('xls', 'xlsx', 'ods'),
			'application-vnd.ms-powerpoint' => array('ppt', 'pptx', 'odp'),
			'application-vnd.ms-word'       => array('doc', 'docx', 'odt'),
			'image-jpeg2000'                => array('jpg', 'jpeg', 'png', 'ico', 'gif'),
			'audio-x-pn-realaudio-plugin'   => array('mp3', 'wma', 'aac'),
			'text-plain'                    => array('txt', 'ini'),
			'uri-mms'                       => array('avi', 'mkv', 'mp4', 'wmv'),
			'text-xml'                      => array('xml', 'htm', 'html')
		);

		foreach ($typesMap as $type => $exts)
		{
			if (in_array($ext, $exts))
			{
				return $type;
			}
		}
		return 'unknown';
	}

	/**
	 * Returns the path to an icon for an extension type.
	 * We look first for an icon for that type in the required size,
	 *  then try other sizes, before defaulting to the 'Unknown' icon.
	 */
	function getIconFor($type)
	{
		// TODO: support other themes?
		$base = "Modules/Files/Oxygen";
		$file = "$base/$this->size/$type.png";

		if (is_file($file))
		{
			return $file;
		}

		foreach ($this->sizes as $size)
		{
			$file = "$base/$size/$type.png";
			if (is_file($file))
			{
				return $file;
			}
		}

		if ($type != 'unknown')
		{
			return $this->getIconFor('unknown');
		}

		// should never get here...
		return False;
	}

	/**
	 * Returns the path to an image to display for a file.
	 * We display an icon based on the type of the file.
	 */
	function getImageFor($name)
	{
		$ext = Path::getExtension($name);
		$type = self::getIconTypeFor($ext);
		return $this->getIconFor($type);
	}
}

/**
 * A static class containing a collection of path related helper functions.
 */
class Path
{
	/**
	 * Combine an arbitrary number of paths into one path, using '/' as a separator.
	 */
	function combine()
	{
		$parts = func_get_args();
		$path = implode('/', $parts);
		return self::tidy($path);
	}

	function getExtension($path)
	{
		$info = pathinfo($path);
		$extension = isset($info['extension']) ? $info['extension'] : '';
		return $extension;
	}

	function getFileNameWithoutExtension($path)
	{
		$info = pathinfo($path);
		$base = basename($path, $info['extension']);
		return $base;
	}

	/**
	 * Inspects a path to determine if it points at a directory below itself or not.
	 * Examples:
	 *   ../.. => False
	 *   foo/bar => True
	 *   foo/.. => False
	 */
	function isBelow($path)
	{
		$tidy = self::tidy($path);
		$isBelow = $tidy[0] != '/' && $tidy !== '..'
		            && strpos($tidy, '../') === False
		            && strpos($tidy, '/..') === False;
		return $isBelow;
	}

	/**
	 * Tidies a path, removing duplicate slashes etc.
	 * Note that this is not the same as realpath,
	 *  as the resulting path is still relative, and is not required to exist.
	 */
	function tidy($path)
	{
		if (substr($path, 0, 2) === './')
		{
			$path = substr($path, 2);
		}

		while (strpos($path, '/./') !== False)
		{
			$path = str_replace('/./', '', $path);
		}

		while (strpos($path, '//') !== False)
		{
			$path = str_replace('//', '/', $path);
		}

		while (strpos($path, '../', 1) !== False)
		{
			$path = preg_replace('/[^\/]+\/\.\.\//', '', $path);
		}

		return $path;
	}
}

/**
 * A static class containing a collection of directory related helper functions.
 */
class Folder
{
	/**
	 * Folder scanning function similar to PHP5 scandir, and uses that if available.
	 * @param directory The directory that will be scanned.
	 * @param directory By default, the sorted order is alphabetical in ascending order.
	 *                  If the optional sorting_order is set to non-zero,
	 *                   then the sort order is alphabetical in descending order.
	 * @return Returns an array of filenames on success, or FALSE on failure.
	 *         If directory is not a directory, then boolean FALSE is returned,
	 *          and an error of level E_WARNING is generated.
	 */
	function scan($directory, $sorting_order = 0)
	{
		if (function_exists('scandir'))
		{
			return scandir($directory, $sorting_order);
		}

		$handle = opendir($directory);

		if ($handle === False)
		{
			return False;
		}

		$files = array();

		while ( ($filename = readdir($handle)) !== False )
		{
			$files[] = $filename;
		}

		closedir($handle);

		return $files;
	}
}
