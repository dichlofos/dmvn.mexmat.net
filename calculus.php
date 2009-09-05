<?php
  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);
    
  include "common.php";
  if ($bDebugEnabled) error_reporting(E_ALL);
  if (!isset($section)) $section = "0";

  $CurrentMenuItem = $mnuCalculus;
  
  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
    
  echo '<TABLE align="center" width="95%"><TR><TD>';
  DisplayPage($CurrentMenuItem, $arrCat, $section);
  echo '</TD></TR></TABLE>';
  PutPageFooter($strDMVNMail);
?>
