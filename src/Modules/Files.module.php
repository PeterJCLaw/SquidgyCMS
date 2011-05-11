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
			$item = new FilesItem("$dir/$file", $this->pathOffset);
			$out .= $item->getTemplate($this);
		}
		$out .= '</ul>';
		return $out;

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
	 * Callback to get the image for a FilesItem.
	 * Architected thus so that changing the image should be relatively easy,
	 * and because the sizing stuff shouldn't be in the FilesItem instance.
	 */
	function getImageFor($item)
	{
		$name = $item->getName();
		$ext = Path::getExtension($name);
		$type = self::getIconTypeFor($ext);
		return $this->getIconFor($type);
	}
}

class FilesItem
{
	var $isDir = null;
	var $name;
	var $pathOffset;
	var $relativePath;

	function FilesItem($relativePath, $pathOffset)
	{
		$this->relativePath = Path::tidy($relativePath);
		$this->pathOffset = Path::tidy($pathOffset);
	}

	/**
	 * Wraps the given item in the template for display.
	 * @param provider An image provider. Must have a method called getImageFor that accepts a FilesItem as its only argument.
	 */
	function getTemplate($provider)
	{
		$name = $this->getName();
		$name_wrap = wordwrap($name, 13, "<br />\n");
		$link = $this->getLink();
		$title = $this->getTitle();
		$image = $this->getImage($provider);
		return <<<TPL
<li>
	<a href="$link" title="$title">
		<img src="$image" />
		<p>$name_wrap</p>
	</a>
</li>
TPL;
	}

	/**
	 * Returns the real path to the item.
	 */
	function getRealPath()
	{
		return "$this->pathOffset/$this->relativePath";
	}

	/**
	 * Returns whether or not this FilesItem is a directory.
	 */
	function isDir()
	{
		// TODO: cache this result
		$real = $this->getRealPath();
		$isDir = is_dir($real);
		return $isDir;
	}

	/**
	 * Returns a title for a link to the item
	 */
	function getTitle()
	{
		$isDir = $this->isDir();
		$name = $this->getName();
		$title = 'Click to '.($isDir ? 'open' : 'download/view').' '.$name;
		return $title;
	}

	/**
	 * Returns the name of the item.
	 */
	function getName()
	{
		// TODO: cache this?
		$name = basename($this->relativePath);
		return $name;
	}

	/**
	 * Returns the link to the item, be it a file or folder, adjusted for immediate use.
	 */
	function getLink()
	{
		// TODO: do we want to make these links absolute?
		if ($this->isDir())
		{
			return "?dir=$this->relativePath";
		}
		$link = $this->getRealPath();
		return $link;
	}

	/**
	 * Returns the path to an image to display for a file, using the given provider.
	 * @param provider An image provider. Must have a method called getImageFor that accepts a FilesItem as its only argument.
	 */
	function getImage($provider)
	{
		$this->_checkImageProvider($provider);
		$image = $provider->getImageFor($this);
		return $image;
	}

	function _checkImageProvider($provider)
	{
		$exists = method_exists($provider, 'getImageFor');
		if (!$exists)
		{
			trigger_error('Image provider does not contain required method "getImageFor".', E_USER_ERROR);
		}
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
		if (empty($path))
		{
			return '';
		}

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
