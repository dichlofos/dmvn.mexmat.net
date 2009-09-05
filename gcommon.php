<?php
  // ------------------------------------------------------------------------------------
  // gcommon.php
  // This is common part of DMVN WebSite and Site Data Generator
  // (C) Copyright by ]DichlofoS[ Systems, Inc, 2005-2009
  // ------------------------------------------------------------------------------------
  
  // -------------------------------------------------------------
  // Returns a far link
  function flink($strURL, $strTitle = "")
  {
    if (!$strTitle) $strTitle = $strURL;
    return "<a href=\"$strURL\" target=\"_blank\">$strTitle</a>";
  }
  // -------------------------------------------------------------
  // Returns a local link
  function llink($strURL, $strTitle = "")
  {
    if (!$strTitle) $strTitle = $strURL;
    return "<a href=\"$strURL\">$strTitle</a>";
  }

  // Session Var Names
  $strSNAdminRights = "adminrights";
  $strSNUserRights = "userrights";

  function RandomString($nLength)
  {
    $strResult = '';
    for ($i = 0; $i < $nLength; $i++) $strResult .= chr(97 + (rand() % 26));
    return $strResult;
  }
?>