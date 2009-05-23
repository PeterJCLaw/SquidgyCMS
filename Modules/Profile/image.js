
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

	originHeight = $('grabbox').offsetHeight;
	originWidth = $('grabbox').offsetWidth;

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

	var select_top = originTop + top;
	var select_left = originLeft + left;

	if(e.originalTarget.id != null && e.originalTarget.id == 'grabbox') {
		$('selectbox').style.top = select_top + 'px';
		$('selectbox').style.left = select_left + 'px';
	} else {
		var classes = (e.originalTarget.className+' ').split(' ');

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
	}
	var prev_top = originTop + top - 3;
	var prev_left = originLeft + left - 3;

	refresh_preview(prev_top,prev_left,$('grabbox').offsetHeight,$('grabbox').offsetWidth);
//*/
}

add_loader( function() { $('previewbox').style.backgroundImage = 'url('+$('srcImg').src+')'; });

