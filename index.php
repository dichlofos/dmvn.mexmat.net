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
  $txtCodepage = ProcessStringPostVar('txtCodepage', $sDefCodepage);

  $section = ProcessStringPostVar('section', '0');

  $CurrentMenuItem = $mnuIndex;
  $bRedir = false;

  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
  DisplayPage($CurrentMenuItem, $arrCat, $section);

?>
	<div class="Page">
		<p class="Subtitle">Рассылка новостей сайта</p>
<?php          
	if ($strAction == 'addmail') {
		if (bEMailValid($txtEAddress) && in_array($txtCodepage, $aCodepages)) {
			$strRandomKey = RandomString(32);
			$nTimeStamp = time();
			// for debugging -----
			/*
			echol(hPar(hBold("Key=$strRandomKey ".
				flink("http://localhost/index.php?strAction=confirmmail&amp;strMailID=$strRandomKey",
				"Confirm Last Link. TimeStamp: $nTimeStamp")), 'PlainText Info'));
			*/
			// -------------------
			$strHashRandomKey = md5($strRandomKey);

			// Check obsolete registartion records
			$arrNCMails = fileCutEOL($strNCFileName);

			$fNCMailsData = fopen($strNCFileName, "wb");

			foreach ($arrNCMails as $strNCMail) {
				$arrNCMailData = explode('|', $strNCMail);
				if ($nTimeStamp < $arrNCMailData[2] + $nTimeShift) WriteLine($fNCMailsData, $strNCMail);
			}
			// Write new item needed in confirmation
			WriteLine($fNCMailsData, "$strHashRandomKey|".str_rot13($txtEAddress)."|$nTimeStamp|$txtCodepage");
			fclose($fNCMailsData);
				
			mail($txtEAddress,
				$strMailConfirmSubject,
				"$strMailConfirmText?strAction=confirmmail&strMailID=$strRandomKey",
				"From: DMVN <$strDMVNMailReal>");
				
			echol(hPar(hBold('На указанный адрес выслано письмо '.
					 'с просьбой подтвердить регистрацию в течение '.($nTimeShift/3600).
					 ' часов. '.llink($PHP_SELF, 'Вернуться на главную страничку')), 'PlainText Info'));
		} else {
			echol(hPar(hBold('Указанный адрес не прошёл проверку! '.
					llink($PHP_SELF, 'Вернуться на главную страничку')), 'PlainText Info'));
		}
	} elseif ($strAction == 'confirmmail') {
		$strWantedHash = md5($strMailID);
		$arrNCMails = fileCutEOL($strNCFileName);
		for ($i = 0; $i < count($arrNCMails); $i++) {
			$arrNCMailData = explode('|', $arrNCMails[$i]);
			if ($arrNCMailData[0] == $strWantedHash) {
				$fCMailsData = fopen($strCFileName, "ab");
				WriteLine($fCMailsData, $arrNCMailData[1].'|'.$arrNCMailData[3]);
				fclose($fCMailsData);
					
				$arrNCMails[$i] = ""; // Eliminate this confirmation line

				$fNCMailsData = fopen($strNCFileName, 'wb');
				foreach ($arrNCMails as $sNCMail) {
					if (!empty($sNCMail)) WriteLine($fNCMailsData, $sNCMail);
				}
				fclose($fNCMailsData);

				echol(hPar(hBold('Ваш адрес был успешно добавлен в основной список. '.
					llink($PHP_SELF, 'Вернуться на главную страничку')), 'PlainText Info'));
				break;
			}
		}
	} else {?>
		<p class="PlainTextFP">
			Если Вы хотите получать по почте уведомления об обновлениях нашего сайта,
			 укажите свой e-mail здесь. Вы получите письмо с просьбой подтвердить
			 необходимость рассылки. После подтверждения Ваш e-mail будет добавлен
			 в список рассылки. Для надёжности применяется шифрование mail-адресов,
			 так что будьте уверены, что Ваш e-mail нигде не встретится открытым текстом
			 на нашем сайте.</p>
		<p class="PlainTextFP">Если Вы хотите убрать свой адрес из списка рассылки, напишите об этом нам.</p>
		<form action="index.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="addmail" />
				<span class="Label">EMail:</span>
				<input type="text" name="txtEAddress" />
				<span class="Label">Кодировка:</span>
				<select name="txtCodepage" class="Codepage">
					<?php echo GetCodepageOptions() ?>
				</select>
				<input type="submit" class="submit" value="Подписаться!" />
			</div>
		</form>
		<?php
	}?>
		<p class="Subtitle">Структура сайта</p>
		<div><!-- SiteMap -->
	<?php
	for ($i = 0; $i < count($arrMenuDesc); $i++) {
		echo '<div class="SiteMapItem">';
		echo '<span class="SiteMapLeft">&#x2022; '.llink($arrMenuFiles[$i], $arrMenuTitles[$i]).'</span>';
		echo '<span class="SiteMapRight">'.$arrMenuDesc[$i].'</span>';
		echo "</div>\r\n";
	}
	?>
		</div><!-- SiteMap -->
	</div><!-- Custom Page -->
	<?php
	PutPageFooter($strDMVNMail);
	if ($bRedir) echol('<html>'.hScript("open('$PHP_SELF', '_self');").'</html>');
?>