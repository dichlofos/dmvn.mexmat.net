<?php
// -------------------------------------------------------------
// Несколько слов по-русски в кодировке Windows
// для того, чтобы частотный анализатор Far-а правильно
// понимал, что этот файл на самом деле представляет собой
// файл в кодировке Windows.
// -------------------------------------------------------------
// Скрипт написан ]DichlofoS[ Systems, 2006
// -------------------------------------------------------------

// The short function for htmlspecialchars
// --------------------------------------------------------------------
function out($strVar) {
    return htmlspecialchars($strVar);
}

// Echoes line to improve readability of target HTML
// Should be used instead of standard 'echo'
// --------------------------------------------------------------------
function echol($strLine) {
    echo "$strLine\r\n";
}

// HTML Elements Library

// Attributes

// --------------------------------------------------------------------
// Makes attribute with some value
function attr($strName, $strValue) {
    return " $strName=\"$strValue\" ";
}

// <table>
// --------------------------------------------------------------------
// Returns table
// --------------------------------------------------------------------
function hTable($strLine, $strExtraAttr = '')
{
    return "<table $strExtraAttr>$strLine</table>";
}

// <tr>
// --------------------------------------------------------------------
// Returns table row
// --------------------------------------------------------------------
function hRow($strLine) {
    return "<tr>$strLine</tr>";
}

// <td>
// --------------------------------------------------------------------
// Returns table cell
// Content, ClassName, Width, Height, extra attributes
// --------------------------------------------------------------------
function hCell($strLine, $strClass = '', $strW = '', $strH = '', $strExtraAttr = '') {
    if ($strLine == '')
        $strLine = '&nbsp;';

    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    $aW = ($strW == '') ? '' : attr('width', $strW);
    $aH = ($strH == '') ? '' : attr('height', $strH);

    return "<td $aClass $aW $aH $strExtraAttr>$strLine</td>";
}

// <img>
// --------------------------------------------------------------------
// Returns image
// Link, Alt, ClassName, W, H, extra attributes
// --------------------------------------------------------------------
function hImg($strLink, $strAlt = '', $strClass = '', $strW = '', $strH = '', $strExtraAttr = '') {
    $aAlt = xu_empty($strAlt) ? '' : attr('alt', $strAlt);
    $aClass = xu_empty($strClass) ? '' : attr('class', $strClass);
    $aW = ($strW == '') ? '' : attr('width', $strW);
    $aH = ($strH == '') ? '' : attr('height', $strH);
    return "<img src=\"$strLink\" $aAlt $aClass $aW $aH $strExtraAttr />";
}

// <form>
// --------------------------------------------------------------------
// Returns form opening tag
// Name, Action, ClassName, extra attributes
// --------------------------------------------------------------------
function hoForm($strName, $strAction, $strClass = '', $strExtraAttr = '') {
    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    return "<form id=\"$strName\" action=\"$strAction\" $aClass $strExtraAttr method=\"post\">";
}

// </form>
// --------------------------------------------------------------------
// Returns form closing tag
// --------------------------------------------------------------------
function hcForm() {
    return '</form>';
}

// <input>
// --------------------------------------------------------------------
// Returns input
// Name, Type, ClassName, Value, extra attributes
// --------------------------------------------------------------------
function hInput($strName, $strType, $strClass = '', $strValue = '', $strExtraAttr = '') {
    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    $strValue = str_replace('"', '&quot;', $strValue);
    $aValue = ($strValue == '') ? '' : attr('value', $strValue);
    return "<input name=\"$strName\" id=\"$strName\" type=\"$strType\" $aClass $aValue $strExtraAttr />";
}

// <select>
// --------------------------------------------------------------------
// Returns select
// Name, Type, ClassName, Value, extra attributes
// --------------------------------------------------------------------
function hSelect($strName, $strOnChange, $strClass = '', $strValue = '', $strExtraAttr = '') {
    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    return "<select name=\"$strName\" onchange=\"$strOnChange\" $aClass $strExtraAttr>$strValue</select>";
}

// <textarea>
// --------------------------------------------------------------------
// Returns textarea
// Name, ClassName, Value, extra attributes
// --------------------------------------------------------------------
function hTextarea($strName, $strClass = '', $strValue = '', $strExtraAttr = '') {
    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    return "<textarea name=\"$strName\" $aClass $strExtraAttr>$strValue</textarea>";
}

// <a>
// --------------------------------------------------------------------
// Returns hyperlink
// Hyperlink, Content, ClassName, extra attributes
// --------------------------------------------------------------------
function hHref($strHyperLink, $strCaption, $strClass = '', $strExtraAttr = '') {
    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    return "<a href=\"$strHyperLink\" $aClass $strExtraAttr>$strCaption</a>";
}

// <p>
// --------------------------------------------------------------------
// Returns paragraph
// --------------------------------------------------------------------
function hPar($strLine, $strClass = '') {
    $aClass = ($strClass == '') ? '' : attr('class', $strClass);
    return "<p $aClass>$strLine</p>";
}

// <b>
// --------------------------------------------------------------------
function hBold($strText) {
    return "<b>$strText</b>";
}

// <strong>
// --------------------------------------------------------------------
function strong($strText) {
    return "<strong>$strText</strong>";
}

// <script>
// --------------------------------------------------------------------
function hScript($strScript) {
    return "<script type=\"text/javascript\">$strScript</script>";
}
