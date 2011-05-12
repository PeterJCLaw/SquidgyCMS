<meta http-equiv="refresh" content="1" />
<style type="text/css">
table {
	margin: 1em;
}
table, td, th {
	border-collapse: collapse;
	border: 1px solid grey;
	padding: 7px;
}
.odd {
	background-color: lightGrey;
}
</style>
<?php

class Admin {}
class Block {}

function getOddEven()
{
	static $i = 0;
	$i++;
	$oe = $i % 2 == 0 ? 'odd' : 'even';
	return $oe;
}

require_once('Files.module.php');
require_once('Gallery.module.php');

$paths = array(
	'foo' => 'foo',
	'foo/bar' => 'foo/bar',
	'foo/..' => '',
	'foo/..' => '',
	'foo/../' => '',
	'foo/../bar' => 'bar',
	'foo/bar/..' => 'foo',
	'foo/../bar/..' => '',
	'../bar' => '../bar',
	'.' => '',
	'' => '',
	'././././' => '',
	'./' => '',
	'./' => '',
	'../f///b/../c/d/..///../a/b/c/d/../' => '../f/a/b/c/'
);

echo '<table><tr>';

echo '<th>'.implode('</th><th>', array('start', 'end', 'tidy', 'tidy matches end', 'areSame')).'</th>';

foreach ($paths as $start => $end)
{
	$class = ' class="'.getOddEven().'"';
	echo "</tr><tr$class>";

	$tidy = Path::tidy($start);
	$areSame = Path::areSame($start, $end);

	echo "<td>$start</td><td>$end</td><td>$tidy</td><td>";
	var_dump($tidy === $end);
	echo '</td><td>';
	var_dump($areSame);
	echo '</td>';
}

echo '</tr></table>';

$sizes = array(
	array(150, 150, 150, 150, 150, 150),
	array(150, 100, 150, 150, 100, 100),
	array(150, 100, 150, 120, 125, 100),
	array(90, 150, 120, 150, 90, 112.5),
	array(150, 90, 120, 150, 72, 90),
	array(100, 100, 150, 150, 100, 100),
	array(150, 150, 100, 150, 100, 150),
);

echo '<table><tr>';

echo '<th>'.implode('</th><th>', array('boxWidth', 'boxHeight', 'width', 'height', 'expWidth', 'expHeight', 'retWidth', 'retHeight', 'state')).'</th>';

foreach ($sizes as $size)
{
	list($boxWidth, $boxHeight, $width, $height, $expWidth, $expHeight) = $size;

	$_sizes = array($size);

	if ( $boxWidth !== $boxHeight || $width !== $height )
	{
		$_sizes[] = array($boxHeight, $boxWidth, $height, $width, $expHeight, $expWidth);
	}

	$class = ' class="'.getOddEven().'"';

	foreach ($_sizes as $size)
	{
		echo "</tr><tr$class>";
		list($boxWidth, $boxHeight, $width, $height, $expWidth, $expHeight) = $size;

		$ret = BlockGallery::fitInBox($boxWidth, $boxHeight, $width, $height);
		$retWidth = $ret['width'];
		$retHeight = $ret['height'];

		echo '<td>'.implode('</td><td>', array($boxWidth, $boxHeight, $width, $height, $expWidth, $expHeight, $retWidth, $retHeight)).'</td>';

		echo '<td>';
		$fails = array();
		if ($retWidth != $expWidth)
		{
			$fails[] = 'width';
		}
		if ($retHeight != $expHeight)
		{
			$fails[] = 'height';
		}

		if (empty($fails))
		{
			echo 'match';
		}
		else
		{
			echo '<strong>';

			if (count($fails) == 2)
			{
				echo 'both';
			} else {
				echo $fails[0];
			}

			echo '</strong>';
		}

		echo '</td>';
	}
}

echo '</tr></table>';
