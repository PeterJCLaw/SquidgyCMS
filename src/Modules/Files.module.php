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
	var $showHiddenFiles = False; // whether or not to show files starting with .

	// TODO: do we even want to spec 'Files' here?
	function BlockFiles() {
		parent::__construct();
		$this->pathOffset = "Sites/$GLOBALS[site]/Files";
	}

	function Breadcrumbs($args)
	{
		$dir = $this->getRequestedFolder();
		$tidy = Path::tidy($dir);
		if (Path::isAbove($tidy))
		{
			log_error('BlockFiles->Breadcrumbs : Blocked an attempt to view an external folder', array('dir' => $dir, 'args' => $args));
			return False;
		}

		// TODO: get the actual page name from somewhere
		$rootItem = new FilesRootItem('$page-name', $this->pathOffset);
		$parts[] = $rootItem->getLinkHTML();

		if (!Path::areSame($tidy, '.'))
		{
			$before = '.';
			foreach (explode('/', $tidy) as $part)
			{
				$relativePath = Path::combine($before, $part);
				$item = new FilesItem($relativePath, $this->pathOffset);
				$parts[] = $item->getLinkHTML();
				$before = $relativePath;
			}
		}

		return implode(' / ', $parts);
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
		if (Path::isAbove($dir))
		{
			log_error('BlockFiles->Listing : Blocked an attempt to view an external folder', array('dir' => $dir, 'args' => $args));
			// TODO: do we want to 403 here?
			return '<strong>Access to paths above the root are not allowed!</strong>';
		}

		$files = Folder::scan("$this->pathOffset/$dir");

		$class = $this->getClass('listing');

		$out = '<ul class="'.$class.'">';
		foreach ($files as $file)
		{
			if ($this->isValid($file))
			{
				$item = new FilesItem("$dir/$file", $this->pathOffset);
				$out .= $item->getTemplate($this);
			}
		}
		$out .= '</ul>';
		return $out;
	}

	/**
	 * Filter out unwanted files.
	 */
	function isValid($filename)
	{
		// never show nothings, . or ..
		if (empty($filename) || $filename == '.' || $filename == '..')
		{
			return False;
		}

		// *nix hidden files
		if (!$this->showHiddenFiles && $filename[0] == '.')
		{
			return False;
		}

		return True;
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
	 * Returns the class for the list element.
	 */
	function getClass($methodName)
	{
		return 'files-'.strtolower($methodName);
	}

	/**
	 * Returns the type that this extension represents.
	 * Several extensions are grouped together into one type,
	 *  for instance jpg, jpeg and png are all images.
	 * @param ext The file extension.
	 * @param isDir Whether or not the item is a folder.
	 */
	function getIconTypeFor($ext, $isDir)
	{
		if ($isDir === True)
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
		$isDir = $item->isDir();
		$type = self::getIconTypeFor($ext, $isDir);
		return $this->getIconFor($type);
	}
}

/**
 * Represents a single item in a folder, be it a folder or a file.
 */
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
		$href = $this->getHref();
		$title = $this->getTitle();
		$image = $this->getImage($provider);
		$class = $this->getClass();
		return <<<TPL
<li class="$class">
	<a href="$href" title="$title">
		<img src="$image" />
		<p>$name_wrap</p>
	</a>
</li>
TPL;
	}

	/**
	 * Returns a full HTML link to the item.
	 * Used for the breadcrumbs.
	 */
	function getLinkHTML()
	{
		$href = $this->getHref();
		$name = $this->getName();
		$title = $this->getTitle();
		$link = "<a href=\"$href\" title=\"$title\">$name</a>";
		return $link;
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
	 * Returns a class for the list item
	 */
	function getClass()
	{
		$ext = Path::getExtension($this->getName());
		if ($ext == '' && $this->isDir())
		{
			$class = 'folder';
		}
		else
		{
			$class = 'file file-'.$ext;
		}
		return $class;
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
	 * Note that this is the (partial) url to the item, not an HTML anchor.
	 */
	function getHref()
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
 * Represents the root item in the breadcrumb listing.
 */
class FilesRootItem extends FilesItem
{
	/**
	 * Returns the link to the root page that we're actually viewing the files from.
	 */
	function getHref()
	{
		// TODO: $page/path/to/folder
		return '?';
	}

	function getTitle()
	{
		return 'Click to return to the original page';
	}

	function isDir()
	{
		return True;
	}
}

/**
 * A static class containing a collection of path related helper functions.
 */
class Path
{
	function Path()
	{
		trigger_error('Instance of static class '.__CLASS__.' not allowed.', E_USER_ERROR);
	}

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
	 * Compares the two paths to see if they point at the same file.
	 */
	function areSame($a, $b)
	{
		$a_tidy = self::tidy($a);
		$b_tidy = self::tidy($b);
		$areSame = $a_tidy === $b_tidy;
		return $areSame;
	}

	/**
	 * Inspects a path to determine if it points at a directory above itself or not.
	 * Examples:
	 *   ../.. => True
	 *   foo/bar => False
	 *   foo/.. => False
	 */
	function isAbove($path)
	{
		$self = self::areSame($path, '.');
		$isAbove = !self::isBelow($path) && !$self;
		return $isAbove;
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
		$isBelow = strlen($tidy) > 0 && $tidy[0] != '/' && $tidy !== '..'
		            && strpos($tidy, '../') === False
		            && strpos($tidy, '/..') === False;
		return $isBelow;
	}

	/**
	 * Tidies a path, removing duplicate slashes etc.
	 * Note that this is not the same as realpath,
	 *  as the resulting path is still relative, and is not required to exist.
	 * Here we do what a human does when inspecting a path,
	 *  that is look at each component in turn, rather than trying to
	 *  apply a collection of replacement tools.
	 */
	function tidy($path)
	{
		if (empty($path) || $path === '.' || $path === './')
		{
			return '';
		}

		$origParts = explode('/', $path);
		$parts = array();

		foreach ($origParts as $part)
		{
			// Ignore empties or current dirs.
			if ($part == '' || $part == '.')
			{
				continue;
			}

			if ($part == '..')
			{
				// If the previous value was not parent, then pop a value
				$count = count($parts);
				if ($count > 0 && $parts[$count-1] != '..')
				{
					array_pop($parts);
					continue;
				}
			}

			$parts[] = $part;
		}

		$tidy = implode('/', $parts);
		return $tidy;
	}
}

/**
 * A static class containing a collection of directory related helper functions.
 */
class Folder
{
	function Folder()
	{
		trigger_error('Instance of static class '.__CLASS__.' not allowed.', E_USER_ERROR);
	}

	/**
	 * Folder scanning function similar to PHP5 scandir, and uses that if available.
	 * @param directory The directory that will be scanned.
	 * @param sorting_order By default, the sorted order is alphabetical in ascending order.
	 *                      If the optional sorting_order is set to non-zero,
	 *                       then the sort order is alphabetical in descending order.
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

		if ($sorting_order !== 0)
		{
			rsort($files);
		}
		else
		{
			sort($files);
		}

		return $files;
	}
}
