function setMaxLength() {
	var x = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'counter';
	for (var i=0;i<x.length;i++) {
		if (x[i].getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x[i];
			counterClone.innerHTML = '<span>0</span>/'+x[i].getAttribute('maxlength');
			x[i].parentNode.insertBefore(counterClone,x[i].nextSibling);
			x[i].relatedElement = counterClone.getElementsByTagName('span')[0];
			x[i].onkeypress = x[i].onchange = checkMaxLength;
			x[i].onkeypress();
		}
	}
}

function checkMaxLength(evt) {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	this.relatedElement.firstChild.nodeValue = currentLength;
	var evt = (evt) ? evt : document.event;
	var charCode = (typeof evt.which != "undefined") ? evt.which : ((typeof evt.keyCode != "undefined") ? evt.keyCode : 0);

	if (!(charCode >= 13 && charCode <= 126)) {
	return true;
	}
	if (currentLength >= maxLength) {
		this.relatedElement.className = 'toomuch';
	} else {
		this.relatedElement.className = '';
	}
	
	return (currentLength < maxLength);
}
