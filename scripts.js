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
	if(typeof id == string)
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
	for(i=0; i<target_list.length; i++)
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
	LI_Obj	= document.getElementById(parent_li);
	UL_Obj	= document.getElementById(child_ul);
	UL_stripped	= UL_Obj.innerHTML.toLowerCase().replace(/^\s*|\s*$/g,'');
	UL_Obj_s	= UL_Obj.style;

	if(UL_stripped == "<li>loading file tree...</li>" || UL_stripped == "<li>loading file tree... </li>")	//call the ajax function to load it
		ajax('get', 'ajax.php', 'type=block&class=File&folder='+child_ul, function(ajaxobj) { document.getElementById(child_ul).innerHTML = ajaxobj.responseText; });

	if(LI_Obj.className	== 'collapsed')	//so that we toggle properly
		LI_Obj.className	= 'expanded';	//change the icon and display status by changing the class
	else
		LI_Obj.className	= 'collapsed';
}

// Tick (or untick) items as a group
function group_tick_2(thing)
{
	state	= thing.checked;
	tick_list	= get_Sub_Class_Elements('', thing.className);
	if(thing.id == '_Whole') {
		for(i=0; i<tick_list.length; i++) {
			tick_list[i].checked	= state;
		}
		window.LOG	+= "\nWhole group being ticked";
	} else {
		for(count = 0, i = 0; i<tick_list.length; i++) {
			if(tick_list[i].id != '_Whole' && tick_list[i].checked)
				count++;
		}
		if(count == tick_list.length-1)
			document.getElementById('_Whole').checked	= true;
		else
			document.getElementById('_Whole').checked	= false;
		window.LOG	+= "\ncount = "+count+", length = "+tick_list.length;
	}
	return;
}

function get_Sub_Class_Elements(container, search_class)
{
	if(container != '' && document.getElementById(container) != null)
		BASE	= document.getElementById(container);
	else
		BASE	= document;

	if(!bad_browser && typeof BASE.getElementsByClassName(search_class) == 'function') {
		return BASE.getElementsByClassName(search_class);
	}

	elem_list	= BASE.getElementsByTagName('*');
	out_list = new Array();

	for(j=0, i=0; i<elem_list.length; i++)
	{
		if(elem_list[i].className == search_class || elem_list[i].className.indexOf(search_class) != -1) {
			out_list[j]	= elem_list[i];
			j++;
		}
		classes	= new Array();
	}
	elem_list	= new Array();	//clear it away
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