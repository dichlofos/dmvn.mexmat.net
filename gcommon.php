<?php
	// ------------------------------------------------------------------------------------
	// gcommon.php
	// This is common part of DMVN WebSite and Site Data Generator
	// (C) Copyright by ]DichlofoS[ Systems, Inc, 2005-2011
	// ------------------------------------------------------------------------------------
	
	// -------------------------------------------------------------
	// Returns a far link (target attribute was removed to conform DOCTYPE)
	function flink($sURL, $sText="", $sTitle="") {
		if (!$sText) $sText=$sURL;
		$sT=($sTitle ? " title=\"$sTitle\"" : "");
		return "<a href=\"$sURL\"$sT>$sText</a>";
	}
	// -------------------------------------------------------------
	// Returns a local link
	function llink($sURL, $sText="", $sTitle="") {
		if (!$sText) $sText=$sURL;
		$sT=($sTitle ? " title=\"$sTitle\"" : "");
		return "<a href=\"$sURL\"$sT>$sText</a>";
	}

	// Session Var Names
	$strSNAdminRights="adminrights";
	$strSNUserRights="userrights";

	function RandomString($nLength) {
		$sResult='';
		for ($i=0; $i < $nLength; ++$i) $sResult .= chr(97 + (rand() % 26));
		return $sResult;
	}
?>