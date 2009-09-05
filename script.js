// ---------------------------------------------------------------------
function ById(id)
{
  if (document.getElementById)
    e = document.getElementById(id);
  else if (document.all)
    e = document.all[id];
  else if (document.layers)
    e = document.layers[id];
  else
  {
    alert('Please mail a bugreport to dmvn@mccme.ru about an error in script::ById(). Please supply some information about your Internet browser in your bugreport');
    return 0;
  }
  return e;
}
// ---------------------------------------------------------------------
function StoreCaret(e)
{
  if (document.selection && document.selection.createRange)
    e.caretPos = document.selection.createRange().duplicate(); 
}
// ---------------------------------------------------------------------
function InsertText(e, text)
{
  var txtarea = e;
  text = ' ' + text + ' ';
  if (txtarea.createTextRange && txtarea.caretPos)
  {
    var caretPos = txtarea.caretPos;
    caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
    txtarea.focus();
  }
  else
  {
    txtarea.value  += text;
    txtarea.focus();
  }
}
