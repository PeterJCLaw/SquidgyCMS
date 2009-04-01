<?php
/*
    Copyright (C) 2004 Chris Howells <howells@kde.org>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/
?>
<?php
include_once("Global.inc.php");	//tweaked by Peter Law due to move to a single global include

/* This function appends a string passed to the filename given */
function best_thumb($cache, $thumbX, $thumbY)
{
	global $debug_info;
	if(!file_exists($cache))
		return FALSE;
	list($cacheX, $cacheY, $type, $attr) = getimagesize($cache);
	$debug_info	.= "\$cacheX=$cacheX\n<br />\$cacheY=$cacheY\n<br />\$thumbX=$thumbX\n<br />\$thumbY=$thumbY\n<br />\n";
	return (($cacheX == $thumbX && $cacheY == $thumbY) || ($cacheX == $thumbX && $cacheY < $thumbY) || ($cacheY == $thumbY && $cacheX < $thumbX)) ? TRUE : FALSE;
}

if (array_key_exists('file', $_REQUEST) && array_key_exists('width', $_REQUEST) && array_key_exists('height', $_REQUEST))
{
	global $maxWidth, $maxHeight;

	$request	= $site_root.$_REQUEST['file']; $debug	= 0;	//Peter Law added the site_root variable
	$debug	= $_REQUEST['debug'];

	authoriseRequest($request);

	$thumbX	= $_REQUEST['width'];
	$thumbY	= $_REQUEST['height'];

	list($imageX, $imageY, $type, $attr) = getimagesize($request);

	$image_ratio	= $imageX / $imageY;
	$thumb_ratio	= $thumbX / $thumbY;

	if ($thumbX > $maxWidth)	//shrink both requested width & height if they're too big, maintian requested ratio.
	{
		$thumbX	= $maxWidth;
		$thumbY	= $thumbX / $thumb_ratio;
	}

	if ($thumbY > $maxHeight)
	{
		$thumbY	= $maxHeight;
		$thumbX	= $thumbY * $thumb_ratio;
	}

	if($thumb_ratio != $image_ratio)	//if the ratio's don't match find the weights (areas) of the respective possoble thumbs, then make the smaller one
	{
		$weightX	= $thumbX * $thumbX / $image_ratio;
		$weightY	= $thumbY * $thumbY * $image_ratio;

		if($weightY < $weightX)
			$thumbX	= $thumbY * $image_ratio;
		else
			$thumbY	= $thumbX / $image_ratio;
	}

	$returnPath	= returnPath($request);
	$cache	= ($returnPath == "" ? "" :"$returnPath/")."Thumbs/".returnName($request);

	if (best_thumb($cache, (int) $thumbX, (int) $thumbY) && !$debug)	//if its a good thumbnail, else make one
		header("Location: $cache");
	else
	{
		$extension	= returnExtension(returnName($request));

		if ($extension == "jpg")
			$source	= imagecreatefromjpeg($request);	//returns a resource handle
		else
			if ($extension == "png")
				$source	= imagecreatefrompng($request);

		$dest	= imagecreatetruecolor($thumbX, $thumbY);
		imagecopyresampled($dest, $source, 0, 0, 0, 0, $thumbX, $thumbY, $imageX, $imageY);
		recursiveMkdir(returnPath($cache));

		if ($extension == "jpg")
			imagejpeg($dest, $cache);	//outputs the image to the cache
		else
			if ($extension == "png")
				imagepng($dest, $cache);

		imagedestroy($dest);
		imagedestroy($source);
		if(!$debug)
			header("Location: $cache");

		$debug_info	.= "\$request=$request\n<br />\$thumbX=$thumbX\n<br />\$thumbY=$thumbY\n<br />\$imageX=$imageX\n<br />\$imageY=$imageY\n<br />\$image_ratio=$image_ratio\n<br />"
		."\$thumb_ratio=$thumb_ratio\n<br />\$cache=<a href=\"$cache\">$cache</a>\n<br />\n";

		echo $debug_info;
	 }
}
?>