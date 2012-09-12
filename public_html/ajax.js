//Gets the browser specific XmlHttpRequest Object
function getXmlHttpRequestObject() {
	if (window.XMLHttpRequest) {
		return new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

//Our XmlHttpRequest object to get the auto suggest
var RushAjax = getXmlHttpRequestObject();

function updateSpan() {
	if (RushAjax.readyState == 4) {
		document.getElementById("ajax").innerHTML = RushAjax.responseText;
	}
}

function pushTimeUpdate() {
	var timestamp = Number(new Date());
	if (RushAjax.readyState == 4 || RushAjax.readyState == 0) {
		RushAjax.open("GET", 'ajax_query.php?uid=' + timestamp, true);
		RushAjax.onreadystatechange = updateSpan;
	}
	RushAjax.send(null);
}

pushTimeUpdate();
setInterval('pushTimeUpdate()', 1000);
