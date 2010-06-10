// ---------------------------------------------------------------------
function ById(id) {
	if (document.getElementById) {
		// afaik, now all modern browsers support it // mvel@ 2010
		e=document.getElementById(id);
	} else if (document.all) {
		e=document.all[id];
	} else if (document.layers) {
		e=document.layers[id];
	} else {
		alert('ById function failed for id='+id+
		'. Please write a bugreport to dmvn@mccme.ru. Please supply information about your browser version. ');
		return null;
	}
	return e;
}
// ---------------------------------------------------------------------
function StoreCaret(e) {
	if (document.selection && document.selection.createRange) {
		e.caretPos = document.selection.createRange().duplicate();
	}
}
// ---------------------------------------------------------------------
function InsertText(id, text) {
	var txtarea=ById(id);
	if (!txtarea) return;
	text=' '+text+' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos=txtarea.caretPos;
		caretPos.text=caretPos.text.charAt(caretPos.text.length - 1)==' ' ? caretPos.text+text + ' ' : caretPos.text+text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}
// ---------------------------------------------------------------------
function SectionFilterOnChange() {
	var e=ById('SectionFilter');
	if (!e) return;
	if (typeof(e.value)=='undefined') return;
	if (e.value===null) return;
	var sRef=String(document.location);
	var n=sRef.indexOf('?');
	if (n>0) sRef=sRef.substr(0,n);
	document.location=sRef+'?section='+e.value; // redirect
}
