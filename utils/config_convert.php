<pre>
<?php

include('../src/Sites/default/config.inc.php');

$configItems = array(
	'General' => array(
		'website_name_long'		=> $website_name_long,
		'website_name_short'	=> $website_name_short,
		'webmaster_email'		=> $webmaster_email,
		'tag_line'				=> $tag_line,
		'who_copyright'			=> $who_copyright,
		'Clean_URLs'			=> $Clean_URLs
	),

	'Contact' => array(
		'website_form_email'	=> $website_form_email,
		'MailingList.enable'	=> $MailingList['enable'],
		'MailingList.subscribe'	=> $MailingList['subscribe'],
		'MailingList.list-name'	=> $MailingList['list-name'],
		'MailingList.password'	=> $MailingList['password']
	),

	'Docs' => array(
		'ImagePath'		=> $ImagePath,
		'NewsPath'		=> $NewsPath,
		'itemsInRow'	=> $itemsInRow,
		'numberOfRows'	=> $numberOfRows,
		'cacheDir'		=> $cacheDir,
		'maxWidth'		=> $maxWidth,
		'maxHeight'		=> $maxHeight,

		'ignore_files'		=> $ignore_files,
		'ignore_parts'		=> $ignore_parts,
		'ignore_exts'		=> $ignore_exts,
		'secure_folders'	=> $secure_folders,

		'copy_email_text'	=> $copy_email_text,
		'copy_recipient'	=> $copy_recipient,
		'copy_recip_gender'	=> $copy_recip_gender,
		'copy_follow_text'	=> $copy_follow_text
	),

	'Email' => array(
		'committee_email'		=> $committee_email,
		'comm_email_prefix'		=> $comm_email_prefix,
		'comm_email_postfix'	=> $comm_email_postfix
	)
);

function print_ini($key, $value)
{
	if (!is_array($value))
	{
		echo $key,'',print_ini_value($value);
	}
	else
	{
		foreach ($value as $val)
		{
			echo $key,'[]',print_ini_value($val);
		}
	}
}

function print_ini_value($value)
{
	if (is_bool($value))
	{
		$value = $value ? 'True' : 'False';
	}
	else
	{
		$value = "\"$value\"";
	}
	echo '	= ', htmlspecialchars($value), "\n";
}

foreach ($configItems as $section => $entries)
{
	echo "\n[$section]\n";
	foreach ($entries as $key => $value)
	{
		print_ini($key, $value);
	}
}
