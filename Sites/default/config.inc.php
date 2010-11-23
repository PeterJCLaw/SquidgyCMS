<?php
/*
	The stuff related to the FSPHP Gallery is
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
	/* Variables that shouldn't be touched */

	$version	= "1.2-pre"; // please don't change this

	/* Variables that you may need to modify */

	$abuseReports	= FALSE;				// if you want to be emailed if someone tries to abuse the script
	$email			= "email@example.com";	// your email address
	$ImagePath		= "Photos";		// the releative path to the image directory. Must not start with .. or / -- use symlinks to get around this if a problem
	$NewsPath		= "Newsletters";	// the releative path to the news directory. Must not start with .. or / -- use symlinks to get around this if a problem
	$itemsInRow		= 4;			// how many items in each row
	$numberOfRows	= 6;			// the number of rows per page -- does not work yet
	$cacheDir		= "cache";		// the directory in which to store the cache -- must be writable by the web server (e.g. chmod 777)
	$maxWidth		= "4000";		// The maximum width to allow images to be re-sized to (to prevent DOS)
	$maxHeight		= "4000";		// The maximum height to allow images to be re-sized to

	/* tell the fsphp gallery about some files to ignore, all are csv arrays, DirALLlist only */
	$ignore_files	= array("index", "test", "resize", "Old");	//file or folder names, case sensitive, no extentions
	$ignore_parts	= array(".inc", "_dev", "_old", "synctoy", "Site_", "thumbs", "debug", "handler");	//parts of names of files or folders, case insensitive
	$ignore_exts	= array("db", "css", "js", "lnk", "htaccess");	//names of file extentions to ignore, case insensitive
	$secure_folders	= array();	//files or folders that you need to be $logged_in to see, case sensitive

	/* Site information - please do not remove these, but you may change them*/
	$website_name_long	= "Be Squidgy default website of the Squidgy Content Management System";
	$website_name_short	= "Be Squidgy";
	$cookie_name		= $website_name_short."_admin";
	$webmaster_email	= "email@example.com";	//the webmaster's email address
	$tag_line			= $website_name_long;
	$who_copyright		= $website_name_long;
	$mail_webmsater_on_event	= FALSE;	//whether to email the webmaster if and event is booked
	$Clean_URLs	= TRUE;	//whether or not to use clean URLs ie example.com/cheese instead of example.com/?page_req=4-cheese

	/* Contact Forms - integration to a majordomo system via email */
	$website_form_email	= "email@example.com";	//the email that webforms claim to send from
	$MailingList['enable']	= TRUE;	//allow them to ask to be added to the mailing list (adds a tickbox)
	$MailingList['subscribe']	= FALSE;	//automatically subscribe them to the mailing list if they ask (sends an email to majordomo)
	$MailingList['list-name']	= 'list-name';	//the name of the list, only needed if subscribe is TRUE
	$MailingList['password']	= 'password';	//the mailing list password (set to boolean FALSE if not required)

	/* for the copyright warning above the images etc in the Galleries among other places */
	$copy_email_text	= "email";	 //the bit the person sees on the page - the link text
	$copy_recipient		= "webmaster";	//who gets the email
	$copy_recip_gender	= "them";		//their gender (him, her, them, etc.) for the title of the link
	$copy_follow_text	= ' the <a href="Committee#Webmaster" title="Who the heck is that?">Webmaster</a>.<br />';		//the text following the mailto link.

	/* for the link to the old style photo gallery */
	$nostalgic_images_footer	= FALSE;
	$n_i_f_link			= "Photos";
	$n_i_f_date_added	= "2007";

	/* Committee Emails: either complete the bottom or the top section */
	//if you have one email that all committe members' email should be sent to then this is that address
	$committee_email	= "email@example.com";
	//if you have an aliasing system using one address per person, with a variable part then this specifies the pre- and post- fixes to that variable part
	$comm_email_prefix	= "prefix";
	$comm_email_postfix	= "@example.com";

