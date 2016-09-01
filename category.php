<?php

extract($_SERVER);
extract($_ENV);
extract($_GET);
extract($_POST);
extract($_REQUEST);

include "common.php";

print_r($_REQUEST);

if ($bDebugEnabled) {
    error_reporting(E_ALL);
}
if (!isset($section)) {
    $section = "0";
}
global $MENU;
$category = xcms_get_key_or($_REQUEST, "category");

$CurrentMenuItem = $MENU[$category]["index"];
PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
DisplayPage($CurrentMenuItem, $arrCat, $section);
PutPageFooter($strDMVNMail);
