<?php
  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);
    
  include "common.php";
  if ($bDebugEnabled) error_reporting(E_ALL);
  if (!isset($section)) $section = "0";

	$CurrentMenuItem = $mnuAlgebra;
	PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
	DisplayPage($CurrentMenuItem, $arrCat, $section);
	PutPageFooter($strDMVNMail);
?>
