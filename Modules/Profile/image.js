
function refresh_preview(top,left,height,width) {
	$('previewbox').style.backgroundPosition = -1*left + 'px ' + -1*top + 'px';
	$('previewbox').style.height = height+'px';
	$('previewbox').style.width = width+'px';
}

function click(e) {
	if(!e)
		e = window.event;

	e.preventDefault();
	e.stopPropagation();

	_MOUSE_X = e.pageX;
	_MOUSE_Y = e.pageY;

	var out = 'spam';
	out += '<br />pageX:' + _MOUSE_X + '<br />pageY:' + _MOUSE_Y;

	$('debugbox').innerHTML = out+'<br /><span id="jam"></span>';

	originTop = $('selectbox').offsetTop;
	originLeft = $('selectbox').offsetLeft;

	if(e.type == 'mousedown')
		e.originalTarget.onmousemove = move;
	else
		e.originalTarget.onmousemove = null;
//*/
}

function move(e) {
	if(!e)
		e = window.event;

	e.preventDefault();
	e.stopPropagation();

	var left = e.pageX - _MOUSE_X;
	var top = e.pageY - _MOUSE_Y;

	var out = 'originTop:' + originTop + '<br />top:' + top + '<br />originLeft:' + originLeft + '<br />left:' + left;
	$('jam').innerHTML = out;

	var prev_top = originTop + top;
	var prev_left = originLeft + left;

	$('selectbox').style.top = prev_top + 'px';
	$('selectbox').style.left = prev_left + 'px';

	refresh_preview(prev_top,prev_left,$('grabbox').offsetHeight,$('grabbox').offsetWidth);
//*/
}

add_loader( function() { $('previewbox').style.backgroundImage = 'url('+$('srcImg').src+')'; });
var _MOUSE_X,_MOUSE_Y,originTop,originLeft;

