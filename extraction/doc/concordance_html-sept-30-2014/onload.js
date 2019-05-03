
var i = location.href.indexOf("?h=");
if (i > 0) {
	var j = location.href.indexOf("#");
	var id = location.href.substring(i + 3, j);
	var element = document.getElementById(id);
	if (element != null)
		element.className += " highlighted";
}