<?php
  include "data/global.inc";
  include "service/service.php";
  include "service/html.php";
  include "gcommon.php";

  // DEGUGGING
  $bDebugEnabled = true;
  // ---------

  $arrMenuData = fileCutEOL('menu.dat');
  foreach ($arrMenuData as $strMenuDataItem)
  {
    $arrMD = explode('|', $strMenuDataItem);
    if (count($arrMD) != 6) continue; // ignore

    $arrMenuVars[] = $arrMD[0];
    $arrMenuColors[] = $arrMD[1];
    $arrMenuTitles[] = $arrMD[2];
    $arrMenuFiles[] = $arrMD[3];
    $arrCat[] = $arrMD[4];
    $arrMenuDesc[] = $arrMD[5];
  }

  for ($i = 0; $i < count($arrMenuVars); $i++)
    ${'mnu'.$arrMenuVars[$i]} = $i;
  
  $strDMVNMail = 'dmvn[(at)]mccme([dot])ru';
  $strDMVNMailReal = 'dmvn@mccme.ru';

  // Admin password: MD5 HashSum
  $strAdminPassword = '06d7c3b56e8e4fd3836d52320ae98e37'; // [DMVN Password]
  $strUserPassword = '24ec931be749f84b90fcecc7aef66736'; // forumuser

  // Data File Names
  $strForumFileName = "userdata/forumdata.dat";
  $strNCFileName = "userdata/ncmails.dat";
  $strCFileName = "userdata/cmails.dat";
  $strSearchLogFileName = "userdata/searchlog.dat";
  $strSearchReplaceFileName = "data/srep.dat";
  $strLogFileName = "../logs/www-access_log";
    
  $nTimeShift = 3*24*3600; // Three days timeshift
  $lblSubmitEMail = "EMail:";
  $lblEMailCP = "���������:";
  $lblSubmitMail = "�������� � ������ ��������";
  $strMailConfirmSubject = "DMVN WebSite News&Information Confirmation";
  $strMailSubscriptionSubject = "DMVN WebSite News&Information";
  $strMailConfirmText = "This informs you that Your e-mail address was specified in order\n".
                        "to add You to DMVN WebSite News&Information mailing list\n".
                        "DMVN WebSite http://dmvn.mexmat.net. If you didn't do that,\n".
                        "please delete this letter and forget about it. Otherwise,\n".
                        "please confirm that you want to receive news from DMVN WebSite\n".
                        "via accessing the following link:\n".
                        "http://dmvn.mexmat.net/index.php";
  
  $cpWin = 0;
  $cpKoi = 1;
  $arrCodepageNames[$cpWin] = "windows-1251";
  $arrCodepageNames[$cpKoi] = "koi8-r";

	// read format file
	$fFormat=fopen('format.dat', 'r');
	if (!$fFormat) die('Format file is missing. ');
	$aFormatFiles=array();
	$aFormatDesc=array();
	while (!feof($fFormat)) {
		$sL=trim(fgets($fFormat));
		if (empty($sL)) continue;
		$aL=explode('|', $sL);
		if (count($aL)!=3) die("Bad line in format file: '$sL'. ");
		$aFormatFiles[$aL[0]]=$aL[1];
		$aFormatDesc[$aL[0]]=$aL[2];		
	}
	
	session_start();
	$bSessionStarted=true; // set flag to indicate session mechanism is already initialized
	SetPermissions();
	// -------------------------------------------------------------
	function Register($sKey) {
		global $bSessionStarted;
		if (!$bSessionStarted) die('Register: Session is not started yet. ');
		if (!session_is_registered($sKey)) session_register($sKey);
	}
	// -------------------------------------------------------------
	function Unregister($sKey) {
		global $bSessionStarted;
		if (!$bSessionStarted) die('Unregister: Session is not started yet. ');
		if (session_is_registered($sKey)) session_unregister($sKey);
	}
	// -------------------------------------------------------------
	function SetPermissions() {
		global $bSessionStarted;
		if (!$bSessionStarted) die('SetPermissions: Session is not started yet. ');
		
		global $bAuth;
		global $bAdmin;
		global $strSNUserRights;
		global $strSNAdminRights;
		$bAuth=$bAuth || session_is_registered($strSNUserRights);
		$bAuth=$bAuth || session_is_registered($strSNAdminRights);
		$bAdmin=session_is_registered($strSNAdminRights);
	}
  // -------------------------------------------------------------
  function bSymbolValid($strSym)
  {
    return ereg("[a-zA-Z0-9\.\@\_\-]", $strSym);
  }
  // -------------------------------------------------------------
  function bEMailValid($strEMail)
  {
    if (!strlen($strEMail)) return false;
    for ($i = 0; $i < strlen($strEMail); $i++)
      if (!bSymbolValid($strEMail{$i})) return false;
    return true;
  }
  // -------------------------------------------------------------
  // Returns a local link to site menu item
  function sHRef($nIndex)
  {
    global $arrMenuFiles;
    global $arrMenuTitles;
    return llink($arrMenuFiles[$nIndex], $arrMenuTitles[$nIndex]);
  }
  // -------------------------------------------------------------
	function GenerateMenu($arrMFiles, $arrMTitles, $arrMColors, $arrCat, $CurrentMenuItem) {
		$arrCUTime = fileCutEOL("data/cutime.dat");

		foreach ($arrCUTime as $strCUTime) {
			$arrL = explode("|", $strCUTime);
			$arrCUTCat[] = $arrL[0];
			$arrCUTTime[] = $arrL[1];
		}?>
		<div class="Menu"><?php
		for ($i = 0; $i < count($arrMTitles); $i++)
		{
			$sty=($i==$CurrentMenuItem) ? 'mselected' : $arrMColors[$i];
			echo "\r\n<a".attr('class',"left $sty").attr('href', $arrMFiles[$i]).'>'.$arrMTitles[$i].'</a>';
		}?>
		</div>
		<?php
	}
  // -------------------------------------------------------------
  function bCodepageValid($arrCodepageNames, $nCodepage)
  {
    return ($nCodepage >= 0 && $nCodepage < count($arrCodepageNames));
  }
  // -------------------------------------------------------------
  function strDecodeCodepage($arrCodepageNames, $nCodepage)
  {
    if (bCodepageValid($arrCodepageNames, $nCodepage)) return $arrCodepageNames[(integer)$nCodepage]; else return "";
  }
  // -------------------------------------------------------------
  function strGetCodepageOptions($arrCodepageNames)
  {
    $strOutput = "";
    for ($i = 0; $i < count($arrCodepageNames); $i++)
      $strOutput .= '<OPTION '.attr('value', $i).'>'.strDecodeCodepage($arrCodepageNames, $i).'</OPTION>'; 
    return $strOutput;
  }
  // -------------------------------------------------------------
  function strRecodeToCodepage($strText, $arrCodepageNames, $nCodepage)
  {
    if (function_exists('iconv')) return iconv($arrCodepageNames[0], strDecodeCodepage($arrCodepageNames, $nCodepage), $strText);
    else return $strText;
  }
  // -------------------------------------------------------------
	function PutMetaInfo($CurrentMenuItem) {
		$fMeta=fopen('meta.dat', 'r');
		if (!$fMeta) {
			echo "Cannot open metadata description file. ";
			die();
		}
		while (!feof($fMeta)) {
			$sMeta=trim(fgets($fMeta));
			$aMeta=explode('|', $sMeta);
			if (count($aMeta) < 4) {
				echo "Invalid meta data format at line $sMeta.";
				die();
			}
			global ${'mnu'.$aMeta[0]};
			if ($CurrentMenuItem != ${'mnu'.$aMeta[0]}) continue;
			$sTitle=htmlspecialchars($aMeta[1]);
			$sKeywords=$aMeta[2];
			$sDesc=$aMeta[3];
			echo "\r\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\" />\r\n";
			echo "<meta name=\"author\" content=\"DMVN\" />\r\n";
			echo "<meta name=\"keywords\" content=\"$sKeywords\" />\r\n";
			echo "<meta name=\"description\" content=\"$sDesc\" />\r\n";
			echo "<title>������� ��������� DMVN :: $sTitle</title>\r\n";
			break;
		}
		fclose($fMeta);
	}
  // -------------------------------------------------------------
	// TODO: remove strSiteLastUpdate (make global)
	function PutPageHeader($arrMFiles, $arrMTitles, $arrMColors, $CurrentMenuItem, $arrCat, $strSiteLastUpdate, $section) {
		// TODO: fix DOCTYPE! (this DOCTYPE spec heavily breaks forum styles)
		//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\r\n";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<?php PutMetaInfo($CurrentMenuItem);?>
		<link rel="stylesheet" type="text/css" href="styles.css" />
	</head>
	<body>
		<script type="text/javascript" src="script.js"></script>
		<table class="Top">
			<tr>
				<td style="width: 200px; text-align: center;">
					<?php echo flink('http://www.mexmat.net', '<img alt="MexMat.Net" src="images/mexmatnet.png" class="MexMat" />');?>
				</td>
				<td style="text-align: center;"><?php echo llink('/', '<img alt="DMVN Logo" src="images/dmvnlogo.png" />');?></td>
			</tr>
		</table>
		<table class="Main">
			<tr>
				<td class="MainLeft">
					<div class="TopTbl">����</div>
					<?php
		GenerateMenu($arrMFiles, $arrMTitles, $arrMColors, $arrCat, $CurrentMenuItem);
		DisplayNews($CurrentMenuItem, $arrCat, $arrMFiles);
		DisplaySectionsMenu($CurrentMenuItem, $arrCat, $arrMFiles, $section);
		?>
					<div class="Counters"><?php echo file_get_contents("li.dat"); ?></div>
		<?php
		global $bAdmin;
		if ($bAdmin) {
			echo '<div style="text-align: center"><a href="data/sdg.php?ref='.$arrMFiles[$CurrentMenuItem].'"><b>Update&nbsp;DB</b></a></div>';
		}?>
				</td>
				<td class="MainDiv" /><!-- black divider -->
				<td class="MainRight">
					<div class="TopTbl"><?php echo $arrMTitles[$CurrentMenuItem];?></div>
		<?php
  }
  // -------------------------------------------------------------
  function PutPageFooter($strMail) {?>
				</td><!-- MainRight-->
			</tr>
		</table>
		<div class="Footer">Copyright &copy;&nbsp;2003&#8211;2010, <?php echo
			llink('mailto:'.$strMail, 'DMVN'); ?>. All rights reserved
		</div>
	</body>
</html>
	<?php
	}
	// -------------------------------------------------------------
	// 1,2,3,5,6,M,S,10,11,12,13,K -> 1-3,5,6,M,S,10-13,K
	function MakeRange($sSections) {
		$sSections=str_replace(' ', '', $sSections);
		$aS=explode(',', ",$sSections,");
		$nL=count($aS);
		$sRes='';
		$bDash=false;
		for ($i=1; $i<$nL-1; ++$i) { // we're LR(1,-1)
			$sC=$aS[$i+0];// current
			$nC=@intval($sC);
			$sP=$aS[$i-1];// previous
			$nP=@intval($sP);
			$sN=$aS[$i+1];// next
			$nN=@intval($sN);
			if ($nC>0 && $nP>0 && $nP+1==$nC && $nN>0 && $nN-1==$nC) {
				if (!$bDash) {
					$sRes.='-';
					$bDash=true;
				}
			} else {
				if ($bDash) {
					$sRes.="$sC";
					$bDash=false;
				} else {
					$sRes.=" $sC";
				}
			}
		}
		return trim($sRes);
	}
	// -------------------------------------------------------------
	function PutItem($strCategory, $strSection, $strItemSectionID, $strTitle, $strDesc, $strSearchID, $arrResData) {
		global $aFormatFiles;
		global $aFormatDesc;
		
		$bDisp = false;
		if ($strSection == $strItemSectionID || $strSection == '0' || $strSection == '') $bDisp = true;
		
		$arrTargetSections = explode(",", $strItemSectionID);
		$arrRequestedSections = explode(",", $strSection);
		for ($i = 0; $i < count($arrTargetSections); $i++) {
			for ($j = 0; $j < count($arrRequestedSections); $j++) {
				if ($arrTargetSections[$i] == $arrRequestedSections[$j]) $bDisp = true;
			}
		}
		
		if (!$bDisp || $strDesc=='.section.' || $strDesc=='.newsblock.') return;
		echo '<div class="PlainTitle"><span class="TitleSection">';
		echo "<a name=\"$strSearchID\"></a>";
		if ($strItemSectionID) echo '['.MakeRange($strItemSectionID).']';
		echo '</span><span class="Title">'.$strTitle."</span>\r\n";
		echo "</div>\r\n";
		if ($strDesc) echo '<div class="ItemDesc">'.$strDesc."</div>\r\n";
		if (!ArrEmpty($arrResData)) {
			echo '<div class="Files">'."\r\n";
			for ($i = 0; $i < count($arrResData); $i+=4) {
				$sFN = $arrResData[$i+0];
				$sSize = $arrResData[$i+1];
				$sDate = $arrResData[$i+2];
				$sFmt = $arrResData[$i+3];
				if (array_key_exists($sFmt, $aFormatFiles)) {
					$sIcon=$aFormatFiles[$sFmt];
					$sDesc=$aFormatDesc[$sFmt];
					// TODO: title tag for image
					$sPFmt="<img class=\"Icon\" src=\"/images/icons/$sIcon\" alt=\"$sDesc\" />";
				} else {
					$sPFmt=$sFmt; // leave 'as is'
				}
				echo llink("/content/$strCategory/$sFN", "$sPFmt $sSize")."&nbsp;&#8212;&nbsp;$sDate<span class=\"FileSep\">&nbsp;</span>";
			}
			echo "</div>\r\n";
		}
	}
	// -------------------------------------------------------------
	function PutTextBlock($strCaption, $strText, $strCaptionColor, $strTextColor) {
		$sCaptionStyle=($strCaptionColor) ? attr('style', "color: #$strCaptionColor") : '';
		$sTextStyle=($strTextColor) ? attr('style', "color: #$strTextColor") : '';
		echo "<p class=\"Subtitle\" $sCaptionStyle>$strCaption</p>\r\n";
		echo "<p class=\"PlainTextFP\" $sTextStyle>$strText</p>\r\n";
	}
	// -------------------------------------------------------------
	function DisplayPage($CurrentMenuItem, $arrCat, $strSection) {
		$strCatName = $arrCat[$CurrentMenuItem];
		if (!file_exists("data/$strCatName.dat")) {
			echo "<p>ERROR: This site section is empty! Data file is missing. Please inform site administration about this, including link to this page.</p>";
			return;
		}
		echo "<div class=\"Page\">\r\n";
		$fData = fopen("data/$strCatName.dat", "r");
		//echo '<tr><td colspan="2">';
		// Searching text blocks
		while (!feof($fData)) {
			$strItemData = trim(fgets($fData));
			$arrItem = explode("|", $strItemData);
			if (count($arrItem) < 4) continue;
			if ($arrItem[3] == '.textblock.') {
				PutTextBlock($arrItem[1], $arrItem[2], $arrItem[4], $arrItem[5]);
			}
		}
		fclose($fData);

		// Displaying file database
		$fData = fopen("data/$strCatName.dat", "r");
		while (!feof($fData)) {
			$strItemData = trim(fgets($fData));
			$arrItem = explode("|", $strItemData);
			if (count($arrItem) < 4) continue;
			if ($arrItem[3] != '.section.' && $arrItem[3] != '.textblock.' && $arrItem[3] != '.newsblock.')
			{
				$arrResData = NULL;
				for ($i = 5; $i < count($arrItem); $i++) $arrResData[] = $arrItem[$i];
				PutItem($arrItem[0], $strSection, $arrItem[1], $arrItem[2], $arrItem[3], $arrItem[4], $arrResData);
			}
		}
		fclose($fData);
		echo "</div>\r\n";
	}
	// -------------------------------------------------------------
	function DisplaySectionsMenu($CurrentMenuItem, $arrCat, $arrMFiles, $section) {
		$strCatName = $arrCat[$CurrentMenuItem];
		if (!file_exists("data/$strCatName.dat")) {
			// TODO: warning?
			return;
		}
		$fData = fopen("data/$strCatName.dat", "r");

		$bHasMoreThanOneSection = false;
		$strHTMLOptions='';

		while (!feof($fData)) {
			$strItemData = trim(fgets($fData));
			$arrItem = explode("|", $strItemData);
			if (count($arrItem) < 4) continue;
			// Here we analyze only sections
			if ($arrItem[3] == '.section.') {
				$bHasMoreThanOneSection=true;
				$sSel=($arrItem[1]==$section) ? ' selected' : '';
				$strHTMLOptions .= "<option$sSel value=\"{$arrItem[1]}\">{$arrItem[2]}</option>\r\n";
			}
		}
		if ($bHasMoreThanOneSection) {?>
			<div class="TopTbl">������</div>
			<div class="Filter">
				<select class="Filter" id="SectionFilter" onchange="SectionFilterOnChange();">
					<option value="0">��</option>
					<?php echo $strHTMLOptions; ?>
				</select>
			</div>
			<?php
		}
		fclose($fData);
	}
	// -------------------------------------------------------------
  function DisplayNews($CurrentMenuItem, $arrCat, $arrMFiles) {
		$strCatName = $arrCat[$CurrentMenuItem];
		if (!file_exists("data/$strCatName.dat")) {
			// TODO: warning??
			return;
		}
		$fData = fopen("data/$strCatName.dat", "r");
		$bAnyNews = false;
		$strHTMLOut="";
		while (!feof($fData)) {
			$strItemData = trim(fgets($fData));
			$arrItem = explode("|", $strItemData);
			if (count($arrItem) < 4) continue;
			// Here we analyze only sections
			if ($arrItem[3] == '.newsblock.') {
				$strHTMLOut .= '<div class="NewsBlock"><b>'.$arrItem[1].'</b> &#8211; '.$arrItem[2]."</div>\r\n";
				$bAnyNews = true;
			}
		}
		if ($bAnyNews) {?>
			<div class="TopTbl">�������</div>
			<div class="News">
				<?php echo $strHTMLOut; ?>
			</div><!-- News -->
			<?php
		}
		fclose($fData);
	}
  // --------------------------------------------------------------------------------
  // Converts string from user input to our internal format for storing data
  function InputToStore($strInput)
  {
  /* The sequence of substitutions must be exactly as shown:
      \        --> \s
      |        --> \v
      <cr><lf> --> \c
      <cr>     --> \c
      <lf>     --> \c
  */
    $strInput = str_replace("\\", "\\s", $strInput);
    // Replace our symbol | with \v
    $strInput = str_replace("|", "\\v", $strInput);
    // Finally, replace all <cr><lf>
    $strInput = str_replace("\r\n", "\\c", $strInput);
    $strInput = str_replace("\r", "\\c", $strInput);
    $strInput = str_replace("\n", "\\c", $strInput);
    return $strInput;
  }
  // -------------------------------------------------------------
  // Reverses the actions of the previous function
  function StoreToInput($strStore)
  {
    $strStore = str_replace("\\c", "\r\n", $strStore);
    $strStore = str_replace("\\v", "|", $strStore);
    $strStore = str_replace("\\s", "\\", $strStore);
    return $strStore;
  }
?>
