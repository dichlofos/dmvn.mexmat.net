<?php
  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);
    
  include "common.php";
  if ($bDebugEnabled) error_reporting(E_ALL);

  $lblHeader = 'DMVN WebSite Control Panel';

  $lblLoginAsAdmin = "Log In as Admin";

  $lblPassword = 'Password:';

  $CurrentMenuItem = $mnuCPL;

  // To redir or not to redir, the question is...
  $bRedir = FALSE;

  session_start();

  $section = ProcessStringPostVar('section', '0'); 

  $strAction = ProcessStringPostVar('strAction');
  $strCodepage = ProcessStringPostVar('strCodepage', $cpWin);

  $txtPass = ProcessStringPostVar('txtPass');
  $txtAddMail = ProcessStringPostVar('txtAddMail');
  $txtRemovedMail = ProcessStringPostVar('txtRemovedMail');
  $txtGrepFilter = ProcessStringPostVar('txtGrepFilter');
  $txtNCGrepFilter = ProcessStringPostVar('txtNCGrepFilter');
  $txtLogGrepFilter = ProcessStringPostVar('txtLogGrepFilter');
  $txtSearchLogGrepFilter = ProcessStringPostVar('txtSearchLogGrepFilter');
    
  // Registration
  if ($strAction == 'login')
  {
    if ($strAdminPassword == md5($txtPass))
    {
      header("Location: $PHP_SELF");
      if (session_is_registered($strSNUserRights)) session_unregister($strSNUserRights);
      session_register($strSNAdminRights);
    }
    else
    {
      header("Location: $PHP_SELF");
      exit();
    }
  }
  elseif ($strAction == 'logout')
  {
    session_destroy();
    header("Location: $PHP_SELF");
    exit();
  }
    
  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
?>
<TABLE width="95%" align="center">
  <TR>
    <TD>
<?php
  echol('<center>');
  if (!session_is_registered($strSNAdminRights))
  {
    echol(hoForm('frmLogin', "$PHP_SELF?strAction=login"));
    echol(hTable(hRow(
     hCell($lblPassword, 'InFormB').
     hCell(hInput('txtPass', 'password')).
     hCell(hInput('subAdminLogin', 'submit', 'subSubmit', $lblLoginAsAdmin))
    )));
    echol(hcForm());
    echol(hScript("document.frmLogin.txtPass.focus();"));
  }
  if (session_is_registered($strSNAdminRights))
  {
    echol('<table>');
    // Add subscribe
    echol(hoForm('frmAdd', "$PHP_SELF?strAction=addsubscribe"));
    echol(hRow(
      hCell('E-Mail:', 'InFormB').
      hCell(hInput('txtAddMail', 'text')).
      hCell(hSelect('selAddMailCP',
        "document.frmAdd.action='$PHP_SELF?strAction=addsubscribe&strCodepage='+this.options[this.selectedIndex].value",
        'Codepage',
        strGetCodepageOptions($arrCodepageNames))).
        hCell(hInput('subAddSubscribe', 'submit', 'subSubmit', 'Add Subscription'))
     ));
    echol(hcForm());
    // Remove subscribe
    echol(hoForm('frmRemove', "$PHP_SELF?strAction=removesubscribe"));
    echol(hRow(
      hCell('E-Mail:', 'InFormB').
      hCell(hInput('txtRemovedMail', 'text')).
      hCell(hSelect('selRemoveMailCP',
        "document.frmRemove.action='$PHP_SELF?strAction=removesubscribe&strCodepage='+this.options[this.selectedIndex].value",
        'Codepage',
        strGetCodepageOptions($arrCodepageNames))).
      hCell(hInput('subRemoveSubscribe', 'submit', 'subSubmit', 'Remove Subscription'))
     ));
    echol(hcForm());
    // View subscribe
    echol(hoForm('frmViewSubscribe', "$PHP_SELF?strAction=viewsubscribe"));
    echol(hRow(
      hCell('Filter:', 'InFormB').
      hCell(hInput('txtGrepFilter', 'text'), '', '', '', attr('colspan', '2')).
      hCell(hInput('subViewSubscribe', 'submit', 'subSubmit', 'View Subscriptions'))
     ));
    echol(hcForm());
    // View nc-subscribe
    echol(hoForm('frmViewNCSubscribe', "$PHP_SELF?strAction=viewncsubscribe"));
    echol(hRow(
      hCell('Filter:', 'InFormB').
      hCell(hInput('txtNCGrepFilter', 'text'), '', '', '', attr('colspan', '2')).
      hCell(hInput('subViewNCSubscribe', 'submit', 'subSubmit', 'View NC-Subscriptions'))
     ));
    echol(hcForm());
    // View duplicates
    echol(hoForm('frmCheckDupSubscribe', "$PHP_SELF?strAction=checkdupsubscribe"));
    echol(hRow(
      hCell('', '', '', '', attr('colspan', '3')).
      hCell(hInput('subCheckDupSubscribe', 'submit', 'subSubmit', 'Check Duplicate Subscriptions'))
     ));
    echol(hcForm());
    // View log
    echol(hoForm('frmViewLog', "$PHP_SELF?strAction=viewlog"));
    echol(hRow(
      hCell('Filter:', 'InFormB').
      hCell(hInput('txtLogGrepFilter', 'text'), '', '', '', attr('colspan', '2')).
      hCell(hInput('subViewLog', 'submit', 'subSubmit', 'View Server Log'))
     ));
    echol(hcForm());
    // View search log
    echol(hoForm('frmViewSearchLog', "$PHP_SELF?strAction=viewsearchlog"));
    echol(hRow(
      hCell('Filter:', 'InFormB').
      hCell(hInput('txtSearchLogGrepFilter', 'text'), '', '', '', attr('colspan', '2')).
      hCell(hInput('subViewSearchLog', 'submit', 'subSubmit', 'View Search Log'))
     ));
    echol(hcForm());
    // Logoff
    echol(hoForm('frmLogout', "$PHP_SELF?strAction=logout"));
    echol(hRow(
      hCell('', '', '', '', attr('colspan', '3')).
      hCell(hInput('subLogout', 'submit', 'subSubmit', 'Logout'))
     ));
    echol(hcForm());
    echol('</table>');
  }
  echol('</center>');
  // ----------------------------------------------
  // Handle different actions
  // ----------------------------------------------
  if ($strAction == 'removesubscribe' && session_is_registered($strSNAdminRights) && $txtRemovedMail != '')
  {
    $arrCMails = fileCutEOL($strCFileName);
    $fCMails = fopen($strCFileName, "wb");
    // TODO: open failure check
    $strRemMail = str_rot13($txtRemovedMail);
    $nRemovedCount = 0;
    foreach ($arrCMails as $strCMail)
    {
      $arrCMailData = explode('|', $strCMail);
      if (strcasecmp($strRemMail, $arrCMailData[0]) || $strCodepage != $arrCMailData[1])
        WriteLine($fCMails, $strCMail);
      else $nRemovedCount++;
    }
    fclose($fCMails);

    echol(hTable(hRow(hCell("$nRemovedCount record(s) removed successfuly", 'PlainText Info'))));
  }
  elseif ($strAction == 'viewsubscribe' && session_is_registered($strSNAdminRights))
  {
    echol(hPar('DMVN Website Subscription List', 'Subtitle'));
    if ($txtGrepFilter != '')
      echol(hPar('Using filter ['.out($txtGrepFilter).']', 'Subtitle'));

    $fCMails = fopen($strCFileName, 'r');
    // TODO: open failure check

    echol('<table width="100%">');
    $nCount = 0;
    while (!feof($fCMails))
    {
      $strCMail = trim(fgets($fCMails));
      if ($strCMail == '') continue;
      $nCount++;

      $arrCMailData = explode('|', $strCMail);

      $bDisp = false;
      if ($txtGrepFilter == '') $bDisp = true;
      else if (stristr(str_rot13($arrCMailData[0]), $txtGrepFilter)) $bDisp = true;
      if ($bDisp)
        echol(hRow(hCell($nCount, 'PlainText', '50px').
             hCell(strDecodeCodepage($arrCodepageNames, $arrCMailData[1]), 'PlainText', '120px').
             hCell(out(str_rot13($arrCMailData[0])), 'PlainText')));
    }
    echo '</table>';
    fclose($fCMails);
  }
  elseif ($strAction == 'viewncsubscribe' && session_is_registered($strSNAdminRights))
  {
    echol(hPar('DMVN Website NC-Subscription List', 'Subtitle'));
    if ($txtNCGrepFilter != '')
      echol(hPar('Using filter ['.out($txtNCGrepFilter).']', 'Subtitle'));

    $fNCMails = fopen($strNCFileName, 'r');
    // TODO: open failure check

    echol('<table width="100%">');
    $nCount = 0;

    while (!feof($fNCMails))
    {
      $strNCMail = trim(fgets($fNCMails));
      if ($strNCMail == '') continue;
      $nCount++;

      $arrNCMailData = explode('|', $strNCMail);

      $bDisp = false;
      if ($txtNCGrepFilter == '') $bDisp = true;
      else if (stristr(str_rot13($arrNCMailData[1]), $txtNCGrepFilter)) $bDisp = true;
      if ($bDisp)
        echol(hRow(hCell($nCount, 'PlainText', '50px').
                   hCell(strDecodeCodepage($arrCodepageNames, $arrNCMailData[3]), 'PlainText', '120px').
                   hCell(out(round((time()-$arrNCMailData[2])/3600)).' hrs idle', 'PlainText', '150px').
                   hCell(out(str_rot13($arrNCMailData[1])), 'PlainText')));
    }
    if (!$nCount) echol(hRow(hCell('No records!', 'PlainText Info')));
    echol('</table>');
    fclose($fNCMails);
  }
  elseif ($strAction == 'viewlog' && session_is_registered($strSNAdminRights))
  {
    echol(hPar('DMVN Website Access Log', 'Subtitle'));
    if ($txtLogGrepFilter != '')
      echol(hPar('Using filter ['.out($txtLogGrepFilter).']', 'Subtitle'));

    $fLog = fopen($strLogFileName, 'r');
    // TODO: open failure check
    echol('<table width="100%">');
    $arrGrep = explode("|", $txtLogGrepFilter);
    while (!feof($fLog))
    {
      $strLine = fgets($fLog);
      if (!stristr($strLine, "content/") & !stristr($strLine, "tmp/")) continue;
      $bDisp = true;
      foreach ($arrGrep as $strGrep)
      {
        if ($strGrep == '') continue;
        if (!stristr($strLine, $strGrep)) $bDisp = false;
      }
      if ($bDisp) echol(hRow(hCell(out($strLine), 'PlainText')));
    }
    echol('</table>');
    fclose($fLog);
  }
  elseif ($strAction == 'viewsearchlog' && session_is_registered($strSNAdminRights))
  {
    echol(hPar('DMVN Website Search Log', 'Subtitle'));
    if ($txtSearchLogGrepFilter != '')
      echol(hPar('Using filter ['.out($txtSearchLogGrepFilter).']', 'Subtitle'));

    $fLog = fopen($strSearchLogFileName, 'r');
    // TODO: open failure check
    echol('<table width="100%">');
    $arrGrep = explode('|', $txtSearchLogGrepFilter);
    while (!feof($fLog))
    {
      $strLine = trim(fgets($fLog));
      if ($strLine == '') continue;
      $arrLine = explode('|', $strLine);
      if (count($arrLine) != 2)
        echol(hRow(hCell('Bad Line in Search Log: '.out($strLine), 'PlainText Info')));

      $strSLine = $arrLine[1];
      $bDisp = true;
      foreach ($arrGrep as $strGrep)
      {
        if ($strGrep == '') continue;
        if (!stristr($strSLine, $strGrep)) $bDisp = false;
      }
      if ($bDisp) echol(hRow(hCell($arrLine[0].': '.hBold(out($strSLine)), 'PlainText')));
    }
    echol('</table>');
    fclose($fLog);
  }
  elseif ($strAction == 'addsubscribe' && session_is_registered($strSNAdminRights) && $txtAddMail != '')
  {
    $fCMails = fopen($strCFileName, "ab");
    // TODO: open failure check
    fwrite($fCMails, str_rot13($txtAddMail)."|$strCodepage\r\n");
    fclose($fCMails);
    echol(hTable(hRow(hCell('Mail address was successfuly added to list', 'PlainText Info'))));
  }
  elseif ($strAction == 'checkdupsubscribe' && session_is_registered($strSNAdminRights))
  {
    echol(hPar('DMVN Website Duplicate Subscriptions', 'Subtitle'));
    $arrCMailsCount = array();
    $fCMails = fopen($strCFileName, 'r');
    while (!feof($fCMails))
    {
      $strCMail = trim(fgets($fCMails));
      if ($strCMail == '') continue;
      if (!isset($arrCMailsCount[$strCMail])) $arrCMailsCount[$strCMail] = 1;
      else $arrCMailsCount[$strCMail]++;
    }
    fclose($fCMails);

    echol('<table>');
    foreach ($arrCMailsCount as $strCMCKey => $strCMCValue)
    {
      if ($strCMCValue > 1)
      {
        $arrMail = explode('|', $strCMCKey);
        echol(hRow(hCell('Mail '.hBold(out(str_rot13($arrMail[0]))).' at codepage '.
         hBold(strDecodeCodepage($arrCodepageNames, $arrMail[1])).' is duplicated '.
         hBold($strCMCValue).' times!', 'PlainText Info')));
      }
    }
    echol('</table>');
  }

  if (session_is_registered($strSNAdminRights))
    echol(hTable(hRow(hCell(hBold(llink("$PHP_SELF?strAction=logout", '[Logout]')), 'ForumC')), attr('width', '100%')));
?>
    </TD>
  </TR>
</TABLE>
<?php
  PutPageFooter($strDMVNMail);
  // Auto redir via jscript
  if ($bRedir) echol('<html>'.hScript("open('$PHP_SELF', '_self');").'</html>');
?>
