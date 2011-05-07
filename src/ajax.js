function GetXmlHttpObject()
{
	var xmlHttp	= null;
	try {
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch(e) {
		// Internet Explorer
		try {
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch(e) {
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

function ajax(method, url, params, do_this)
{
	var xmlHttp	= GetXmlHttpObject();

	if(xmlHttp == null) {
		alert("Your browser does not support AJAX!");
		window.location	+= (window.location.search == '' ? "?" : "&") + "ajax=0";
	}

	xmlHttp.onreadystatechange	= function() {
		if(xmlHttp.readyState == 4)
			do_this(xmlHttp);
	};

	if(method.toLowerCase() == 'post') {
		xmlHttp.open(method, url, true);
		xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xmlHttp.send(params);
	} else {
		xmlHttp.open(method, url+"?"+params, true);
		xmlHttp.send(null);
	}
	
	window.LOG	+= "\n"+method+": "+url+"?"+params;
}