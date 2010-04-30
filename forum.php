<?php
  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);

  include "common.php";
  if ($bDebugEnabled) error_reporting(E_ALL);

  $lblPage = 'Страница';
  $lblAddComment = 'Отправить';
  $lblName = 'Имя<span style="color:#dddd77;">*</span>:';
  $lblHomePage = 'Сайт:';
  $lblEMail = 'Почта:';
  $lblComment = 'Сообщение<span style="color:#dddd77;">*</span>:';
  $lblTheme = 'Тема:';
  $lblDate = 'Дата:';
  $lblTime = 'Время:';
  $lblLogin = 'Войти';
  $lblAdminComment = "Комментарий:";
  $lblDate = "Дата:";
  $lblIP = "IP:";
  $lblUserAgent = "User Agent:";
  $lblFlag = 'Flag:';

//  $lblBadName = 'Имя не введено';
  $lblReservedName = 'Имя <i>Admin</i> может использовать только администратор';
//  $lblBadComment = 'Сообщение не введено';

  // Comments per page
  $nCommentsPerPage = 10;

  // Administrative functions
  $lblPassword = 'Пароль:';

  $strMailTo = "DMVN <$strDMVNMailReal>";
  $strMailSubject = 'Forum message from ';

  $arrSmiles = array('grin', 'rolleyes', 'sad', 'shocked', 'smile', 'tongue', 'undecided', 'wink');
    
  $strDefaultAdminName = "Admin";
  $strDefaultAdminHomePage = "http://dmvn.mexmat.net";
  $strDefaultAdminEMail = $strDMVNMailReal;
  $strReservedName = $strDefaultAdminName;
   
  $CurrentMenuItem = $mnuForum;

  $bRedir = false;

  // Processing all HTTP POST variables
  $section = ProcessStringPostVar('section', '0');
  $strAction = ProcessStringPostVar('strAction', 'post');
  $strPostID = ProcessStringPostVar('strPostID');
  $strUpdate = ProcessStringPostVar('strUpdate', 'no');
  $strDPostID = ProcessStringPostVar('strDPostID');

  $txtDate = ProcessStringPostVar('txtDate');
  $txtTime = ProcessStringPostVar('txtTime');
  $txtName = ProcessStringPostVar('txtName');
  $txtTheme = ProcessStringPostVar('txtTheme');
  $txtHomePage = ProcessStringPostVar('txtHomePage');
  $txtEMail = ProcessStringPostVar('txtEMail');
  $txtComment = ProcessStringPostVar('txtComment');
  $txtAdminComment = ProcessStringPostVar('txtAdminComment');
  $txtIP = ProcessStringPostVar('txtIP');
  $txtUserAgent = ProcessStringPostVar('txtUserAgent');
  $txtFlag = ProcessStringPostVar('txtFlag');

  $txtDate = FilterLowASCII($txtDate);
  $txtTime = FilterLowASCII($txtTime);
  $txtName = FilterLowASCII($txtName);
  $txtTheme = FilterLowASCII($txtTheme);
  $txtHomePage = FilterLowASCII($txtHomePage);
  $txtEMail = FilterLowASCII($txtEMail);
  $txtIP = FilterLowASCII($txtIP);
  $txtUserAgent = FilterLowASCII($txtUserAgent);
  $txtFlag = FilterLowASCII($txtFlag);

  $bPostInvalid = false;

  srand();
  session_start();
  
  $bAuth = session_is_registered($strSNUserRights) || session_is_registered($strSNAdminRights);
  $bAdmin = session_is_registered($strSNAdminRights);
  
  if ($strAction == 'login')
  {
    // Unregister old sessions
    if (session_is_registered($strSNAdminRights)) session_unregister($strSNAdminRights);
    if (session_is_registered($strSNUserRights)) session_unregister($strSNUserRights);
    
    // Check passwords
    if ($strAdminPassword == md5($txtPass))
    {
      session_register($strSNAdminRights);
      header("Location: $PHP_SELF");
      exit();
    }
    elseif ($strUserPassword == md5($txtPass))
    {
      session_register($strSNUserRights);
      header("Location: $PHP_SELF");
      exit();
    }
    else
    {
      header("Location: $PHP_SELF");
      exit();
    }
  }
  elseif ($strAction == 'logout')
  {
    if (session_is_registered($strSNAdminRights)) session_unregister($strSNAdminRights);
    if (session_is_registered($strSNUserRights)) session_unregister($strSNUserRights);
    session_destroy();
    header("Location: $PHP_SELF");
    exit();
  }
  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
?>
<TABLE width="95%" align="center">
  <TR>
    <TD>
      <?php DisplayPage($CurrentMenuItem, $arrCat, $section); ?>
    </TD>
  </TR>
  <TR>
    <TD>
<?php
  if ($strAction == 'post')
  {
    echol('<center>');
    // If user is not registered, show authorization form
    if (!$bAuth)
    {
      echol(
        hoForm('frmLogin', "$PHP_SELF?strAction=login").
        hTable(hRow(
          hCell($lblPassword, 'InForm Bold').
          hCell(hInput('txtPass', 'password')).
          hCell(hInput('subLogin', 'submit', 'subSubmit', $lblLogin))
        )).
        hcForm());
    }
    else
    {
      $valDate = '';
      $valTime = '';
      $valName = '';
      $valTheme = '';
      $valHomePage = '';
      $valEMail = '';
      $valComment = '';
      $valAdminComment = '';
      $valIP = '';
      $valUserAgent = '';
      $valFlag = '';

      $bProcess = false;
      // Analyze parameters
      if ($strPostID == "")
      {
        // This is a new post
        if ($bAdmin)
        {
          // Admin can omit some values, he's mighty :)
          if ($txtName == '') $txtName = $strDefaultAdminName;
          if ($txtHomePage == '') $txtHomePage = $strDefaultAdminHomePage;
          if ($txtEMail == '') $txtEMail = $strDefaultAdminEMail;
        }
        $valName = $txtName;
        $valTheme = $txtTheme;
        $valHomePage = $txtHomePage;
        $valEMail = $txtEMail;
        $valComment = $txtComment;
        $valAdminComment = $txtAdminComment;
        $bProcess = true;
      }
      else
      {
        // We should edit the post (godmode only)
        // Get post $strPostID from file
        if ($bAdmin)
        {
          if ($strUpdate != 'yes')
          {
            $strUpdate = 'yes';
            $fForum = fopen($strForumFileName, 'r');
            if (!$fForum) FDeath('Cannot open forum file!');
            while (!feof($fForum))
            {
              $strFL = trim(fgets($fForum));
              if ($strFL == '') continue;
              $arrFL = explode('|', $strFL);
              if ($arrFL[0] == $strPostID)
              {
                $valDate = $arrFL[1];
                $valTime = $arrFL[2];
                $valName = StoreToInput($arrFL[3]);
                $valTheme = StoreToInput($arrFL[4]);
                $valHomePage = StoreToInput($arrFL[5]);
                $valEMail = StoreToInput($arrFL[6]);
                $valComment = StoreToInput($arrFL[7]);
                $valAdminComment = StoreToInput($arrFL[8]);
                $valIP = $arrFL[9];
                $valUserAgent = $arrFL[10];
                $valFlag = $arrFL[11];

                $txtDate = $valDate;
                $txtTime = $valTime;
                $txtName = $valName;
                $txtTheme = $valTheme;
                $txtHomePage = $valHomePage;
                $txtEmail = $valEMail;
                $txtComment = $valComment;
                $txtAdminComment = $valAdminComment;
                $txtIP = $valIP;
                $txtUserAgent = $valUserAgent;
                $txtFlag = $valFlag;

                $bProcess = true;
                break;
              }
            }
            fclose($fForum);
            // Invalidate post
            $bPostInvalid = true;

            if (!$bProcess) $bRedir = true;
          }
          else // $strUpdate: 'yes'
          {
            // we should update post info from $txt-Variables
            // $strPostID is set
            $valDate = $txtDate;
            $valTime = $txtTime;
            $valName = $txtName;
            $valTheme = $txtTheme;
            $valHomePage = $txtHomePage;
            $valEMail = $txtEMail;
            $valComment = $txtComment;
            $valAdminComment = $txtAdminComment;
            $valIP = $txtIP;
            $valUserAgent = $txtUserAgent;
            $valFlag = $txtFlag;

            $bProcess = true;
          } // $strUpdate check
        } // $bAdmin
      } // $strPostID check

      if ($bProcess)
      {
        // -----------------------------------
        // Here follows check if we should post 
        $strError = '';
        if ($txtName == '') $bPostInvalid = true;
        if ($txtName == $strReservedName && !$bAdmin)
        { $strError .= "$lblReservedName. "; $bPostInvalid = true; }
        if (strlen($txtComment) < 5) { $bPostInvalid = true; }
        // !!! Here we can add some checking if $txtFlag is empty
        // -----------------------------------
        // Display form and data (if necessary)
        if ($bPostInvalid)
        {
          $strFormSubmitAction = "$PHP_SELF?strAction=post&strPostID=$strPostID&strUpdate=$strUpdate&strDPostID=$strDPostID";
          echol('<table><tr>');
          foreach ($arrSmiles as $strSmile)
            echol(hCell(hImg("images/smiles/$strSmile.gif", "\$$strSmile\$", '', '', '',
                  attr('onclick', "InsertText(document.frmPost.txtComment, '\$$strSmile\$')")), '', '30', '30'));
          echol('</tr></table>');
          echol(hoForm('frmPost', $strFormSubmitAction));
          echol('<table border=0 cellspacing=0>');
/*        echol(hRow(hCell('PostID:', 'InForm Labels').
                hCell(":|".out($strPostID)."|:", 'InForm Bold')));
          echol(hRow(hCell('Action:', 'InForm Labels').
                hCell(out($strFormSubmitAction), 'InForm Bold'))); */
          if ($bAdmin && $strPostID != '')
          {
            // Date
            echol(hRow(hCell($lblDate, 'InForm Labels').
                  hCell(hInput('txtDate', 'text', 'txtForum', $valDate))));
            // Time
            echol(hRow(hCell($lblTime, 'InForm Labels').
                  hCell(hInput('txtTime', 'text', 'txtForum', $valTime))));
          }
          // Name
          echol(hRow(hCell($lblName, 'InForm Labels').
                hCell(hInput('txtName', 'text', 'txtForum', $valName))));
          // Theme
          echol(hRow(hCell($lblTheme, 'InForm Labels').
                hCell(hInput('txtTheme', 'text', 'txtForum', $valTheme))));
          // HomePage
          echol(hRow(hCell($lblHomePage, 'InForm Labels').
                hCell(hInput('txtHomePage', 'text', 'txtForum', $valHomePage))));
          // EMail
          echol(hRow(hCell($lblEMail, 'InForm Labels').
                hCell(hInput('txtEMail', 'text', 'txtForum', $valEMail))));
          if ($bAdmin && $strPostID != '')
          {
            // IP
            echol(hRow(hCell($lblIP, 'InForm Labels').
                  hCell(hInput('txtIP', 'text', 'txtForum', $valIP))));
            // UserAgent
            echol(hRow(hCell($lblUserAgent, 'InForm Labels').
                  hCell(hInput('txtUserAgent', 'text', 'txtForum', $valUserAgent))));
            // Flag
            echol(hRow(hCell($lblFlag, 'InForm Labels').
                  hCell(hInput('txtFlag', 'text', 'txtForum', $valFlag))));
          }
          // Comment
          echol(hRow(hCell($lblComment, 'InForm Labels').hCell('')));
          echol(hRow(hCell(hTextarea('txtComment', '', out($valComment),
                            attr('onselect', "StoreCaret(this);").
                            attr('onclick', "StoreCaret(this);").
                            attr('onkeyup', "StoreCaret(this);")), '', '', '', attr('colspan', '3'))));
          if ($bAdmin && $strPostID != '')
          {
            // AdminComment
            echol(hRow(hCell($lblAdminComment, 'InForm Labels').hCell('')));
            echol(hRow(hCell(hTextarea('txtAdminComment', '', out($valAdminComment)), '', '', '', attr('colspan', '3'))));
          }
          if ($strError != '')
            echol(hRow(hCell($strError, 'InForm Bold Red', '', '', attr('colspan', '3'))));
          // --- SUBMIT
          echol(hRow(hCell(hInput('subUserPost', 'submit', 'subSubmit', $lblAddComment), '', '', '', attr('colspan', '3'))));
          echol('</table>');
          echol(hcForm());
          echol(hScript('document.frmPost.txtName.focus();'));
        }
        else // $bPostInvalid = false
        {
          if ($strPostID == '')
          {
            $strPostID = RandomString(32); // we believe, they won't repeat!
            $txtDate = date("d.m.y", time()+0);
            $txtTime = date("H:i:s", time()+0);
            $txtIP = $REMOTE_ADDR;
            $txtUserAgent = $HTTP_USER_AGENT;
            $txtFlag = 'user';
            if ($bAdmin) $txtFlag = 'admin';
            //------------------------------------------------
            // First, send mail if necessary
            if ($txtEMail == '') $txtEMail = "forumuser@dmvn.mexmat.net";
            if ($txtHomePage == '') $txtHomePage = "http://localhost";
            $strHeaders = "Content-Type: text/plain; charset=windows-1251\n";
            mail($strMailTo, $strMailSubject.$txtName,
              "Name:     $txtName\n".
              "Theme:    $txtTheme\n".
              "HomePage: $txtHomePage\n".
              "E-Mail:   $txtEMail\n".
              "Comment:  $txtComment\n",
              "From: $txtName <$txtEMail>\n".
              "Reply-To: $txtName <$txtEMail>\n".
              $strHeaders);
            // Then we should send subscriptions
            if ($bAdmin && $txtName == $strReservedName)
            {
              echol('<table>');
              echol(hRow(hCell('Sending News&Information to:', 'PlainText Info')));
              $arrEMails = fileCutEOL($strCFileName);
              foreach ($arrEMails as $strEMail)
              {
                $arrCMailData = explode('|', $strEMail);
                $strSubscrEMail = str_rot13($arrCMailData[0]);
                if ($strSubscrEMail != '')
                {
                  $strHeaders = "Content-Type: text/plain; charset={$arrCodepageNames[$arrCMailData[1]]}\n";
                  mail($strSubscrEMail,
                    $strMailSubscriptionSubject,
                    strRecodeToCodepage($txtComment, $arrCodepageNames, $arrCMailData[1]),
                    "From: DMVN <$strDMVNMailReal>\n".
                    "Reply-To: DMVN <$strDMVNMailReal>\n".
                    $strHeaders);
                  echol(hRow(hCell("Mail sent to $strSubscrEMail", 'PlainText')));
                }
              }
              echo '</table>';
            }
            // Writing to forum file
            // common
            $txtName = InputToStore($txtName);
            $txtTheme = InputToStore($txtTheme);
            $txtHomePage = InputToStore($txtHomePage);
            $txtEMail = InputToStore($txtEMail);
            $txtComment = InputToStore($txtComment);
            $txtAdminComment = InputToStore($txtAdminComment);        
            $strFullPost = "$strPostID|$txtDate|$txtTime|$txtName|$txtTheme|$txtHomePage|$txtEMail|$txtComment|$txtAdminComment|$txtIP|$txtUserAgent|$txtFlag";
            // end of common
            $fForum = fopen($strForumFileName, 'r');
            $strFContents = fread($fForum, filesize($strForumFileName));
            fclose($fForum);
            $fForum = fopen($strForumFileName, 'w');
            if (!$fForum) FDeath('DEBUG: Cannot open file for writing!');
            WriteLine($fForum, $strFullPost);
            fwrite($fForum, $strFContents);
            fclose($fForum);
          }
          else
          {
            // common
            $txtName = InputToStore($txtName);
            $txtTheme = InputToStore($txtTheme);
            $txtHomePage = InputToStore($txtHomePage);
            $txtEMail = InputToStore($txtEMail);
            $txtComment = InputToStore($txtComment);
            $txtAdminComment = InputToStore($txtAdminComment);        
            $strFullPost = "$strPostID|$txtDate|$txtTime|$txtName|$txtTheme|$txtHomePage|$txtEMail|$txtComment|$txtAdminComment|$txtIP|$txtUserAgent|$txtFlag";
            // end of common
            $arrNewFData = array();
            $fForum = fopen($strForumFileName, 'r');
            if (!$fForum) FDeath('DEBUG: forum.update(): cannot open file for reading!');
            while (!feof($fForum))
            {
              $strFL = trim(fgets($fForum));
              if ($strFL == '') continue;
              $arrFL = explode('|', $strFL);
              if ($arrFL[0] != $strPostID) $arrNewFData[] = $strFL;
              else $arrNewFData[] = $strFullPost;
            }
            fclose($fForum);
            $fForum = fopen($strForumFileName, 'w');
            foreach ($arrNewFData as $strFL)
              WriteLine($fForum, $strFL);
            fclose($fForum);
          }
          $bRedir = true;
        } // $bPostInvalid
      } // $bProcess
    } // $bAuth
    echo '</center>';
  }
  elseif ($strAction == 'delete' && $bAdmin)
  {
    $arrNewFData = array();
    $fForum = fopen($strForumFileName, 'r');
    if (!$fForum) FDeath('DEBUG: forum.delete(): cannot open file for reading!');
    while (!feof($fForum))
    {
      $strFL = trim(fgets($fForum));
      if ($strFL == '') continue;
      $arrFL = explode('|', $strFL);
      if ($arrFL[0] != $strPostID) $arrNewFData[] = $strFL;
    }
    fclose($fForum);
    $fForum = fopen($strForumFileName, 'w');
    foreach ($arrNewFData as $strFL)
      WriteLine($fForum, $strFL);
    fclose($fForum);
    $bRedir = true;
  }
  // ---------------------- display
  if (!$bRedir)
  {
    echol('<table width="100%">');
    if ($bAuth)
      echol(hRow(hCell(llink("$PHP_SELF?strAction=logout", '[Выйти]'), 'ForumC')));
    echol('</table>');

    $fForum = fopen($strForumFileName, 'r');
    if (!$fForum) FDeath('DEBUG: forum.display(): Cannot open forum file!');

    $strForumOut = '';
    $strForumOut .= '<table valign="top" bgcolor="#063562" width="100%"><tr><td bgcolor="00254a"><table width="100%">';
    $bStartDisplay = false;
    if ($strDPostID == '') $bStartDisplay = true;
    $nDisplayedCount = 0;
    $nPN = 0;
    $strPageLinks = '';
    $strPostIDs = array();
    $strPage = '';
    while (!feof($fForum))
    {
      $strFL = trim(fgets($fForum));
      if ($strFL == '') continue;
      $arrFL = explode('|', $strFL);
      if ($arrFL[0] == $strDPostID)
      {
        $bStartDisplay = true;
        $strPage = floor($nPN / $nCommentsPerPage);
      }
      if ($nPN % $nCommentsPerPage == 0) $strPostIDs[$nPN / $nCommentsPerPage] = $arrFL[0];
      if ($bStartDisplay && $nDisplayedCount < $nCommentsPerPage)
      {
        $valPostID = $arrFL[0];
        $valDate = $arrFL[1];
        $valTime = $arrFL[2];
        $valName = StoreToInput($arrFL[3]);
        $valTheme = StoreToInput($arrFL[4]);
        $valHomePage = StoreToInput($arrFL[5]);
        $valEMail = StoreToInput($arrFL[6]);
        $valComment = StoreToInput($arrFL[7]);
        $valAdminComment = StoreToInput($arrFL[8]);
        $valIP = $arrFL[9];
        $valUserAgent = $arrFL[10];
        $valFlag = $arrFL[11];

        $dispEMail = $bAuth ? $valEMail : 'please@uthorize.yourself';
        $dispIP = $bAuth ? $valIP : 'IP Saved';
        $dispUserAgent = $bAuth ? $valUserAgent : 'User Agent Saved';

        $strUserFunctions = ' '.hHref("$PHP_SELF?strDPostID=$valPostID", '[Link]');

        $strAdminFunctions = '';
        if ($bAdmin) $strAdminFunctions .= ' '.hHref("$PHP_SELF?strAction=post&strPostID=$valPostID", '[Edit]');
        if ($bAdmin) $strAdminFunctions .= ' '.hHref("$PHP_SELF?strAction=delete&strPostID=$valPostID", '[Erase]');

        // Display post
        $strForumOut .= hRow(hCell(
          hHref('mailto:'.FilterDQuotes($dispEMail), hImg('/images/icons/em.gif', '', 'MessageIcon', '', '', attr('title', out($dispEMail)))).
          hHref(FilterDQuotes($valHomePage), hImg('/images/icons/hm.gif', '', 'MessageIcon')).
          hImg('/images/icons/ip.gif', '', 'MessageIcon', '', '', attr('title', out($dispIP))).
          hImg('/images/icons/br.gif', '', 'MessageIcon', '', '', attr('title', out($dispUserAgent))).
          "$strUserFunctions $strAdminFunctions ".out($valDate).' '.out($valTime).'  <b>'.out($valName).'</b>: '.out($valTheme), 'ForumF'));
        
        $strCommentStyle = ($valFlag == 'admin') ? 'PlainText Admin' : 'PlainText';
        $strForumOut .= hRow(hCell(FormatPost(out($valComment)), $strCommentStyle));
        if ($valAdminComment != '')
        {
          $strForumOut .= hRow(hCell("<b>$lblAdminComment</b>", 'PlainText AdminComment'));
          $strForumOut .= hRow(hCell(FormatPost(out($valAdminComment)), 'PlainText AdminComment'));
        }
        $strForumOut .= '<tr height="12px"></tr>';
        $nDisplayedCount++;
      }
      $nPN++;
    }
    fclose($fForum);

    for ($nPageN = 0; $nPageN < ceil($nPN/$nCommentsPerPage); $nPageN++)
      if ($strPage == $nPageN)
        $strPageLinks .= hHref("$PHP_SELF?strDPostID={$strPostIDs[$nPageN]}", "<b>[$nPageN]</b>").' ';
      else $strPageLinks .= hHref("$PHP_SELF?strDPostID={$strPostIDs[$nPageN]}", "[$nPageN]").' ';

    // ----------------------
    $strForumOut .= '</table></td></tr></table>';

    echol(hTable(hRow(hCell($strPageLinks, 'PlainText'))));
    echol($strForumOut);
    echol(hTable(hRow(hCell($strPageLinks, 'PlainText'))));
  }
?>

    </TD>
  </TR>
</TABLE>
<?php
  PutPageFooter($strDMVNMail);
  // Auto redir via jscript
  if ($bRedir) echo('<html>'.hScript("open('$PHP_SELF', '_self');").'</html>');
  // Manual redir via jscript
  // if ($bRedir) echo('<html>'.llink("$PHP_SELF?strDPostID=$strDPostID", 'Do Redirect Now!').'</html>');

  // --------------------------------------------------------------------------------
  // Converts string from user input to our internal format for storing data
  function FDeath($strMsg)
  {
    echol(hPar("DEBUG: $strMsg", 'PlainText Info'));
    die();
  }
  // -------------------------------------------------------------
  // Reverses the actions of the previous function
  function FormatPost($strPost)
  {
    global $arrSmiles;
    foreach ($arrSmiles as $strSmile)
      $strPost = str_replace("\$$strSmile\$", hImg("/images/smiles/$strSmile.gif", $strSmile), $strPost);
    $strPost = str_replace("\r\n", "<br>", $strPost);
    $strPost = TraceURL($strPost);
    return $strPost;
  }

  // -------------------------------------------------------------
  function bURLSymbol($strSym)
  {
    return ereg("[a-zA-Z0-9/\!\+\&\#\=\%\:\?\.\_\-]", $strSym);
  }

  function TraceURL($strS)
  {
    // We got htmlspecialchar-ed string, so we must decode it slightly
    $cstrHTTP = 'http://';
    $nPos = 0;
    $strNewS = '';
    $strS = str_replace('&amp;', '&MyAmp.', $strS);
    while (true)
    {
      $nPos = strpos($strS, $cstrHTTP);
      if ($nPos === false) break;
      $nCPos = $nPos + strlen($cstrHTTP);
      while ($nCPos < strlen($strS))
        if (bURLSymbol($strS[$nCPos])) $nCPos++; else break;
      $strNewS .= str_replace('&MyAmp.', '&amp;', substr($strS, 0, $nPos));
      $strHLink = substr($strS, $nPos, $nCPos-$nPos);
      // there are no spaces in real HLink, so we use ' ' to avoid
      // prefix replacement
      $strRHLink = str_replace('&MyAmp.', '& ', $strHLink);
      $strRHLink = str_replace(' ', '', $strRHLink);
      if ($strRHLink[strlen($strRHLink)-1] == '.') $strRHLink = substr($strRHLink, 0, strlen($strRHLink)-1);
      // Visible link
      $strVHLink = str_replace('&MyAmp.', '&amp;', $strHLink);
      // Form hyperlink
      $strHLink = flink($strRHLink, $strVHLink);
      $strNewS .= $strHLink;
      $strS = substr($strS, $nCPos);
    }
    return $strNewS.str_replace('&MyAmp.', '&amp;', $strS);
  }
?>
