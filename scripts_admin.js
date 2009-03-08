// gets called by some textboxes when a key is pressed
function TBpress(that)
{
	clearTimeout(that.countdown);
	that.countdown	= setTimeout("TBrefresh('"+that.id+"')", 12*1000);
}

// gets called after a set delay after a key press on certain text boxes
function TBrefresh(thing)
{
	that	= document.getElementById(thing);
	clearTimeout(that.countdown);
	//do the refresh stuff
}

// Add a row of articles
function add_article_row ()
{
	num_rows	= document.getElementById('num_rows').value;
	ai_sel		= new Array()
	curr_sel	= new Array()

	num_rows_1	= 1*num_rows + 1;
	num_rows_2	= 2*num_rows + 1;
	num_rows_3	= num_rows_2 + 1;

	replace_1	= "article_id_" + num_rows_2;
	replace_2	= "article_id_" + num_rows_3;

	old_html	= document.getElementById('art_sel_p').innerHTML;
	new_html	= old_html.replace(/article_id_1/g, replace_1);	//change the ids
	new_html	= new_html.replace(/article_id_2/g, replace_2);

	for(i=1; i <= 2*num_rows; i++) {
		art_id	= 'article_id_'+i;
		ai_sel[i]	= document.getElementById(art_id).selectedIndex;
	}

	document.getElementById('article_select_div').innerHTML	+= "<p>" + new_html + "</p>";
	document.getElementById('num_rows').value	= num_rows_1;

	for(i=1; i <= 2*num_rows; i++) {
		art_id	= 'article_id_'+i;
		document.getElementById(art_id).options[ai_sel[i]].selected	= true;
	}

	num_opts	= document.getElementById('article_id_1').length-1;
	curr_sel[1]	= document.getElementById('article_id_1').selectedIndex;
	curr_sel[2]	= document.getElementById('article_id_2').selectedIndex;

	for(i=num_rows_2, j=1; i <= 2*num_rows_1; i++, j++) {
		art_id	= 'article_id_'+i;
		cur_sel	= curr_sel[j];
		document.getElementById(art_id).options[cur_sel].selected	= false;
		document.getElementById(art_id).options[num_opts].selected	= true;
	}

	return;
}

// Clear the contents of an entire table row
function clearRow(thing)
{
	row	= thing.parentNode.parentNode;
	input_list	= row.getElementsByTagName('input');
	for(i=0; i<input_list.length; i++) {
		input_list[i].value	= '';
	}
	return;
}

// Tick (or untick) items as a group
function group_tick(thing)
{
	state	= thing.checked;
	tick_list	= get_Sub_Class_Elements('', thing.className);
	for(i=0; i<tick_list.length; i++) {
		tick_list[i].checked	= state;
	}
	return;
}

// get a page or article
function get(type, orig)
{
	that	= document.getElementById(type+"_select");
	type_l	= type.toLowerCase();
	req		= (type_l == "page" ? "page" : "art");
	SI		= that.selectedIndex;
	SI_len	= that.length;


	if(that.value == orig && !window.AJAX_enabled) {	//if its unchanged
		document.getElementById(type+'_form').reset();
		return;
	}
	if(that.value == 'new') {	//if its a new one
		document.getElementById(type_l+"_id").value	= 'new';
		document.getElementById(type_l+'_title').value	= '';
		document.getElementById(type+'_content').innerHTML	= '';
		return;
	}

	if(!window.AJAX_enabled) {
		window.location = "Admin.php?" + req + "_req=" + that.value + "#" + type;
	} else {
		ajax('GET', window.DATA_root+'/'+escape(that.value)+'.'+type_l, 0, function(ajax_obj) {
			document.getElementById(type+'_content').innerHTML	= ajax_obj.responseText;
			document.getElementById(type_l+'_title').value	= unescape(that.value).substr(1+that.value.indexOf('-')).replace(/\+/g, ' ');
			document.getElementById(type_l+'_id').value	= that.value;
		} );
	}
	return;
}

function switch_tabs(cur_div, mode)
{
	if(typeof cur_div == 'undefined') {
		if('' != location.hash)
			cur_div	= window.cur_div	= location.hash.substr(1);
		else
			cur_div	= window.cur_div	= divList[0].id;
	}

	if(typeof mode == 'undefined')
		mode	= 0;

	if(mode == 0) {	//if the first call
		for(i=0; i<divList.length; i++) {	//switch to the correct div
			if(document.getElementById(divList[i].id+'_h3'))
				document.getElementById(divList[i].id+'_h3').style.display	= 'none';
			if(divList[i].id != cur_div)
			{
				document.getElementById(divList[i].id).style.display		= 'none';
				document.getElementById(divList[i].id+'_link').className	= '';
			} else {
				document.getElementById(divList[i].id).style.display		= '';
				document.getElementById(divList[i].id+'_link').className	= 'got';
			}
		}

		if(cur_div.toLowerCase() != "webmaster" && window.AJAX_enabled && document.getElementById(cur_div+'_h3')) {	//if we need to ajax stuff
			ajax('GET', "ajax.php", "type=admin&class="+cur_div+'&page_req='+PAGE_req+'&art_req='+ART_req, function(ajax_obj) {
				document.getElementById(cur_div).innerHTML	= ajax_obj.responseText;
				switch_tabs(cur_div, 1);	//now go again, but skip the top stuff
			} );
			return;	//prevent the rest of the funciton running
		}
	}

	if(cur_div == "Profile")
		pass_change("hide");

	if(mode == 1) {
		window.hideList	= get_Sub_Class_Elements('admin_divs_holder', 'JS_hide');
		window.showList	= get_Sub_Class_Elements('admin_divs_holder', 'JS_show');
		window.moveList	= get_Sub_Class_Elements('admin_divs_holder', 'JS_move');
		for(i=0; i<moveList.length; i++)
			moveList[i].className	= moveList[i].className.replace('JS_move', 'JS');	//only allow it to be moved once
	}

	for(i=0; i<hideList.length; i++) {	//hide stuff
		document.getElementById(hideList[i].id).style.display		= 'none';
	}
	for(i=0; i<showList.length; i++) {	//show stuff
		document.getElementById(showList[i].id).style.display		= '';
	}

	document.title	= window.TITLE+' - '+cur_div;

	return;
}

function pass_change(type)
{
	if(!document.getElementById('pass_0'))
		return;
	PASS_AR	= [ "old_pass", "new_pass", "confirm_pass" ];
	if(type == "show")
	{
		boxes	= "";
		link	= "none";
	} else {
		boxes	= "none";
		link	= "";
	}
	for(i=1; i<4; i++)
	{
		document.getElementById(PASS_AR[i-1]).value	= '';
		document.getElementById('pass_'+i).style.display	= boxes;
	}
	document.getElementById('pass_0').style.display	= link;
	return;
}

function show_change(type)
{
	document.getElementById(type+'_change').style.display	= 'none';
	document.getElementById(type+'_sel_span').style.display	= '';
	return;
}

function change_pic(value)
{
	if(value != "none")
		img_path	= 'comm_' + value + '.jpg';
	else
		img_path	= 'Unknown.jpg';
	document.getElementById('pic_preview').src	= 'Thumbs/' + img_path;
	document.getElementById('pic_preview_link').href	= 'Site_Images/' + img_path;
	return;
}

/*	script inspired by "True Date Selector"
	 Created by: Lee Hinder, lee.hinder@ntlworld.com

	 Tested with Windows IE 6.0
	 Tested with Linux Opera 7.21, Mozilla 1.3, Konqueror 3.1.0

*/

function daysInFebruary (year){
	// February has 28 days unless the year is divisible by four, and if it is the turn of the century
	// then the century year must also be divisible by 400 when it has 29 days
	return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}

//function for returning how many days there are in a month including leap years
function DaysInMonth(WhichMonth, WhichYear)
{
	var DaysInMonth = 31;
	if(WhichMonth == "4" || WhichMonth == "6" || WhichMonth == "9" || WhichMonth == "11")
	DaysInMonth = 30;
	if(WhichMonth == "2")
	DaysInMonth = daysInFebruary( WhichYear );
	return DaysInMonth;
}

//function to change the available days in a months
function ChangeOptionDays(formObj, prefix)
{
	var DaysObject	= eval("formObj." + prefix + "day" );
	var MonthObject	= eval("formObj." + prefix + "month" );
	var YearObject	= eval("formObj." + prefix + "year" );

	if(typeof DaysObject.selectedIndex == 'number' && DaysObject.options)
	{ // The DOM2 standard way
		window.LOG	+= "\nThe DOM2 standard way";
		var DaySelIdx	= DaysObject.selectedIndex;
		var Month	= parseInt(MonthObject.options[MonthObject.selectedIndex].value);
		var Year	= parseInt(YearObject.options[YearObject.selectedIndex].value);
	}
	else if(typeof DaysObject.selectedIndex == 'number' && DaysObject[DaysObject.selectedIndex])
	{ // The legacy ETBS way
		window.LOG	+= "\nThe legacy ETBS way";
		var DaySelIdx	= DaysObject.selectedIndex;
		var Month	= parseInt(MonthObject[MonthObject.selectedIndex].value);
		var Year	= parseInt(YearObject[YearObject.selectedIndex].value);
	}
	else if(typeof DaysObject.value == 'number')
	{ // Opera 6 stores the selectedIndex in property 'value'.
		window.LOG	+= "\nThe Opera 6 way";
		var DaySelIdx	= parseInt(DaysObject.value);
		var Month	= parseInt(MonthObject.options[MonthObject.value].value);
		var Year	= parseInt(YearObject.options[YearObject.value].value);
	}

	window.LOG	+= "\nDay="+(DaySelIdx+1)+" Month="+Month+" Year="+Year;

	var DaysForThisSelection	= DaysInMonth(Month, Year);
	var CurrentDaysInSelection	= DaysObject.length;
	if(CurrentDaysInSelection > DaysForThisSelection)
	{
		for(i=0; i<(CurrentDaysInSelection-DaysForThisSelection); i++) {
			DaysObject.options[DaysObject.options.length - 1] = null
		}
	}
	if(DaysForThisSelection > CurrentDaysInSelection)
	{
		for(i=0; i<DaysForThisSelection; i++) {
			DaysObject.options[i] = new Option(eval(i + 1));
		}
	}
	if(DaysObject.selectedIndex < 0)	DaysObject.selectedIndex = 0;
	if(DaySelIdx >= DaysForThisSelection)
		DaysObject.selectedIndex = DaysForThisSelection-1;
	else
		DaysObject.selectedIndex = DaySelIdx;
}

function Validate_On_Admin_Submit(FORM)
{
	form_id	= FORM.id.replace('_form', '');
	window.LOG	+= "Admin Validation: form_id = "+form_id;

	/* run through the sections with custom validation */
	if(form_id == "Profile") {
		if(/(^$)|(^\s+$)/.test(FORM.new_name.value)) {	// null strings and spaces only strings not allowed
			setfocus(FORM.new_name);
			alert("Please enter your name.");
			return false;
		}
		if(!(/(^$)|(^\s+$)/.test(FORM.old_pass.value))) {	// if they entered the old password then they're trying to change it
			if(FORM.new_pass.value != FORM.confirm_pass.value) {	// the new passwords must match
				FORM.confirm_pass.value = FORM.new_pass.value = '';
				setfocus(FORM.new_pass);
				alert("Your new passwords do not match.");
				return false;
			}
			if(/(^$)|(^\s+$)/.test(FORM.new_pass.value)) {	// null strings and spaces only strings not allowed
				setfocus(FORM.new_pass);
				alert("Your new password cannot be null, or just spaces.");
				return false;
			}
			if(/(^$)|(^\s+$)/.test(FORM.confirm_pass.value)) {	// null strings and spaces only strings not allowed
				setfocus(FORM.confirm_pass);
				alert("Your new password cannot be null, or just spaces.");
				return false;
			}
		}
	} else if(form_id == "Article") {
		if(/(^$)|(^\s+$)/.test(FORM.article_title.value)) {	// null strings and spaces only strings not allowed
			setfocus(FORM.article_title);
			alert("Please enter an article title.");
			return false;
		}
	} else if(form_id == "Manage") {
		if(!confirm('Are you sure you want to delete the files? (this action cannot be undone)'))
			return false;
	} else if(form_id == "Webmaster") {
		if(!confirm('Are you sure you want to reset the passwords?'))
			return false;
	} else if(form_id == "Page") {
		if(/(^$)|(^\s+$)/.test(FORM.page_title.value)) {	// null strings and spaces only strings not allowed
			setfocus(FORM.page_title);
			alert("Please enter a page title.");
			return false;
		}
		if(document.getElementById('WYSIWYG_Page') != null) {
			not_none	= 0;
			num_rows	= document.getElementById('num_rows').value;
			for(i=1; i <= 2*num_rows; i++) {
				d_down	= "article_id_" + i;
				SI	= document.getElementById(d_down).selectedIndex;
				len	= document.getElementById(d_down).length - 1;
				if(SI != len)
					not_none	+= 1;
			}
			if(not_none == 0) {
				alert("Please select some articles for the page.");
				return false;
			}
		}
	} else if(form_id == "Event") {
		if(/(^$)|(^\s+$)/.test(FORM.event_title.value)) {	// null strings and spaces only strings not allowed
			setfocus(FORM.event_title);
			alert("Please enter an event title" );
			return false;
		}
	}

	/* test for and check the content box */
	content_id	= form_id + '_content';

	if(document.getElementById(content_id) != null) {
		if(typeof tinyMCE != 'undefined')
			cont3nt	= tinyMCE.getContent(content_id);	//use the tinyMCE editor to get the content
		else
			cont3nt	= document.getElementById(content_id).value;	//grab it manually
		if(/(^$)|(^\s+$)/.test(cont3nt)) {	// null strings and spaces only strings not allowed
			alert("Please enter some content.");
			return false;
		}
		window.LOG	+= "\ncont3nt = " + cont3nt;
	}

	FORM.submit.disabled	= "disabled";	//make sure they don't hit save more than once

	return true;
}