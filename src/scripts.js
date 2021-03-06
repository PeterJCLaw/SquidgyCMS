// Strip leading and trailing white-space
String.prototype.trim = function() {
	return this.replace(/^\s*|\s*$/g, "");
}

// Replace repeated spaces, newlines and tabs with a single space
String.prototype.normalize_space = function() {
	return this.replace(/^\s*|\s(?=\s)|\s*$/g, "");
}

// $ id function
function $(id)
{
	if(typeof id == 'string')
		return document.getElementById(id);
	else
		return id;
}

// validate the tickboxes
function add_loader(obj)
{
	if(typeof obj != 'function')
		return 'object not a function';

	if(typeof window.onload != 'function') {
		window.onload	= obj;
		return 'added first thing to window.onload';
	}

	var old_load	= window.onload;
	window.onload	= function() {
		old_load();
		obj();
		return 'added another thing to window.onload';
	}
}

// validate the tickboxes
function validate_boxes(FORM)
{
	target_list	= get_Sub_Class_Elements(FORM.id, 'tick_1');
	out = '';
	for(var i=0; i<target_list.length; i++)
	{
		val	= target_list[i].checked;
		out	+= ", i = " + i + ", val = " + val;
		if(val)
			return true;
	}
	window.LOG	+= '\nBox validation: '+out;
	return false;
}

/* toggle the open or closed  status of the list heading including changing the icon */
function toggle_FE(child_ul, parent_li)
{
	var LI_Obj	= $(parent_li);
	var UL_Obj	= $(child_ul);
	var UL_stripped	= UL_Obj.innerHTML.toLowerCase().replace(/^\s*|\s*$/g,'');
	var UL_Obj_s	= UL_Obj.style;

	if(UL_stripped == "<li>loading file tree...</li>" || UL_stripped == "<li>loading file tree... </li>")	//call the ajax function to load it
		ajax('get', 'ajax.php', 'type=block&class=File&folder='+child_ul, function(ajaxobj) { $(child_ul).innerHTML = ajaxobj.responseText; });

	if(LI_Obj.className	== 'collapsed')	//so that we toggle properly
		LI_Obj.className	= 'expanded';	//change the icon and display status by changing the class
	else
		LI_Obj.className	= 'collapsed';
}

// Tick (or untick) items as a group
function group_tick_2(thing, all_id)
{
	var state	= thing.checked;
	var tick_list	= get_Sub_Class_Elements(this.form, thing.className);
	if(thing.id == all_id) {
		for(var i=0; i<tick_list.length; i++) {
			tick_list[i].checked	= state;
		}
		window.LOG	+= "\nWhole group being ticked";
	} else {
		for(var count = 0, i = 0; i<tick_list.length; i++) {
			if(tick_list[i].id != all_id && tick_list[i].checked)
				count++;
		}
		if(count == tick_list.length-1)
			$(all_id).checked	= true;
		else
			$(all_id).checked	= false;
		window.LOG	+= "\ncount = "+count+", length = "+tick_list.length;
	}
	return;
}

function get_Sub_Class_Elements(container, search_class)
{
	if(container != '' && $(container) != null)
		var BASE	= $(container);
	else
		var BASE	= document;

	if(!bad_browser && typeof BASE.getElementsByClassName(search_class) == 'function') {
		return BASE.getElementsByClassName(search_class);
	}

	var elem_list	= BASE.getElementsByTagName('*');
	var out_list = new Array();

	for(var j=0, i=0; i<elem_list.length; i++) {
		if(elem_list[i].className == search_class || elem_list[i].className.indexOf(search_class) != -1) {
			out_list[j]	= elem_list[i];
			j++;
		}
	}
	return out_list;
}

/* find out what browser they are running, if its too crap then don't do some parts of the js */
function bad_browser()
{
	if (navigator.appName == "Microsoft Internet Explorer")
		return true;
}

/* set the focus to a specific input */
function setFocusDelayed(global_valfield)
{
	global_valfield.focus();
}

function setfocus(valfield)
{
	// save valfield in global variable so value retained when routine exits
	global_valfield = valfield;
	setTimeout('setFocusDelayed(global_valfield)', 100);
}

/* validation script from the MRBS system with some major tweaks by me */

function Validate_On_Contact_Submit(FORM)
{
	var out	= "form_name = "+FORM.name;

	//	make sure they clicked someone to send it too.
	if(!validate_boxes(FORM))
	{
		setfocus(FORM.from_name);
		alert("Please enter select a recipient for your message");
		return false;
	}
	//	null strings and spaces only strings not allowed
	if(/(^$)|(^\s+$)/.test(FORM.from_name.value))
	{
		setfocus(FORM.from_name);
		alert("Please enter your name");
		return false;
	}
	//	null strings and spaces only strings not allowed
	if(/(^$)|(^\s+$)/.test(FORM.from_email.value))
	{
		setfocus(FORM.from_email);
		alert("Please enter your email address");
		return false;
	}
	var email = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;	// also the email must be of the form blah@example.com etc
	if(!email.test(FORM.from_email.value))
	{
		setfocus(FORM.from_email);
		alert("Please ensure that your email address is valid");
		return false;
	}
	//	null strings and spaces only strings not allowed
	if(/(^$)|(^\s+$)/.test(FORM.subject.value))
	{
		setfocus(FORM.subject);
		alert("Please enter a subject for your message");
		return false;
	}
	//	null strings and spaces only strings not allowed
	if(/(^$)|(^\s+$)/.test(FORM.message.value))
	{
		setfocus(FORM.message);
		alert("Please enter a message");
		return false;
	}
	FORM.c_submit.disabled	= "disabled";	//make sure they don't hit send twice

	return true;
}
