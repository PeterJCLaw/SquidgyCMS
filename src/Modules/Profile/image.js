
function refresh_preview(top,left,height,width) {
	$('previewbox').style.backgroundPosition = -1*left + 'px ' + -1*top + 'px';
	$('previewbox').style.height = height+'px';
	$('previewbox').style.width = width+'px';
}

function click(e) {
	var out = 'CLICK:';
	$('debugbox').innerHTML = out
	if(!e)
		e = window.event;

	e.preventDefault();
	e.stopPropagation();

	if(e.type == 'mousedown') {
		window.onmousemove = move;
		window.onmouseup = click;
	} else {
		window.onmousemove = null;
		window.onmouseup = null;
		return;
	}

	_MOUSE_X = e.pageX;
	_MOUSE_Y = e.pageY;

	out += '<br />pageX:' + _MOUSE_X + '<br />pageY:' + _MOUSE_Y;

	$('debugbox').innerHTML = out+'<br /><span id="jam"></span>';

	originTop = $('selectbox').offsetTop;
	originLeft = $('selectbox').offsetLeft;

	originHeight = $('grabbox').offsetHeight;
	originWidth = $('grabbox').offsetWidth;

	_TARGET = e.target;
	return;
}

function move(e) {
	var out = 'MOVE:';
	$('jam').innerHTML = out
	if(!e)
		e = window.event;

	e.preventDefault();
	e.stopPropagation();

	var left = e.pageX - _MOUSE_X;
	var top = e.pageY - _MOUSE_Y;

	out += '<br />originTop:' + originTop + '<br />top:' + top + '<br />originLeft:' + originLeft + '<br />left:' + left;

	var select_top = originTop + top;
	var select_left = originLeft + left;

	if(_TARGET.id == 'grabbox') {
		$('selectbox').style.top = select_top + 'px';
		$('selectbox').style.left = select_left + 'px';
	} else {
		var classes = (_TARGET.className+' ').split(' ');

		if(classes[0] == 'n' || classes[1] == 'n') {
			$('selectbox').style.top = select_top + 'px';
			$('grabbox').style.height = originHeight - top + 'px';
		}

		if(classes[0] == 'w' || classes[1] == 'w') {
			$('selectbox').style.left = select_left + 'px';
			$('grabbox').style.width = originWidth - left + 'px';
		}

		if(classes[0] == 's' || classes[1] == 's') {
			$('grabbox').style.height = originHeight + top + 'px';
			top = 0;
		}

		if(classes[0] == 'e' || classes[1] == 'e') {
			$('grabbox').style.width = originWidth + left  + 'px';
			left = 0;
		}

		if(classes.length < 3) {	//not a corner
			if(classes[0] == 'n' || classes[0] == 's')
				left = 0;
			else if(classes[0] == 'e' || classes[0] == 'w')
				top = 0;
		}
	}

	if(_TARGET.id != 'grabbox' && fixedRatio != false) {
		box_ratio = $('grabbox').offsetHeight / $('grabbox').offsetWidth;

		if(box_ratio > fixedRatio)
			$('grabbox').style.height = $('grabbox').offsetWidth*fixedRatio  + 'px';
		else if(box_ratio < fixedRatio)
			$('grabbox').style.width = $('grabbox').offsetHeight/fixedRatio  + 'px';

		new_box_ratio = $('grabbox').offsetHeight / $('grabbox').offsetWidth;
		out += '<br />fixedRatio:' + fixedRatio + '<br />box_ratio:' + box_ratio + '<br />new_box_ratio:' + new_box_ratio;
	}

	$('jam').innerHTML = out;
	var prev_top = originTop + top - 5;
	var prev_left = originLeft + left - 5;

	refresh_preview(prev_top,prev_left,$('grabbox').offsetHeight,$('grabbox').offsetWidth);
//*/
}

add_loader( function() { $('previewbox').style.backgroundImage = 'url('+$('srcImg').src+')'; });

var _MOUSE_X,_MOUSE_Y,_TARGET,originTop,originLeft,originHeight,originWidth;
var fixedRatio = false; // 1.2;	//float: height/width; false: free shaping

