<?php
/* This function converts a job title into an email add-in */
function email($job)
{
	if(stristr($job, "web"))	//special case
		return "Webmaster";

	return str_replace(" ", ".", $job);
}
?>