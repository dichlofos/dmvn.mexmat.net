<?php

  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);
    
  include "common.php";
  if ($bDebugEnabled) error_reporting(E_ALL);

  $strAction = ProcessStringPostVar('strAction');
  $strMailID = ProcessStringPostVar('strMailID');
  $strCodepage = ProcessStringPostVar('strCodepage', '0');

  $section = ProcessStringPostVar('section', '0');

  $CurrentMenuItem = $mnuIndex;
  $bRedir = false;

  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
  DisplayPage($CurrentMenuItem, $arrCat, $section);

?>
	<table class="Page">
		<tr>
			<td>
				<P class="Subtitle">Рассылка новостей сайта</P>
<?php          
  if ($strAction == 'addmail')
  {
    if (bEMailValid($txtEAddress) && bCodepageValid($arrCodepageNames, $strCodepage))
    {
      $strRandomKey = RandomString(32);
      $nTimeStamp = time();
      // for debugging -----
      echol(hPar(hBold("Key=$strRandomKey ".
        flink("http://localhost/index.php?strAction=confirmmail&strMailID=$strRandomKey",
        "Confirm Last Link. TimeStamp: $nTimeStamp")), 'PlainText Info'));
      // -------------------
      $strHashRandomKey = md5($strRandomKey);

      // Check obsolete registartion records
      $arrNCMails = fileCutEOL($strNCFileName);

      $fNCMailsData = fopen($strNCFileName, "wb");

      foreach ($arrNCMails as $strNCMail)
      {
        $arrNCMailData = explode('|', $strNCMail);
        if ($nTimeStamp < $arrNCMailData[2] + $nTimeShift) WriteLine($fNCMailsData, $strNCMail);
      }
      // Write new item needed in confirmation
      WriteLine($fNCMailsData, "$strHashRandomKey|".str_rot13($txtEAddress)."|$nTimeStamp|$strCodepage");
      fclose($fNCMailsData);
        
      mail($txtEAddress,
           $strMailConfirmSubject,
           "$strMailConfirmText?strAction=confirmmail&strMailID=$strRandomKey", "From: DMVN <$strDMVNMailReal>");
        
      echol(hPar(hBold('На указанный адрес выслано письмо '.
           'с просьбой подтвердить регистрацию в течение '.$nTimeShift/(3600).
           ' часов. '.llink($PHP_SELF, 'Вернуться на главную страничку')), 'PlainText Info'));
    }
    else
    {
      echol(hPar(hBold('Указанный адрес не прошёл проверку! '.
          llink($PHP_SELF, 'Вернуться на главную страничку')), 'PlainText Info'));
    }
  }
  elseif ($strAction == 'confirmmail')
  {
    $strWantedHash = md5($strMailID);
    $arrNCMails = fileCutEOL($strNCFileName);
    for ($i = 0; $i < count($arrNCMails); $i++)
    {
      $arrNCMailData = explode('|', $arrNCMails[$i]);
      if ($arrNCMailData[0] == $strWantedHash)
      {
        $fCMailsData = fopen($strCFileName, "ab");
        WriteLine($fCMailsData, $arrNCMailData[1].'|'.$arrNCMailData[3]);
        fclose($fCMailsData);
          
        $arrNCMails[$i] = ""; // Eliminate this confirmation line

        $fNCMailsData = fopen($strNCFileName, 'wb');
        for ($j = 0; $j < count($arrNCMails); $j++)
          if ($arrNCMails[$j] != "") WriteLine($fNCMailsData, $arrNCMails[$j]);
        fclose($fNCMailsData);

        echol(hPar(hBold('Ваш адрес был успешно добавлен в основной список. '.
             llink($PHP_SELF, 'Вернуться на главную страничку')), 'PlainText Info'));
        break;
      }
    }
  }
  else
  {?>
		<p class="PlainTextFP">
			Если Вы хотите получать по почте уведомления об обновлениях нашего сайта,
       укажите свой e-mail здесь. Вы получите письмо с просьбой подтвердить
       необходимость рассылки. После подтверждения Ваш e-mail будет добавлен
       в список рассылки. Для надёжности применяется шифрование mail-адресов,
       так что будьте уверены, что Ваш e-mail нигде не встретится открытым текстом
       на нашем сайте.</p>
		<p class="PlainTextFP">Если Вы хотите убрать свой адрес из списка рассылки, напишите об этом нам.</p>
	<?php
       echol('<center>'.
       hoForm('frmMail', "$PHP_SELF?strAction=addmail&strCodepage=0").
        hTable(hRow(
         hCell($lblSubmitEMail, 'InForm Bold').
         hCell(hInput('txtEAddress', 'text')).
         hCell($lblEMailCP, 'InForm Bold').
         hCell(hSelect('selMailCP',
          "document.frmMail.action='$PHP_SELF?strAction=addmail&strCodepage='+this.options[this.selectedIndex].value",
          'Codepage',
          strGetCodepageOptions($arrCodepageNames))).
         hCell(hInput('subNewsEMail', 'submit', 'subSubmit', $lblSubmitMail))
         )).
       hcForm().
       '</center>');
  }
	
  echol(hPar('Структура сайта', 'Subtitle'));
  echol('<table border="0" width="100%">');
  for ($i = 0; $i < count($arrMenuDesc); $i++)
    echol(hRow(hCell('&#0149; '.llink($arrMenuFiles[$i], $arrMenuTitles[$i]), 'PlainTextFP', '200px', '', attr('valign', 'top')).
               hCell(' ', 'PlainTextFP', '10px').
               hCell($arrMenuDesc[$i], 'PlainTextFP', '', '', attr('valign', 'top'))));
	echol('</table>');
	?>
			</td>
		</tr>
	</table><!-- page -->
	<?php
	PutPageFooter($strDMVNMail);
	if ($bRedir) echol('<html>'.hScript("open('$PHP_SELF', '_self');").'</html>');
?>