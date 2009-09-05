<?php
  include "data/global.inc";
  include "service/service.php";
  include "service/html.php";
  include "gcommon.php";

  // DEGUGGING
  $bDebugEnabled = TRUE;
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
  $lblEMailCP = "Кодировка:";
  $lblSubmitMail = "Добавить в список рассылки";
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
  function GenerateMenu($arrMFiles, $arrMTitles, $arrMColors, $arrCat, $CurrentMenuItem)
  {
    $arrCUTime = fileCutEOL("data/cutime.dat");

    foreach ($arrCUTime as $strCUTime)
    {
      $arrL = explode("|", $strCUTime);
      $arrCUTCat[] = $arrL[0];
      $arrCUTTime[] = $arrL[1];
    }
    
    echo '<TABLE width="100%" border="0" cellpadding="0px" cellspacing="1px">';
    for ($i = 0; $i < count($arrMTitles); $i++)
    {
      echo('<TR><TD>');
      $sty = ($i == $CurrentMenuItem) ? 'mselected' : $arrMColors[$i];
      echo '<A '.attr('class', 'left '.$sty).attr('href', $arrMFiles[$i]).'>'.$arrMTitles[$i].'</A>';
      echo '</TD></TR>';
    }
    echo '</TABLE>';
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
  function PutPageHeader($arrMFiles, $arrMTitles, $arrMColors, $CurrentMenuItem, $arrCat, $strSiteLastUpdate, $section)
  {
    echo '
<HTML>
  <HEAD>
    <TITLE>DMVN WebSite &#8212; Учебные материалы &#8212; Обновлён: '.$strSiteLastUpdate.'</TITLE>
    <META http-equiv="Content-Type" content="text/html; charset=windows-1251">
    <meta name="author" content="DMVN">
    <meta name="keywords" content="образование, учебные материалы, мехмат, МГУ, учеба, лекции, семинары">
    <meta name="description" content="Сайт DMVN">
    <LINK rel="stylesheet" type="text/css" href="styles.css">
  </HEAD>
  <BODY bgColor="#000000" text="#cccccc">
    <script language="javascript" src="script.js"></SCRIPT>
    <TABLE bgColor="#00274f" border="0" width="100%" height="25">
      <TR>
        <TD width="200">
          <CENTER>'.flink('http://www.mexmat.net', '<IMG alt="MexMat.Net" border="0" src="images/mexmatnet.gif">').
         '</CENTER>
        </TD>
        <TD>
          <CENTER><IMG alt="DMVN Logo" border="0" src="images/dmvnlogo.gif"></CENTER>
        </TD>
      </TR>
    </TABLE>
    <TABLE border="0" height="400" width="100%">
      <TR>
        <TD bgColor="#00356a" height="326" vAlign="top" width="200">
          <TABLE bgColor="#00458a" border="0" width="100%">
            <TR>
              <TD class="TopTbl">Меню</TD>
            </TR>
          </TABLE>';
    GenerateMenu($arrMFiles, $arrMTitles, $arrMColors, $arrCat, $CurrentMenuItem);
    DisplayNews($CurrentMenuItem, $arrCat, $arrMFiles);
    DisplaySectionsMenu($CurrentMenuItem, $arrCat, $arrMFiles, $section);
    echo '
          <TABLE bgColor="#00356a" border="0" width="100%">
            <TR>
              <TD class="TopTbl">';
    $arrLIScript = file("li.dat");
    for ($i = 0; $i < count($arrLIScript); $i++)
      echo $arrLIScript[$i];
    echo '   </TD>
            </TR>
          </TABLE>';

    echo '<TABLE border="0" width="100%">
            <TR>
              <TD class="MenuText"> 
          <a href="data/sdg.php?ref='.$arrMFiles[$CurrentMenuItem].'">[Admin] Update&nbsp;DB</a>  
              </TD>
            </TR>
          </TABLE>';
    echo '
        </TD>
        <TD bgColor="#00254a" height="326" vAlign="top">
          <TABLE bgColor="#00458a" border="0" width="100%">
            <TR>
              <TD class="TopTbl">'.$arrMTitles[$CurrentMenuItem].'
              </TD>
            </TR>
          </TABLE>';
  }
  // -------------------------------------------------------------
  function PutPageFooter($strMail)
  {    
    echo '
        </TD>
      </TR>
    </TABLE>
    <CENTER><TABLE border="0" width="100%">
        <TR>
          <TD align="middle" width="50%"><P><BR>
              Copyright © 2003&#8211;2009, '.llink('mailto:'.$strMail, 'DMVN').'. All rights reserved.</P>
          </TD>
        </TR>
      </TABLE>
    </CENTER>
  </BODY>
</HTML>';
  }
  // -------------------------------------------------------------
  function PutItem($strCategory, $strSection, $strItemSectionID, $strTitle, $strDesc, $strSearchID, $arrResData)
  {
    $bDisp = false;
    if ($strSection == $strItemSectionID || $strSection == '0' || $strSection == '') $bDisp = true;
    
    $arrTargetSections = explode(",", $strItemSectionID);
    $arrRequestedSections = explode(",", $strSection);
    for ($i = 0; $i < count($arrTargetSections); $i++)
      for ($j = 0; $j < count($arrRequestedSections); $j++)
        if ($arrTargetSections[$i] == $arrRequestedSections[$j]) $bDisp = true;
            
    if ($strDesc != '.section.' && $strDesc != '.newsblock.' && $bDisp)
    {
      echo '<TR><TD class="PlainTitle" vAlign = "center" style="width: 0cm">';
      echo '<A NAME="'.$strSearchID.'"></A>';
      if ($strItemSectionID != '') echo '['.$strItemSectionID.']';
      echo '</TD><TD class="PlainTitle" vAlign = "center">';
      echo $strTitle.'</TD></TR>';
      if ($strDesc) echo '<TR><TD style ="width: 0cm"></TD><TD class="PlainText">'.$strDesc.'</TD></TR>';
      if (!ArrEmpty($arrResData))
      {
        echo '<TR><TD style ="width: 0cm"></TD><TD class="LUpd">';
        for ($i = 0; $i < count($arrResData); $i+=4)
          echo llink('/content/'.$strCategory.'/'.$arrResData[$i+0], $arrResData[$i+3].' ('.$arrResData[$i+1].')'). ' &#8212; '.$arrResData[$i+2].'. ';
        echo '</TD></TR>';
      }
    }
  }

  function PutTextBlock($strCaption, $strText, $strCaptionColor, $strTextColor)
  {
    echo '<P class="Subtitle" ';
    if ($strCaptionColor) echo attr('style', "color: #$strCaptionColor");
    echo '>';
    echo $strCaption;
    echo '</P>';

    echo '<P class="PlainTextFP" ';
    if ($strTextColor) echo attr('style', "color: #$strTextColor");
    echo '>';
    echo $strText;
    echo '</P>';
  }

  // -------------------------------------------------------------
  function DisplayPage($CurrentMenuItem, $arrCat, $strSection)
  {
    $strCatName = $arrCat[$CurrentMenuItem];
    if (!file_exists("data/$strCatName.dat")) {
      echo "<p>ERROR: This site section is empty! Data file is missing. Please inform site administration about this, including link to this page.</p>";
      return;
    }
    echo '<TABLE width="100%">';
    $fData = fopen("data/$strCatName.dat", "r");
    echo '<TR><TD colspan="2">';
    // Searching text blocks
    while (!feof($fData))
    {
      $strItemData = trim(fgets($fData));
      $arrItem = explode("|", $strItemData);
      if (count($arrItem) < 4) continue;
      if ($arrItem[3] == '.textblock.')
        PutTextBlock($arrItem[1], $arrItem[2], $arrItem[4], $arrItem[5]);
    }
    fclose($fData);
    echo '</TD></TR>';

    // Displaying file database
    $fData = fopen("data/$strCatName.dat", "r");
    while (!feof($fData))
    {
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
    echo '</TABLE>';
  }
  // -------------------------------------------------------------
  function DisplaySectionsMenu($CurrentMenuItem, $arrCat, $arrMFiles, $section)
  {
    $strCatName = $arrCat[$CurrentMenuItem];
    if (!file_exists("data/$strCatName.dat")) {
      // TODO: warning?
      return;
    }
    $fData = fopen("data/$strCatName.dat", "r");

    $bHasMoreThanOneSection = false;
    $strHTMLOptions = '';

    while (!feof($fData))
    {
      $strItemData = trim(fgets($fData));
      $arrItem = explode("|", $strItemData);
      if (count($arrItem) < 4) continue;
      // Here we analyze only sections
      if ($arrItem[3] == '.section.')
      {
        if (!$bHasMoreThanOneSection)
        {
          $bHasMoreThanOneSection = true;
          $strHTMLOptions .= '<TABLE border="0"><TR><TD class="MenuText">'.
                             'Фильтр:</TD></TR><TR><TD><select class="Menu" onchange="document.frmSelect.action=\''.
                             $arrMFiles[$CurrentMenuItem].'?section=\'+ this.options[this.selectedIndex].value">'.
                             '<option value="0">Всё</option>';
        }
        if ($arrItem[1] == $section)
          $strHTMLOptions .= '<option selected value="'.$arrItem[1].'">'.$arrItem[2].'</option>';
        else
          $strHTMLOptions .= '<option value="'.$arrItem[1].'">'.$arrItem[2].'</option>';
      }
    }
    if ($bHasMoreThanOneSection)
    {
      $strHTMLOptions .= '</select></td></tr><tr><td><FORM name="frmSelect" '.attr('action', $arrMFiles[$CurrentMenuItem].'?section=0').
                         'method="post"><input type="SUBMIT" class="subMenu" value="Вывести"></FORM></td></tr></table>';
      echo $strHTMLOptions;
    }
    fclose($fData);
  }
  // -------------------------------------------------------------
  function DisplayNews($CurrentMenuItem, $arrCat, $arrMFiles)
  {
    $strCatName = $arrCat[$CurrentMenuItem];
    if (!file_exists("data/$strCatName.dat")) {
      // TODO: warning??
      return;
    }
    $fData = fopen("data/$strCatName.dat", "r");

    $bAnyNews = FALSE;
    $strHTMLOut = '<TABLE width="100%">';

    while (!feof($fData))
    {
      $strItemData = trim(fgets($fData));
      $arrItem = explode("|", $strItemData);
      if (count($arrItem) < 4) continue;
      // Here we analyze only sections
      if ($arrItem[3] == '.newsblock.')
      {
        $strHTMLOut .= '<TR><TD class="News"><b>'.$arrItem[1].'</b> &#8211; '.$arrItem[2].'</TD></TR><TR><TD height="5px"></TD></TR>';
        $bAnyNews = TRUE;
      }
    }
    $strHTMLOut .= '</TABLE>';
    if ($bAnyNews)
    {
      $strHTMLOut = 
         '<TABLE bgColor="#00458a" border="0" width="100%">
            <TR>
              <TD class="TopTbl">Новости</TD>
            </TR>
          </TABLE>'.$strHTMLOut;
      echo $strHTMLOut;
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
