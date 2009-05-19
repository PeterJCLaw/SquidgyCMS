
function refresh_preview() {
	$('previewbox').style.background
}

function click(ev) {
//	console.dir(ev);
	window._TARGET = ev.originalTarget;
	window._MOUSE_X = ev.clientX;
	window._MOUSE_Y = ev.clientY;

	if(ev.type == 'mousedown')
		ev.originalTarget.onmousemove = move;
	else
		ev.originalTarget.onmousemove = null;
}

function move(ev) {
	console.dir(ev);
	console.dir(ev.clientX);
	console.dir(window);
	console.dir(window._MOUSE_X);
/*	left = ev.clientX - window._MOUSE_X;
	top = ev.clientY - window._MOUSE_Y;
	console.dir(top);
	console.dir(left);
	_TARGET.style.marginTop = top+'px';
	_TARGET.style.marginLeft = left+'px';
*/
}

add_loader( function() { $('previewbox').style.backgroundImage = $('srcImg').src; });

