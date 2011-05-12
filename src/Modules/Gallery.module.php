<?php
#name = Gallery
#description = Image gallery based on the Files module
#package = Core - optional
#depends = Files
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
	}

	/**
	 * Callback to get the image for a FilesItem.
	 * This implementation creates a link to the ajax call,
	 * which we use to serve up a small version of the file in question.
	 */
	function getImageFor($item)
	{
		// we only support some image types..
		$name = $item->getName();
		$ext = Path::getExtension($name);
		$supportedTypes = self::getSupportedTypes();
		if (!in_array($ext, $supportedTypes))
		{
			return parent::getImageFor($item);
		}

		$queryParams = array('type' => 'block', 'module' => 'Gallery');
		$queryParams['width'] = $queryParams['height'] = $this->size;

		$url = 'ajax.php';
		$query = toQueryString($queryParams);
		return $url.'?'.$query;
	}

	function ajax()
	{
		// TODO: make this resize the image
		Header('Location: Modules/Files/Oxygen/128/application-pdf.png');
	}

	/**
	 * Static method that finds out what flavours of image we can support by querying the gd library.
	 */
	function getSupportedTypes()
	{
		$info = gd_info();
		$types = array();

		if ( empty($info['GD Version']) )
		{
			return $types;
		}

		if ( !empty($info['GIF Read Support']) && !empty($info['GIF Create Support']) )
		{
			$types[] = 'gif';
		}

		// Support PHP > 5.3 AND PHP < 5.3
		if ( !empty($info['JPEG Support']) || !empty($info['JPG Support']) )
		{
			$types[] = 'jpg';
			$types[] = 'jpeg';
		}

		if ( !empty($info['PNG Support']) )
		{
			$types[] = 'png';
		}

		return $types;
	}
}

/**
 * Generate a URL-encoded query string.
 * Values, but not keys, are encoded using urlencode.
 * Uses http_build_query if available, does not accept input anywhere near as complex.
 */
function toQueryString($query_data, $arg_separator = '&')
{
	if (function_exists('http_build_query'))
	{
		return http_build_query($query_data, '', $arg_separator);
	}

	if (empty($query_data))
	{
		return '';
	}

	foreach ($query_data as $key => $value)
	{
		$pairs[] = $key.'='.urlencode($value);
	}

	$query = implode($arg_separator, $pairs);
	return $query;
}
