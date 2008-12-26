function setMaxLength() {
	var x = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'counter';
	for (var i=0;i<x.length;i++) {
		if (x[i].getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x[i];
			counterClone.innerHTML = 'Ha utilizado <span>0</span> de '+x[i].getAttribute('maxlength')+' caracteres disponibles';
			x[i].parentNode.insertBefore(counterClone,x[i].nextSibling);
			x[i].relatedElement = counterClone.getElementsByTagName('span')[0];
			x[i].onkeypress = checkMaxLength;
			x[i].onkeypress();
		}
	}
}

function checkMaxLength(evt) {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	this.relatedElement.firstChild.nodeValue = currentLength;
	if  (evt) {
		var e = evt || window.event;
		var key = e.which || e.keyCode;
		if (!(key >= 13 && key <= 126)) {
			return true;
		}
	}
	if (currentLength >= maxLength) {
		this.relatedElement.className = 'toomuch';
	} else {
		this.relatedElement.className = '';
	}
	
	return (currentLength < maxLength);
}
