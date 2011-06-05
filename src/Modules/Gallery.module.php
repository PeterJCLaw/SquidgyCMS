<?php
#name = Gallery
#description = Image gallery based on the Files module
#package = Core - optional
#dependencies = Files
#type = content
###

// TODO: do we want to be able to have distinct options for the gallery than the files?

// TODO: implement dependencies properly so that we don't need this:
require_once('Files.module.php');

class BlockGallery extends BlockFiles {

	var $cacheFolder;

	function BlockGallery()
	{
		parent::__construct();
		$this->cacheFolder = Path::combine($this->data_root, 'gallery-cache');
		if (!is_dir($this->cacheFolder))
		{
			mkdir($this->cacheFolder);
		}
	}

	/**
	 * Wraps the given item in the template for display.
	 * @param provider An image provider. Must have a method called getImageFor that accepts a FilesItem as its only argument.
	 */
	function buildListingEntry($item)
	{
		if (!self::isResizable($item->getRealPath()))
		{
			return parent::buildListingEntry($item);
		}

		$name = $item->getName();
		$ext = Path::getExtension($name);
		$name_wrap = wordwrap($name, 13, "<br />\n");
		$href = $item->getHref();
		$title = $item->getTitle();
		$image = self::getImageLinkFor($item);
		$class = $item->getClass();
		return <<<TPL
<li class="$class">
	<a href="$href" title="$title">
		<div class="img" style="width: {$this->size}px;">
			<div style="height: {$this->size}px; width: {$this->size}px;">
				<img src="$image" alt="A $ext file." />
			</div>
		</div>
		<p>$name_wrap</p>
	</a>
</li>
TPL;
	}

	/**
	 * Create a link to the ajax call which we use to serve up
	 *  a small version of the file in question.
	 */
	function getImageLinkFor($item)
	{
		$queryParams = array('type' => 'block', 'module' => 'Gallery');
		$queryParams['width'] = $queryParams['height'] = $this->size;
		$queryParams['path'] = $item->getRealPath();

		$url = 'ajax.php';
		$query = toQueryString($queryParams);
		return $url.'?'.$query;
	}

	/**
	 * Returns the class for the list element.
	 * Overridden to add ourselves, but not replacing
	 * so that we can inherit most of the styling.
	 */
	function getClass($methodName)
	{
		$myClass = 'gallery-'.strtolower($methodName);
		$parentClass = parent::getClass($methodName);
		$class = $myClass.' '.$parentClass;
		return $class;
	}

	function ajax()
	{
		$width = $height = $path = null;
		extract($_GET, EXTR_IF_EXISTS);

		$thumbPath = null;

		if (self::isResizable($path))
		{
			$image = new GalleryImage($path);
			$thumbPath = $image->getOrCreateResizedImage($this->cacheFolder, $width, $height);
		}

		// something went wrong, so fall back to using an icon.
		if ($thumbPath === null)
		{
			$item = new FilesItem($path, '');
			$thumbPath = parent::getImageFor($item);
		}

		Header("Location: $thumbPath");
	}

	/**
	 * Static method that determines the resized size of an image based on a box to resize it into.
	 * @param boxWidth The width of the box to fit the dimensions in
	 * @param boxHeight The height of the box to fit the dimensions in
	 * @param origWidth The width of the item to be resized
	 * @param origHeight The height of the item to be resized
	 * @returns An array with keys 'width' and 'height' both guaranteed
	 * to be less than their respective dimension of the fit box,
	 * but in the same ratio as the original width & height.
	 */
	function fitInBox($boxWidth, $boxHeight, $origWidth, $origHeight)
	{
		$boxRatio = $boxWidth / $boxHeight;
		$origRatio = $origWidth / $origHeight;

		if ($boxRatio > $origRatio)
		{
			// the height is the determining dimension
			$h = $boxHeight;
			$w = $boxHeight * $origRatio;
		}
		else
		{
			// the width is the determining dimension
			$w = $boxWidth;
			$h = $boxWidth / $origRatio;
		}

		assert($w <= $boxWidth); // width too large
		assert($h <= $boxHeight); // height too large

		$ret = array('width' => $w, 'height' => $h);
		return $ret;
	}

	/**
	 * Static method that checks whether the file given can be resized on the fly.
	 * This takes into account the actual MIME type of the file,
	 *  and the availability of gd addons.
	 * @param path The path to the file in question.
	 * @returns (bool) Whether or not the file can be resized.
	 */
	function isResizable($path)
	{
		$supportedTypes = self::getSupportedTypes();
		// Need jpeg to create the new image
		$isResizable = in_array('image/jpeg', $supportedTypes['mimes']);
		if ($isResizable)
		{
			$ext = Path::getExtension($path);
			$isResizable = in_array($ext, $supportedTypes['extensions']);
		}
		if ($isResizable)
		{
			$image = new GalleryImage($path);
			$mime = $image->getMIME();
			$isResizable = in_array($mime, $supportedTypes['mimes']);
		}
		return $isResizable;
	}

	/**
	 * Static method that finds out what flavours of image we can support by querying the gd library.
	 * @returns An array with two keys, 'mimes' and 'extensions',
	 *  each containing a list of MIME types and file extensions respectively that we can resize.
	 */
	function getSupportedTypes()
	{
		// this gets called for every file, so cache the result
		static $types = null;
		if ($types === null)
		{
			$info = gd_info();
			$mimes = array();
			$extensions = array();
			// ensure valid return value
			$types = array('mimes' => $mimes, 'extensions' => $extensions);

			if ( empty($info['GD Version']) )
			{
				return $types;
			}

			if ( !empty($info['GIF Read Support']) && !empty($info['GIF Create Support']) )
			{
				$mimes[] = 'image/gif';
				$extensions[] = 'gif';
			}

			// Support PHP > 5.3 AND PHP < 5.3
			if ( !empty($info['JPEG Support']) || !empty($info['JPG Support']) )
			{
				$mimes[] = 'image/jpeg';
				$extensions[] = 'jpeg';
				$extensions[] = 'jpg';
			}

			if ( !empty($info['PNG Support']) )
			{
				$mimes[] = 'image/png';
				$extensions[] = 'png';
			}

			// fill the real values
			$types = array('mimes' => $mimes, 'extensions' => $extensions);
		}

		return $types;
	}
}

/**
 * Class akin to the FilesItem, but for images in the Gallery.
 * As such it does not inherit from FilesItem, nor contain any of the templating functionality.
 * Instead it is used to help resize the images.
 * Note that this class does no validation of the availability of the image functions it may try to use.
 */
 // TODO: review -- should we inherit from FilesItem?
 // TODO: review how we figure out which types are supported by using MIME types instead of file extensions.
class GalleryImage
{
	var $path;
	var $info;

	function GalleryImage($path)
	{
		$this->path = $path;
		$this->info = getimagesize($this->path);
	}

	/**
	 * Gets a resized version of the image by first searching the cache
	 *  for a suitable image, and if that fails by creating one, and then
	 *  storing that new image in the cache.
	 * @param cacheFolder The cache folder to uses.
	 * @param width The (maximum) width of the new image.
	 * @param height The (maximum) height of the new image.
	 * @param keepRatio Whether or not to preserve the current aspect ratio of the image.
	 * @returns The path to the resized image file.
	 */
	function getOrCreateResizedImage($cacheFolder, $width, $height, $keepRatio = True)
	{
		if ($keepRatio === True)
		{
			$newSize = $this->getSizeInBox($width, $height);
			$height = $newSize['height'];
			$width = $newSize['width'];
		}

		$cachedName = $this->getCachedName($width, $height);
		$cachedPath = "$cacheFolder/$cachedName";

		// if it's missing then create it if we can.
		if (!is_file($cachedPath) && is_writable($cacheFolder))
		{
			$resource = $this->createResizedImage($width, $height, $keepRatio);
			if ($resource !== null)
			{
				// all cached images are jpg.. it's just simpler that way.
				// TODO: what if jpeg support isn't available?
				imagejpeg($resource, $cachedPath);
				imagedestroy($resource);
			}
			else
			{
				log_error('GalleryImage->getOrCreateResizedImage: Unable to create resized image!');
			}
		}
		elseif (!is_writable($cacheFolder))
		{
			log_error('GalleryImage->getOrCreateResizedImage: Cache folder not writable!', array('cacheFolder' => $cacheFolder));
			return null;
		}
		return $cachedPath;
	}

	/**
	 * Creates a new image that is a copy of the current one,
	 *  resized to fit in the specified box.
	 * @param width The (maximum) width of the new image.
	 * @param height The (maximum) height of the new image.
	 * @param keepRatio Whether or not to preserve the current aspect ratio of the image.
	 * @returns A handle to the new image, or null if it couldn't be created.
	 */
	function createResizedImage($width, $height, $keepRatio = True)
	{
		if ($keepRatio === True)
		{
			$newSize = $this->getSizeInBox($width, $height);
			$height = $newSize['height'];
			$width = $newSize['width'];
		}

		$newImage = imagecreatetruecolor($width, $height);
		$curImage = $this->loadFromFile();

		// TODO: do we need to imagedestroy these anywhere?

		// can go no further if we don't have the resources.
		if ($curImage === null || $newImage === null)
		{
			log_error('GalleryImage->createResizedImage: Could not load or create image.', array('curImage' => $curImage, 'newImage' => $newImage));
			return null;
		}

		$res = imagecopyresampled($newImage, $curImage, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		if (!$res)
		{
			log_error('GalleryImage->createResizedImage: Error during resize step.', array('res' => $res));
			return null;
		}
		return $newImage;
	}

	/**
	 * Convenience function that delegates to BlockGallery::fitInBox.
	 * See that function for full details.
	 */
	function getSizeInBox($boxWidth, $boxHeight)
	{
		$w = $this->getWidth();
		$h = $this->getHeight();
		$ret = BlockGallery::fitInBox($boxWidth, $boxHeight, $w, $h);
		return $ret;
	}

	/**
	 * Load the image from the file.
	 * Note that while this uses the file extension to determine the file type,
	 *  no validation is done regarding the availability of the image handling functions.
	 * @returns a handle to the resource of the image, or null if it could not be loaded.
	 */
	function loadFromFile()
	{
		$resource = null;
		$mime = $this->getMIME();
		if ($mime == 'image/png')
		{
			$resource = imagecreatefrompng($this->path);
		}
		elseif ($mime == 'image/gif')
		{
			$resource = imagecreatefromgif($this->path);
		}
		elseif ($mime == 'image/jpeg')
		{
			$resource = imagecreatefromjpeg($this->path);
		}
		return $resource;
	}

	/**
	 * Get the cached name of the image, for a given width & height.
	 */
	function getCachedName($width, $height)
	{
		$md5 = $this->getMD5();
		// all cached images are jpg.. it's just simpler that way.
		$name = $md5.'-'.$width.'x'.$height.'.jpg';
		return $name;
	}

	/**
	 * Get the MIME type of the image.
	 */
	function getMIME()
	{
		return $this->info['mime'];
	}

	/**
	 * Get the height of the image, in pixels.
	 */
	function getHeight()
	{
		return $this->info[1];
	}

	/**
	 * Get the width of the image, in pixels.
	 */
	function getWidth()
	{
		return $this->info[0];
	}

	/**
	 * Get the md5 of the original image.
	 */
	function getMD5()
	{
		$md5 = md5_file($this->path);
		return $md5;
	}
}
