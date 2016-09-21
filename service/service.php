<?php
// -------------------------------------------------------------
// Íåñêîëüêî ñëîâ ïî-ðóññêè â êîäèðîâêå Windows
// äëÿ òîãî, ÷òîáû ÷àñòîòíûé àíàëèçàòîð Far-à ïðàâèëüíî
// ïîíèìàë, ÷òî ýòîò ôàéë íà ñàìîì äåëå ïðåäñòàâëÿåò ñîáîé
// ôàéë â êîäèðîâêå Windows.
// -------------------------------------------------------------
// Ñêðèïò íàïèñàí ]DichlofoS[ Systems, 2006
// -------------------------------------------------------------

// Service functions
error_reporting(E_ALL);

$strProtection = 'dichlofos-script-protection-string';

// -------------------------------------------------------------
// My own strtoupper realization
$strStrToUpperAH = 'ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß';
$strStrToUpperAL = 'abcdefghijklmnopqrstuvwxyzàáâãäå¸æçèéêëìíîïðñòóôõö÷øùúûüýþÿ';

$arrStrToUpperAH = array();
$arrStrToUpperAL = array();

for ($i = 0; $i < strlen($strStrToUpperAH); $i++)
    $arrStrToUpperAH[] = $strStrToUpperAH[$i];

for ($i = 0; $i < strlen($strStrToUpperAL); $i++)
    $arrStrToUpperAL[] = $strStrToUpperAL[$i];

function myStrToUpper($strS) {
    global $arrStrToUpperAH;
    global $arrStrToUpperAL;
    $strS = str_replace($arrStrToUpperAL, $arrStrToUpperAH, $strS);
    return $strS;
}

// -------------------------------------------------------------
// My own strtolower realization
$strStrToLowerAH = 'ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß';
$strStrToLowerAL = 'abcdefghijklmnopqrstuvwxyzàáâãäå¸æçèéêëìíîïðñòóôõö÷øùúûüýþÿ';

$arrStrToLowerAH = array();
$arrStrToLowerAL = array();

for ($i = 0; $i < strlen($strStrToLowerAH); $i++)
    $arrStrToLowerAH[] = $strStrToLowerAH[$i];

for ($i = 0; $i < strlen($strStrToLowerAL); $i++)
    $arrStrToLowerAL[] = $strStrToLowerAL[$i];

function myStrToLower($strS) {
    global $arrStrToLowerAH;
    global $arrStrToLowerAL;
    $strS = str_replace($arrStrToLowerAH, $arrStrToLowerAL, $strS);
    return $strS;
}

// -------------------------------------------------------------
// Analog of WriteLn in PASCAL
function WriteLine($fStream, $strLine) {
    fwrite($fStream, "$strLine\r\n");
}

// -------------------------------------------------------------
// Indenting WriteLine
function WriteHLine($fStream, $nCount, $strLine) {
    $strSpaces = '';
    for ($i = 0; $i < $nCount; $i++) $strSpaces = $strSpaces.' ';
    fwrite($fStream, $strSpaces.$strLine."\r\n");
}

// -------------------------------------------------------------
// Echoes a message to the current table
function RepMsg($strMessage) {
    echo("<tr><td>$strMessage</td></tr>\r\n");
}

// -------------------------------------------------------------
// Echoes an error message to the current table
function RepError($strMessage) {
    echo("<tr><td style=\"color: #FF0000\">$strMessage</td></tr>\r\n");
}

// -------------------------------------------------------------
// Echoes a message to the current table
function RepMsgEx($strMessage, $strStyle) {
    echo("<tr><td style=\"$strStyle\">$strMessage</td></tr>\r\n");
}

// -------------------------------------------------------------
// This is the replacement for file() function because original file()
// includes end-of-line characters in array items. We don't need such things
function fileCutEOL($strFileName) {
    $arrLines = file($strFileName);
    for ($i = 0; $i < count($arrLines); $i++) {
        $arrLines[$i] = str_replace("\r", '', $arrLines[$i]);
        $arrLines[$i] = str_replace("\n", '', $arrLines[$i]);
    }
    return $arrLines;
}

// -------------------------------------------------------------
// Glues array to one string using $strSep as delimiter
function ArrayToString($arrData, $strSep) {
    if (!count($arrData))
        return '';

    $strResult = $arrData[0];
    for ($i = 1; $i < count($arrData); $i++)
        $strResult .= $strSep.$arrData[$i];
    return $strResult;
}

// -------------------------------------------------------------
// Returns true if array is missing or empty
function ArrEmpty($arrTest) {
    if (!isset($arrTest))
        return true;
    if (!is_array($arrTest))
        return true;
    if (!count($arrTest))
        return true;
    return false;
}

// -------------------------------------------------------------
// Returns true if string is missing or empty
function StrEmpty($strTest) {
    if (!isset($strTest))
        return true;
    if (!$strTest)
        return true;
    return false;
}

// -------------------------------------------------------------
// Filters string post variable
function ProcessStringPostVar($strVarName, $strDefaultValue = '') {
    global $$strVarName;
    if (!isset($$strVarName))
        return $strDefaultValue;
    else
        if (is_string($$strVarName))
            return (get_magic_quotes_gpc() ? stripslashes($$strVarName) : $$strVarName);
        else
            return $strDefaultValue;
}

// -------------------------------------------------------------
// Filters low ASCII (ord < 32)
function FilterLowASCII($strString) {
    for ($i = 0; $i < strlen($strString); $i++)
        if (ord($strString[$i]) < 32)
            $strString[$i] = ' ';
    return $strString;
}

// -------------------------------------------------------------
// Filters double quotes
function FilterDQuotes($strString) {
    for ($i = 0; $i < strlen($strString); $i++)
        if ($strString[$i] == '"')
            $strString[$i] = ' ';
    return $strString;
}

// ---------------------------------------------
// Zero expansion routine
function ZExp($strNum, $nWidth) {
    while (strlen($strNum) < $nWidth)
        $strNum = '0'.$strNum;
    return $strNum;
}
