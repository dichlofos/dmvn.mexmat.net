<?php
	extract($_SERVER);
	extract($_ENV);
	extract($_GET);
	extract($_POST);
	extract($_REQUEST);

	include "common.php";
	if ($bDebugEnabled) error_reporting(E_ALL);
	$CurrentMenuItem=$mnuCPL;

	// To redir or not to redir, the question is...
	$bRedir=false;

	$section=ProcessStringPostVar('section', '0');

	$strAction=ProcessStringPostVar('strAction');

	$txtCodepage=ProcessStringPostVar('txtCodepage', $sDefCodepage);
	$txtPass=ProcessStringPostVar('txtPass');
	$txtAddMail=ProcessStringPostVar('txtAddMail');
	$txtRemovedMail=ProcessStringPostVar('txtRemovedMail');
	$txtGrepFilter=ProcessStringPostVar('txtGrepFilter');
	$txtNCGrepFilter=ProcessStringPostVar('txtNCGrepFilter');
	$txtLogGrepFilter=ProcessStringPostVar('txtLogGrepFilter');
	$txtSearchLogGrepFilter=ProcessStringPostVar('txtSearchLogGrepFilter');

	// Registration
	if ($strAction=='login') {
		if ($strAdminPassword==md5($txtPass)) {
			Unregister($strSNUserRights);
			Register($strSNAdminRights);
			header("Location: $PHP_SELF");
			exit();
		} else {
			header("Location: $PHP_SELF");
			exit();
		}
	} elseif ($strAction=='logout') {
		Unregister($strSNAdminRights);
		Unregister($strSNUserRights);
		header("Location: $PHP_SELF");
		exit();
	}
	PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
?>
<div class="Page" style="margin-top: 15px;">
<?php
	if (!$bAdmin) {?> <!-- show login form -->
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="login" />
				<span class="Label">Пароль:</span>
				<input type="password" name="txtPass" id="txtPass" />
				<input type="submit" class="submit" value="Log In as Admin!" />
			</div>
		</form>
		<script type="text/javascript">{ ById('txtPass').focus(); }</script>
		<?php
	} else {?> <!-- show control panel -->
		<form action="cpl.php">
			<div class="Form" style="width: 100%">
				<input type="hidden" name="strAction" value="addsubscribe" />
				<span class="TD CPLeftCol">
					<span class="TLabel TD CPLeftLabel">E-Mail:</span>
					<input type="text" name="txtAddMail"/>
				</span>
				<span class="TD CPMidCol">
					<span class="TLabel TD CPMidLabel">Кодировка:</span>
					<select name="txtCodepage" class="Codepage">
						<?php echo GetCodepageOptions() ?>
					</select>
				</span>
				<input type="submit" class="submit" value="Add Subscription" />
			</div>
		</form>
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="removesubscribe" />
				<span class="TD CPLeftCol">
					<span class="TLabel TD CPLeftLabel">E-Mail:</span>
					<input type="text" name="txtRemovedMail"/>
				</span>
				<span class="TD CPMidCol">
					<span class="TLabel TD CPMidLabel">Кодировка:</span>
					<select name="txtCodepage" class="Codepage">
						<?php echo GetCodepageOptions() ?>
					</select>
				</span>
				<input type="submit" class="submit" value="Remove Subscription" />
			</div>
		</form>
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="viewsubscribe" />
				<span class="TD CPLeftCol">
					<span class="TLabel TD CPLeftLabel">E-Mail:</span>
					<input type="text" name="txtGrepFilter"/>
				</span>
				<span class="TD CPMidCol">&nbsp;</span>
				<input type="submit" class="submit" value="View Subscriptions" />
			</div>
		</form>
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="viewncsubscribe" />
				<span class="TD CPLeftCol">
					<span class="TLabel TD CPLeftLabel">E-Mail:</span>
					<input type="text" name="txtNCGrepFilter"/>
				</span>
				<span class="TD CPMidCol">&nbsp;</span>
				<input type="submit" class="submit" value="View NC-Subscriptions" />
			</div>
		</form>
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="checkdupsubscribe" />
				<span class="TD CPLeftCol">&nbsp;</span>
				<span class="TD CPMidCol">&nbsp;</span>
				<input type="submit" class="submit" value="Check Duplicates" />
			</div>
		</form>
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="viewlog" />
				<span class="TD CPLeftCol">
					<span class="TLabel TD CPLeftLabel">Filter:</span>
					<input type="text" name="txtLogGrepFilter"/>
				</span>
				<span class="TD CPMidCol">&nbsp;</span>
				<input type="submit" class="submit" value="View Access Log" />
			</div>
		</form>
		<form action="cpl.php">
			<div class="Form">
				<input type="hidden" name="strAction" value="viewsearchlog" />
				<span class="TD CPLeftCol">
					<span class="TLabel TD CPLeftLabel">Filter:</span>
					<input type="text" name="txtSearchLogGrepFilter"/>
				</span>
				<span class="TD CPMidCol">&nbsp;</span>
				<input type="submit" class="submit" value="View Search Log" />
			</div>
		</form>
		<?php
	}
	// ----------------------------------------------
	// Handle different actions
	// ----------------------------------------------
	if ($strAction=='removesubscribe' && $bAdmin && !empty($txtRemovedMail)) {
		$arrCMails = fileCutEOL($strCFileName);
		$fCMails=@fopen($strCFileName, "wb");
		if (!$fCMails) {
			echol(hPar("File $strCFileName cannot be open for reading. ", 'PlainText Info'));
		} else {
			$strRemMail=str_rot13($txtRemovedMail);
			$nRemovedCount=0;
			foreach ($arrCMails as $strCMail) {
				$arrCMailData = explode('|', $strCMail);
				if (strcasecmp($strRemMail, $arrCMailData[0]) || $txtCodepage != $arrCMailData[1]) {
					WriteLine($fCMails, $strCMail);
				} else $nRemovedCount++;
			}
			fclose($fCMails);
			echol(hPar("$nRemovedCount record(s) removed successfuly", 'PlainText Info'));
		}
	}
	elseif ($strAction=='viewsubscribe' && $bAdmin) {?>
		<p class="Subtitle">DMVN Website Subscription List</p>
		<?php
		if (!empty($txtGrepFilter)) {
			echol(hPar('Using filter ['.out($txtGrepFilter).']', 'PlainText Info'));
		}

		$bOpen=true;
		$fCMails=@fopen($strCFileName, 'r');
		if (!$fCMails) {
			echol(hPar("File $strCFileName cannot be open for reading. ", 'PlainText Info'));
		}
		$nCount=0;
		while ($fCMails && !feof($fCMails)) {
			$strCMail=trim(fgets($fCMails));
			if (empty($strCMail)) continue;
			$arrCMailData=explode('|', $strCMail);
			$sEMail=str_rot13($arrCMailData[0]);

			if (empty($txtGrepFilter) || stristr($sEMail, $txtGrepFilter)) {
			if ($nCount==0) {?>
				<div style="text-align: center">
					<div class="MailList">
					<?php
			}
			++$nCount;
			?>
			<div>
				<span class="PlainText TD" style="width: 50px"><?php echo $nCount; ?></span>
				<span class="PlainText TD" style="width: 120px"><?php echo $arrCMailData[1]; ?></span>
				<span class="PlainText TD" style="width: 250px"><?php echo out($sEMail); ?></span>
			</div>
			<?php
			}
		}
		if ($nCount) {?>
			</div>
		</div>
		<?php
		} else {?>
			<p class="PlainText Info">No records!</p><?php
		}
		if ($fCMails) fclose($fCMails);
	}
	elseif ($strAction=='viewncsubscribe' && $bAdmin) {?>
		<p class="Subtitle">DMVN Website NC-Subscription List</p>
		<?php
		if (!empty($txtNCGrepFilter)) {
			echol(hPar('Using filter ['.out($txtNCGrepFilter).']', 'Subtitle'));
		}

		$fNCMails=@fopen($strNCFileName, 'r');
		if (!$fNCMails) {
			echol(hPar("File $strNCFileName cannot be open for reading. ", 'PlainText Info'));
		}

		$nCount = 0;
		while ($fNCMails && !feof($fNCMails)) {
			$strNCMail=trim(fgets($fNCMails));
			if (empty($strNCMail)) continue;
			$arrNCMailData = explode('|', $strNCMail);
			$sEMail=str_rot13($arrNCMailData[1]);
			if (empty($txtNCGrepFilter) || stristr($sEMail, $txtNCGrepFilter)) {
				if ($nCount==0) {?>
					<div style="text-align: center">
						<div class="MailList">
						<?php
				}
				$nCount++;
				?>
				<div>
					<span class="PlainText TD" style="width: 50px"><?php echo $nCount; ?></span>
					<span class="PlainText TD" style="width: 120px"><?php echo $arrNCMailData[3]; ?></span>
					<span class="PlainText TD" style="width: 80px"><?php echo out(round((time()-$arrNCMailData[2])/3600)); ?> hrs idle</span>
					<span class="PlainText TD" style="width: 250px"><?php echo out($sEMail); ?></span>
				</div>
				<?php
			}
		}
		if ($nCount) {?>
			</div>
		</div>
		<?php
		} else {?>
			<p class="PlainText Info">No records!</p><?php
		}
		if ($fNCMails) fclose($fNCMails);
	}
	elseif ($strAction=='viewlog' && $bAdmin) {?>
		<p class="Subtitle">DMVN Website Access Log</p>
		<?php
		if (!empty($txtLogGrepFilter)) {
			echol(hPar('Using filter ['.out($txtLogGrepFilter).']', 'Subtitle'));
		}

		$fLog=@fopen($strLogFileName, 'r');
		if (!$fLog) {
			echol(hPar("File $strLogFileName cannot be open for reading. ", 'PlainText Info'));
		}
		$arrGrep=explode("|", $txtLogGrepFilter);
		while ($fLog && !feof($fLog)) {
			$strLine=fgets($fLog);
			if (!stristr($strLine, "content/") & !stristr($strLine, "tmp/")) continue;
			$bDisp=true;
			foreach ($arrGrep as $strGrep) {
				$strGrep=trim($strGrep);
				if (empty($strGrep)) continue;
				if (!stristr($strLine, $strGrep)) $bDisp=false;
			}
			if ($bDisp) {?>
				<div class="LogLine"><?php echo out($strLine); ?></div>
				<?php
			}
		}
		if ($fLog) fclose($fLog);
	}
	elseif ($strAction=='viewsearchlog' && $bAdmin) {?>
		<p class="Subtitle">DMVN Website Search Log</p>
		<?php
		if (!empty($txtSearchLogGrepFilter)) {
			echol(hPar('Using filter ['.out($txtSearchLogGrepFilter).']', 'Subtitle'));
		}

		$fLog=@fopen($strSearchLogFileName, 'r');
		if (!$fLog) {
			echol(hPar("File $strSeaLogFileName cannot be open for reading. ", 'PlainText Info'));
		}
		$arrGrep = explode('|', $txtSearchLogGrepFilter);
		while ($fLog && !feof($fLog)) {
			$strLine=trim(fgets($fLog));
			if (empty($strLine)) continue;
			$arrLine=explode('|', $strLine);
			if (count($arrLine) != 2) {
				echol(hPar('Bad Line in Search Log: '.hBold(out($strLine)), 'PlainText Info'));
			}
			$strSLine = $arrLine[1];
			$bDisp=true;
			foreach ($arrGrep as $strGrep) {
				if (empty($strGrep)) continue;
				if (!stristr($strSLine, $strGrep)) $bDisp=false;
			}
			if (!$bDisp) continue;
			?><div class="LogLine"><?php echo $arrLine[0].': '.hBold(out($strSLine)); ?></div>
			<?php
		}
		if ($fLog) fclose($fLog);
	}
	elseif ($strAction=='addsubscribe' && $bAdmin && !empty($txtAddMail)) {?>
		<p class="Subtitle">DMVN Website Adding Subscription</p>
		<?php
		$fCMails=@fopen($strCFileName, "ab");
		if (!$fCMails) {
			echol(hPar("File $strCFileName cannot be open for appending. ", 'PlainText Info'));
		} else {
			fwrite($fCMails, str_rot13($txtAddMail)."|$txtCodepage\r\n");
			fclose($fCMails);
			echol(hPar('Mail address '.hBold(out($txtAddMail)). ' was successfuly added to list', 'PlainText Info'));
		}
	}
	elseif ($strAction=='checkdupsubscribe' && $bAdmin) {?>
		<p class="Subtitle">DMVN Website Duplicate Subscriptions</p>
		<?php
		$arrCMailsCount = array();
		$fCMails=@fopen($strCFileName, 'r');
		if (!$fCMails) {
			echol(hPar("File $strCFileName cannot be open for reading. ", 'PlainText Info'));
		}
		while ($fCMails && !feof($fCMails)) {
			$strCMail=trim(fgets($fCMails));
			if (empty($strCMail)) continue;
			if (!isset($arrCMailsCount[$strCMail])) $arrCMailsCount[$strCMail]=1;
			else $arrCMailsCount[$strCMail]++;
		}
		fclose($fCMails);
		$nCount=0;

		foreach ($arrCMailsCount as $strCMCKey => $strCMCValue) {
			if ($strCMCValue < 2) continue;
			if ($nCount==0) {?>
				<div style="text-align: center">
					<div class="MailList">
					<?php
			}
			$nCount++;
			$arrMail=explode('|', $strCMCKey);
			$sEMail=str_rot13($arrMail[0]);
			?>
			<div>
				<span class="PlainText TD" style="width: 50px"><?php echo $nCount; ?></span>
				<span class="PlainText TD" style="width: 120px"><?php echo $arrMail[1]; ?></span>
				<span class="PlainText TD" style="width: 70px"><?php echo $strCMCValue; ?> times</span>
				<span class="PlainText TD" style="width: 250px"><?php echo out($sEMail); ?></span>
			</div>
			<?php
		}
		if ($nCount) {?>
			</div>
		</div>
		<?php
		} else {?>
			<p class="PlainText Info">No duplicates!</p><?php
		}
	}

	if ($bAdmin) {
		echol('<div style="text-align: center; font-weight: bold;">'.llink("$PHP_SELF?strAction=logout", '[Logout]').'</div>');
	}
?>
</div>
<?php
	PutPageFooter($strDMVNMail);
	// Auto redir via jscript
	if ($bRedir) echol('<html>'.hScript("open('$PHP_SELF', '_self');").'</html>');
?>
