// gets called by some textboxes when a key is pressed
function TBpress(that) {
	clearTimeout(that.countdown);
	that.countdown	= setTimeout("TBrefresh('"+that.id+"')", 12*1000);
}

// gets called after a set delay after a key press on certain text boxes
function TBrefresh(thing) {
	var that	= $(thing);
	clearTimeout(that.countdown);
	//do the refresh stuff
}

// Add a row of articles
function add_article_row() {
	var num_rows	= $('num_rows').value;
	var ai_sel		= new Array()
	var curr_sel	= new Array()

	var num_rows_1	= 1*num_rows + 1;
	var num_rows_2	= 2*num_rows + 1;
	var num_rows_3	= num_rows_2 + 1;

	var replace_1	= "article_id_" + num_rows_2;
	var replace_2	= "article_id_" + num_rows_3;

	var old_html	= $('art_sel_p').innerHTML;
	var new_html	= old_html.replace(/article_id_1/g, replace_1);	//change the ids
	new_html	= new_html.replace(/article_id_2/g, replace_2);

	for(var i=1; i <= 2*num_rows; i++) {
		var art_id	= 'article_id_'+i;
		ai_sel[i]	= $(art_id).selectedIndex;
	}

	$('article_select_div').innerHTML	+= "<p>" + new_html + "</p>";
	$('num_rows').value	= num_rows_1;

	for(i=1; i <= 2*num_rows; i++) {
		art_id	= 'article_id_'+i;
		$(art_id).options[ai_sel[i]].selected	= true;
	}

	var num_opts	= $('article_id_1').length-1;
	curr_sel[1]	= $('article_id_1').selectedIndex;
	curr_sel[2]	= $('article_id_2').selectedIndex;

	for(var j=1, i=num_rows_2; i <= 2*num_rows_1; i++, j++) {
		var art_id	= 'article_id_'+i;
		$(art_id).options[curr_sel[j]].selected	= false;
		$(art_id).options[num_opts].selected	= true;
	}

	return;
}

// Clear the contents of an entire table row
function clearRow(thing) {
	var row = thing.parentNode.parentNode;
	var input_list	= row.getElementsByTagName('input');
	var select_list	= row.getElementsByTagName('select');
	for(var i=0; i<input_list.length; i++) {
		input_list[i].value	= '';
	}
	for(var i=0; i<select_list.length; i++) {
		select_list[i].value	= '';
	}
	return;
}

// Tick (or untick) items as a group
function group_tick(thing) {
	var state	= thing.checked;
	var tick_list	= get_Sub_Class_Elements('', thing.className);
	for(var i=0; i<tick_list.length; i++) {
		tick_list[i].checked	= state;
	}
	return;
}

// get a page or article
function get(type, orig) {
	var that	= $(type+"_select");
	var type_l	= type.toLowerCase();
	var file_type	= (type_l == "content" ? "chunk" : type_l);
	var req		= (type_l == "page" ? "p" : "a");
	var SI		= that.selectedIndex;
	var SI_len	= that.length;


	if(that.value == orig && !window.AJAX_enabled) {	//if its unchanged
		$(type+'_form').reset();
		return;
	}
	if(that.value == 'new') {	//if its a new one
		$(file_type+"_id").value	= 'new';
		$(file_type+'_title').value	= '';
		$('Admin'+type+'_content').value	= '';
//		$('Admin'+type+'_content').innerHTML	= '';
		return;
	}

	if(!window.AJAX_enabled) {
		window.location = "Admin.php?" + req + "=" + that.value + "#" + type;
	} else {
		ajax('GET', window.DATA_root+'/'+escape(that.value)+'.'+file_type, 0, function(ajax_obj) {
	//		$('Admin'+type+'_content').innerHTML	= ajax_obj.responseText;
			$('Admin'+type+'_content').value	= ajax_obj.responseText;
			$(file_type+'_title').value	= unescape(that.value).substr(1+that.value.indexOf('-')).replace(/\+/g, ' ');
			$(file_type+'_id').value	= that.value;
		} );
	}
	return;
}

function switch_tabs(cur_div, mode) {
	if(typeof cur_div == 'undefined') {
		if('' != location.hash)
			cur_div	= window.cur_div	= location.hash.substr(1);
		else
			cur_div	= window.cur_div	= divList[0].id;
	}
	var cur_id = 'Admin'+cur_div;

	if(typeof mode == 'undefined')
		mode	= 0;

	if(mode == 0) {	//if the first call
		for(var i=0; i<divList.length; i++) {	//switch to the correct div
			if($(divList[i].id+'_h3'))
				$(divList[i].id+'_h3').style.display	= 'none';
			if(divList[i].id != cur_id)
			{
				divList[i].style.display		= 'none';
				$(divList[i].id+'_link').className	= '';
			} else {
				divList[i].style.display		= '';
				$(divList[i].id+'_link').className	= 'got';
			}
		}

		if(window.AJAX_enabled && $(cur_id+'_h3')) {	//if we need to and can do ajax stuff
			ajax('GET', "ajax.php", "type=admin&module="+cur_div+'&p='+PAGE, function(ajax_obj) {
				$(cur_id).innerHTML	= ajax_obj.responseText;
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
		for(var i=0; i<moveList.length; i++)
			moveList[i].className	= moveList[i].className.replace('JS_move', 'JS');	//only allow it to be moved once
	}

	for(var i=0; i<hideList.length; i++) {	//hide stuff
		hideList[i].style.display		= 'none';
	}
	for(i=0; i<showList.length; i++) {	//show stuff
		showList[i].style.display		= '';
	}

	document.title	= window.TITLE+' - '+cur_div;

	return;
}

function pass_change(type) {
	if(!$('pass_0'))
		return;
	var PASS_AR	= [ "old_pass", "new_pass", "confirm_pass" ];
	if(type == "show") {
		var boxes	= "";
		var link	= "none";
	} else {
		var boxes	= "none";
		var link	= "";
	}
	for(var i=1; i<4; i++) {
		$(PASS_AR[i-1]).value	= '';
		$('pass_'+i).style.display	= boxes;
	}
	$('pass_0').style.display	= link;
	return;
}

function show_change(type) {
	$(type+'_change').style.display	= 'none';
	$(type+'_sel_span').style.display	= '';
	return;
}

function change_pic(value) {
	if(value != "none")
		var path = value + '.jpg';
	else
		var path = 'Unknown.jpg';

	$('pic_preview').src	= SITE_root+'/Users/Thumbs/'+path;
	$('pic_preview_link').href	= SITE_root+'/Users/'+path;
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
function DaysInMonth(WhichMonth, WhichYear) {
	var DaysInMonth = 31;
	if(WhichMonth == "4" || WhichMonth == "6" || WhichMonth == "9" || WhichMonth == "11")
	DaysInMonth = 30;
	if(WhichMonth == "2")
	DaysInMonth = daysInFebruary( WhichYear );
	return DaysInMonth;
}

//function to change the available days in a months
function ChangeOptionDays(formObj, prefix) {
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
		for(var i=0; i<(CurrentDaysInSelection-DaysForThisSelection); i++) {
			DaysObject.options[DaysObject.options.length - 1] = null
		}
	}
	if(DaysForThisSelection > CurrentDaysInSelection)
	{
		for(var i=0; i<DaysForThisSelection; i++) {
			DaysObject.options[i] = new Option(eval(i + 1));
		}
	}
	if(DaysObject.selectedIndex < 0)	DaysObject.selectedIndex = 0;
	if(DaySelIdx >= DaysForThisSelection)
		DaysObject.selectedIndex = DaysForThisSelection-1;
	else
		DaysObject.selectedIndex = DaySelIdx;
}

function Validate_On_Admin_Submit(FORM) {
	var form_id	= FORM.id.replace('_form', '');
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
		if($('WYSIWYG_Page') != null) {
			var not_none	= 0;
			var num_rows	= $('num_rows').value;
			for(var i=1; i <= 2*num_rows; i++) {
				var d_down	= "article_id_" + i;
				var SI	= $(d_down).selectedIndex;
				var len	= $(d_down).length - 1;
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

	if($(content_id) != null) {
		if(typeof tinyMCE != 'undefined')
			cont3nt	= tinyMCE.getContent(content_id);	//use the tinyMCE editor to get the content
		else
			cont3nt	= $(content_id).value;	//grab it manually
		if(/(^$)|(^\s+$)/.test(cont3nt)) {	// null strings and spaces only strings not allowed
			alert("Please enter some content.");
			return false;
		}
		window.LOG	+= "\ncont3nt = " + cont3nt;
	}

	FORM.submit.disabled	= "disabled";	//make sure they don't hit save more than once

	return true;
}
