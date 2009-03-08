<?php
/* This function converts a job title into an email add-in */
function email($job)
{
	if(stristr($job, "web"))	//special case
		return "Webmaster";

	$job	= str_replace(" Secretary", "", str_replace(" Officer", "", str_replace(" Liason", "", $job)));

	if(stristr($job, "faith"))	//special case
		$job	= str_replace("faith", "Faith", $job);

	return str_replace(" ", ".", $job);
}
?>