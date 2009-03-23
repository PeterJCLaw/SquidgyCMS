<?php
class BlockUsers extends Block {
	function BlockUsers() {
		parent::__construct();
	}

	function ListDiv($args) {
		global $job_list;
		
		if(empty($job_list))
			return '<span id="users_tbl">No Users Found!</span>';
		
		$ret	= '
<table id="users_tbl">';

		foreach($job_list as $job) {
			if(in_array($job, array('Committee', 'Chaplain')))
				continue;
			$info_name = info_name($job);
			include "$this->site_root/Users/$info_name.user.php";
			$firstname = first_name($name);
			$ret	.= '<tr>
		<td rowspan="2" class="user_pic">
			<a href="Site_Images/'.user_pic($image_path).'" title="Click image to view larger"><img src="Thumbs/'.user_pic($image_path).'" alt="'."$firstname, $job".'" title="Click image to view larger" /></a>				</td>
		<td>
			<h4 id="'.str_replace(".", "_", $info_name).'" class="user_name">'."$job - ".stripslashes($name).'</h4>
			<span class="user_email">
				'.email_link("email $gender", $gender, $info_name, 0,0,0,0).' or use the <a href="Contact_Us.php?target='.$job.'" title="Use the online contact form">contact form</a>.
			</span>
		</td>
	</tr><tr>
		<td class="user_txt">
			'.stripslashes($spiel) //gets rid of any slashes invoked by (')s
			.'<br /><br />
		</td>
	</tr>';
			}
		return $ret.'
</table>';
	}
}

/* This function determines if the committee picture passed is valid, if it is it returns it, else returns a standin image */
function user_pic($pic)
{
	if($pic == "" || !is_readable("Site_Images/$pic"))
		$pic	= "Unknown.jpg";

	return $pic;
}

?>