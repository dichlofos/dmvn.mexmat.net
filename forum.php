<?php
	extract($_SERVER);
	extract($_ENV);
	extract($_GET);
	extract($_POST);
	extract($_REQUEST);

	include "common.php";
	if ($bDebugEnabled) error_reporting(E_ALL);

	$lblPage='��������';
	$lblAddComment='���������';
	$lblName='���<span style="color:#dddd77;">*</span>:';
	$lblHomePage='����:';
	$lblEMail='�����:';
	$lblComment='���������<span style="color:#dddd77;">*</span>:';
	$lblTheme='����:';
	$lblDate='����:';
	$lblTime='�����:';
	$lblLogin='�����';
	$lblAdminComment="�����������:";
	$lblDate="����:";
	$lblIP="IP:";
	$lblUserAgent="User Agent:";
	$lblFlag='Flag:';

	$lblReservedName='��� <i>Admin</i> ����� ������������ ������ �������������';

	// Comments per page
	$nCommentsPerPage=10;

	// Administrative functions
	$lblPassword='������:';

	$strMailTo="DMVN <$strDMVNMailReal>, Editor <dichlofos-mv@yandex.ru>";
	$strMailSubject='Forum message from ';

	$arrSmiles=array('grin', 'rolleyes', 'sad', 'shocked', 'smile', 'tongue', 'undecided', 'wink');

	$strDefaultAdminName="Admin";
	$strDefaultAdminHomePage="http://dmvn.mexmat.net";
	$strDefaultAdminEMail=$strDMVNMailReal;
	$strReservedName=$strDefaultAdminName;

	$CurrentMenuItem=$mnuForum;

	$bRedir=false;

	// Processing all HTTP POST variables
	$section=ProcessStringPostVar('section', '0');
	$strAction=ProcessStringPostVar('strAction', 'post');
	$strPostID=ProcessStringPostVar('strPostID');
	$strUpdate=ProcessStringPostVar('strUpdate', 'no');
	$strDPostID=ProcessStringPostVar('strDPostID');

	$txtDate=ProcessStringPostVar('txtDate');
	$txtTime=ProcessStringPostVar('txtTime');
	$txtName=ProcessStringPostVar('txtName');
	$txtTheme=ProcessStringPostVar('txtTheme');
	$txtHomePage=ProcessStringPostVar('txtHomePage');
	$txtEMail=ProcessStringPostVar('txtEMail');
	$txtComment=ProcessStringPostVar('txtComment');
	$txtAdminComment=ProcessStringPostVar('txtAdminComment');
	$txtIP=ProcessStringPostVar('txtIP');
	$txtUserAgent=ProcessStringPostVar('txtUserAgent');
	$txtFlag=ProcessStringPostVar('txtFlag');

	$txtDate=FilterLowASCII($txtDate);
	$txtTime=FilterLowASCII($txtTime);
	$txtName=FilterLowASCII($txtName);
	$txtTheme=FilterLowASCII($txtTheme);
	$txtHomePage=FilterLowASCII($txtHomePage);
	$txtEMail=FilterLowASCII($txtEMail);
	$txtIP=FilterLowASCII($txtIP);
	$txtUserAgent=FilterLowASCII($txtUserAgent);
	$txtFlag=FilterLowASCII($txtFlag);

	$bPostInvalid=false;

	srand();
	if ($strAction == 'login') {
		Unregister($strSNAdminRights);
		Unregister($strSNUserRights);
		// Check passwords
		if ($strAdminPassword==md5($txtPass)) {
			Register($strSNAdminRights);
			header("Location: $PHP_SELF");
			exit();
		} elseif ($strUserPassword==md5($txtPass)) {
			Register($strSNUserRights);
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
	DisplayPage($CurrentMenuItem, $arrCat, $section);
?>
<div class="Page">
<?php
	if ($strAction == 'post') {
		// If user is not registered, show authorization form
		if (!$bAuth) {?>
			<form action="forum.php" method="post">
				<div class="Form">
					<span class="Label">������:</span>
					<input type="hidden" name="strAction" value="login" />
					<input type="password" name="txtPass" id="txtPass" />
					<input type="submit" class="submit" value="�����" />
				</div>
			</form>
			<?php
		} else {
			$valDate='';
			$valTime='';
			$valName='';
			$valTheme='';
			$valHomePage='';
			$valEMail='';
			$valComment='';
			$valAdminComment='';
			$valIP='';
			$valUserAgent='';
			$valFlag='';

			$bProcess=false;
			// Analyze parameters
			if (empty($strPostID)) {
				// This is a new post
				if ($bAdmin) {
					// Admin can omit some values, he's mighty :)
					if (empty($txtName)) $txtName=$strDefaultAdminName;
					if (empty($txtHomePage)) $txtHomePage=$strDefaultAdminHomePage;
					if (empty($txtEMail)) $txtEMail=$strDefaultAdminEMail;
				}
				$valName=$txtName;
				$valTheme=$txtTheme;
				$valHomePage=$txtHomePage;
				$valEMail=$txtEMail;
				$valComment=$txtComment;
				$valAdminComment=$txtAdminComment;
				$bProcess=true;
			} else {
				// We should edit the post (godmode only)
				// Get post $strPostID from file
				if ($bAdmin) {
					if ($strUpdate != 'yes') {
						$strUpdate='yes';
						$fForum=fopen($strForumFileName, 'r');
						if (!$fForum) FDeath('Cannot open forum file!');
						while (!feof($fForum))
						{
							$strFL=trim(fgets($fForum));
							if ($strFL == '') continue;
							$arrFL=explode('|', $strFL);
							if ($arrFL[0] == $strPostID) {
								$valDate=$arrFL[1];
								$valTime=$arrFL[2];
								$valName=StoreToInput($arrFL[3]);
								$valTheme=StoreToInput($arrFL[4]);
								$valHomePage=StoreToInput($arrFL[5]);
								$valEMail=StoreToInput($arrFL[6]);
								$valComment=StoreToInput($arrFL[7]);
								$valAdminComment=StoreToInput($arrFL[8]);
								$valIP=$arrFL[9];
								$valUserAgent=$arrFL[10];
								$valFlag=$arrFL[11];

								$txtDate=$valDate;
								$txtTime=$valTime;
								$txtName=$valName;
								$txtTheme=$valTheme;
								$txtHomePage=$valHomePage;
								$txtEmail=$valEMail;
								$txtComment=$valComment;
								$txtAdminComment=$valAdminComment;
								$txtIP=$valIP;
								$txtUserAgent=$valUserAgent;
								$txtFlag=$valFlag;

								$bProcess=true;
								break;
							}
						}
						fclose($fForum);
						// Invalidate post
						$bPostInvalid=true;

						if (!$bProcess) $bRedir=true;
					} else { // $strUpdate: 'yes'
						// we should update post info from $txt-Variables
						// $strPostID is set
						$valDate=$txtDate;
						$valTime=$txtTime;
						$valName=$txtName;
						$valTheme=$txtTheme;
						$valHomePage=$txtHomePage;
						$valEMail=$txtEMail;
						$valComment=$txtComment;
						$valAdminComment=$txtAdminComment;
						$valIP=$txtIP;
						$valUserAgent=$txtUserAgent;
						$valFlag=$txtFlag;

						$bProcess=true;
					} // $strUpdate check
				} // $bAdmin
			} // $strPostID check

			if ($bProcess) {
				// -----------------------------------
				// Here follows check if we should post
				$strError='';
				if (empty($txtName)) {
					$strError.="�� �� ������� ���. ";
					$bPostInvalid=true;
				}
				if ($txtName==$strReservedName && !$bAdmin) {
					$strError.="$lblReservedName. ";
					$bPostInvalid=true;
				}
				if (strlen($txtComment) < 5) {
					$strError.="�� ����� ������� �������� ���������. ";
					$bPostInvalid=true;
				}
				// !!! Here we can add some checking if $txtFlag is empty
				// -----------------------------------
				// Display form and data (if necessary)
				if ($bPostInvalid) {?>
					<div style="text-align: center;">
					<?php
					foreach ($arrSmiles as $strSmile) {
						$sExt='png';
						if ($strSmile=='rolleyes' || $strSmile=='shocked') $sExt='gif';
						echol(hImg("images/smiles/$strSmile.$sExt", $strSmile, 'SmileTop', '', '',
							attr('onclick', "InsertText('txtComment', '\$$strSmile\$')")));
					}?>
					</div>
					<form action="forum.php" method="post">
						<div class="Form">
							<input type="hidden" name="strAction" value="post" />
							<input type="hidden" name="strPostID" value="<?php echo $strPostID; ?>" />
							<input type="hidden" name="strUpdate" value="<?php echo $strUpdate; ?>" />
							<input type="hidden" name="strDPostID" value="<?php echo $strDPostID; ?>" />
					<?php
					if ($bAdmin && !empty($strPostID)) {?>
								<div><!-- Date -->
									<span class="TD ForumPost"><?php echo $lblDate; ?></span>
									<input type="text" name="txtDate" class="Forum" value="<?php echo $valDate; ?>"/>
								</div>
								<div><!-- Time -->
									<span class="TD ForumPost"><?php echo $lblTime; ?></span>
									<input type="text" name="txtTime" class="Forum" value="<?php echo $valTime; ?>"/>
								</div>
							<?php
					}?>
								<div><!-- Name -->
									<span class="TD ForumPost"><?php echo $lblName; ?></span>
									<input type="text" name="txtName" class="Forum" id="txtName" value="<?php echo $valName; ?>"/>
								</div>
								<div><!-- Theme -->
									<span class="TD ForumPost"><?php echo $lblTheme; ?></span>
									<input type="text" name="txtTheme" class="Forum" value="<?php echo $valTheme; ?>"/>
								</div>
								<div><!-- HomePage -->
									<span class="TD ForumPost"><?php echo $lblHomePage; ?></span>
									<input type="text" name="txtHomePage" class="Forum" value="<?php echo $valHomePage; ?>"/>
								</div>
								<div><!-- EMail -->
									<span class="TD ForumPost"><?php echo $lblEMail; ?></span>
									<input type="text" name="txtEMail" class="Forum" value="<?php echo $valEMail; ?>"/>
								</div>
					<?php
					if ($bAdmin && !empty($strPostID)) {?>
								<div><!-- IP -->
									<span class="TD ForumPost"><?php echo $lblIP; ?></span>
									<input type="text" name="txtIP" class="Forum" value="<?php echo $valIP; ?>"/>
								</div>
								<div><!-- UserAgent -->
									<span class="TD ForumPost"><?php echo $lblUserAgent; ?></span>
									<input type="text" name="txtUserAgent" class="Forum" value="<?php echo $valUserAgent; ?>"/>
								</div>
								<div><!-- Flag -->
									<span class="TD ForumPost"><?php echo $lblFlag; ?></span>
									<input type="text" name="txtFlag" class="Forum" value="<?php echo $valFlag; ?>"/>
								</div><?php
					}
					?>
								<div><!-- Comment -->
										<span class="TD ForumPost" style="width: 706px;"><?php echo $lblComment; ?></span><br />
										<textarea name="txtComment" id="txtComment" onselect="StoreCaret(this);" onclick="StoreCaret(this);"
											onkeyup="StoreCaret(this);" rows="7" cols="80"><?php echo out($valComment); ?></textarea>
								</div>
					<?php
					if ($bAdmin && !empty($strPostID)) {?>
								<div><!-- AdminComment -->
										<span class="TD ForumPost" style="width: 706px;"><?php echo $lblAdminComment; ?></span><br />
										<textarea name="txtAdminComment" rows="7" cols="80"><?php echo out($valAdminComment); ?></textarea>
								</div>
						<?php
					}
					if (!empty($strError)) {?>
								<div class="Label Red">
									<?php echo $strError ?>
								</div><?php
					}?>
								<div style="display: inline-block; text-align: right; width: 706px;"><!-- Submit -->
									<input type="submit" class="submit" value="<?php echo $lblAddComment; ?>" />
								</div>
							</div>
						</form>
					<script type="text/javascript">
						ById('txtName').focus();
					</script>
					<?php
				} else {//$bPostInvalid=false
					if (empty($strPostID)) {
						$strPostID=RandomString(32); // we believe, they won't repeat!
						$txtDate=date("d.m.y", time()+0);
						$txtTime=date("H:i:s", time()+0);
						$txtIP=$REMOTE_ADDR;
						$txtUserAgent=$HTTP_USER_AGENT;
						$txtFlag='user';
						if ($bAdmin) $txtFlag='admin';
						//------------------------------------------------
						// First, send mail if necessary
						if (empty($txtEMail)) $txtEMail="forumuser@dmvn.mexmat.net";
						if (empty($txtHomePage)) $txtHomePage="http://localhost";
						$strHeaders="Content-Type: text/plain; charset=windows-1251\n";
						@mail($strMailTo, $strMailSubject.$txtName,
							"Name:     $txtName\n".
							"Theme:    $txtTheme\n".
							"HomePage: $txtHomePage\n".
							"E-Mail:   $txtEMail\n".
							"Comment:  $txtComment\n",
							"From: $txtName <$txtEMail>\n".
							"Reply-To: $txtName <$txtEMail>\n".
							$strHeaders);
						// Then we should send subscriptions
						if ($bAdmin && $txtName==$strReservedName) {?>
							<p class="PlainText Info">Sending News&amp;Information</p>
							<?php
							$arrEMails=fileCutEOL($strCFileName);
							foreach ($arrEMails as $strEMail) {
								$arrCMailData=explode('|', $strEMail);
								$strSubscrEMail=str_rot13($arrCMailData[0]);
								if (!empty($strSubscrEMail)) {
									$sTargetCP=$arrCMailData[1];
									$strHeaders="Content-Type: text/plain; charset=$sTargetCP\n";
									$bResult=@mail($strSubscrEMail,
										$strMailSubscriptionSubject,
										RecodeToCodepage($txtComment, $sTargetCP),
										"From: DMVN <$strDMVNMailReal>\n".
										"Reply-To: DMVN <$strDMVNMailReal>\n".
										$strHeaders);
									if (!$bResult) {
										echol(hPar("Mail sending to <b>$strSubscrEMail</b> failed, interrupting.", 'PlainText Info'));
										break;
									}
									echol(hPar("Mail sent to <b>$strSubscrEMail<b>.", 'PlainText'));
								}
							}
						}
						// Writing to forum file
						// common
						$txtName=InputToStore($txtName);
						$txtTheme=InputToStore($txtTheme);
						$txtHomePage=InputToStore($txtHomePage);
						$txtEMail=InputToStore($txtEMail);
						$txtComment=InputToStore($txtComment);
						$txtAdminComment=InputToStore($txtAdminComment);
						$strFullPost="$strPostID|$txtDate|$txtTime|$txtName|$txtTheme|$txtHomePage|$txtEMail|$txtComment|$txtAdminComment|$txtIP|$txtUserAgent|$txtFlag";
						// end of common
						$fForum=fopen($strForumFileName, 'r');
						$strFContents=fread($fForum, filesize($strForumFileName));
						fclose($fForum);
						$fForum=fopen($strForumFileName, 'w');
						if (!$fForum) FDeath('DEBUG: Cannot open file for writing!');
						WriteLine($fForum, $strFullPost);
						fwrite($fForum, $strFContents);
						fclose($fForum);
					} else {
						// common
						$txtName=InputToStore($txtName);
						$txtTheme=InputToStore($txtTheme);
						$txtHomePage=InputToStore($txtHomePage);
						$txtEMail=InputToStore($txtEMail);
						$txtComment=InputToStore($txtComment);
						$txtAdminComment=InputToStore($txtAdminComment);
						$strFullPost="$strPostID|$txtDate|$txtTime|$txtName|$txtTheme|$txtHomePage|$txtEMail|$txtComment|$txtAdminComment|$txtIP|$txtUserAgent|$txtFlag";
						// end of common
						$arrNewFData=array();
						$fForum=fopen($strForumFileName, 'r');
						if (!$fForum) FDeath('DEBUG: forum.update(): cannot open file for reading!');
						while (!feof($fForum)) {
							$strFL=trim(fgets($fForum));
							if (empty($strFL)) continue;
							$arrFL=explode('|', $strFL);
							if ($arrFL[0] != $strPostID) $arrNewFData[]=$strFL;
							else $arrNewFData[]=$strFullPost;
						}
						fclose($fForum);
						$fForum=fopen($strForumFileName, 'w');
						foreach ($arrNewFData as $strFL) WriteLine($fForum, $strFL);
						fclose($fForum);
					}
					$bRedir=true;
				} // $bPostInvalid
			} // $bProcess
		} // $bAuth
	} elseif ($strAction=='delete' && $bAdmin) {
		$arrNewFData=array();
		$fForum=fopen($strForumFileName, 'r');
		if (!$fForum) FDeath('DEBUG: forum.delete(): cannot open file for reading!');
		while (!feof($fForum)) {
			$strFL=trim(fgets($fForum));
			if (empty($strFL)) continue;
			$arrFL=explode('|', $strFL);
			if ($arrFL[0] != $strPostID) $arrNewFData[]=$strFL;
		}
		fclose($fForum);
		$fForum=fopen($strForumFileName, 'w');
		foreach ($arrNewFData as $strFL) WriteLine($fForum, $strFL);
		fclose($fForum);
		$bRedir=true;
	}
	// ---------------------- display
	if (!$bRedir) {
		if ($bAuth) {
			echol('<div style="text-align: center; font-weight: bold;">'.llink("$PHP_SELF?strAction=logout", '[�����]').'</div>');
		}

		$fForum=fopen($strForumFileName, 'r');
		if (!$fForum) FDeath('DEBUG: forum.display(): Cannot open forum file!');

		// first, read whole file and fetch posts that we should draw
		$nTotalPosts=0;
		$bStartDisplay=false;
		if (empty($strDPostID)) $bStartDisplay=true; // open immediately
		$nPN=0;
		$nDisplayedCount=0;
		$nCurrentPage=0;
		$aPosts=array();
		$aPagePostIDs=array();
		while (!feof($fForum)) {
			$sFL=trim(fgets($fForum));
			if (empty($sFL)) continue;
			// explode and check ID
			$aFL=explode('|', $sFL);
			if ($aFL[0]==$strDPostID) {
				$bStartDisplay=true;
				$nCurrentPage=floor($nPN/$nCommentsPerPage);
			}
			if ($nPN % $nCommentsPerPage==0) {
				$nPage=$nPN / $nCommentsPerPage;
				$aPagePostIDs[$nPage]=$aFL[0];
			}
			if ($bStartDisplay && $nDisplayedCount<$nCommentsPerPage) {
				$aPosts[]=$aFL; // store exploded values
				$nDisplayedCount++;
			}
			$nPN++;
			$nTotalPosts++;
		}
		fclose($fForum);
		// Next it's time to display fetched information
		$nTotalPages=ceil($nTotalPosts/$nCommentsPerPage);
		$sPageLinks='';
		// draw page links
		// TODO:
		foreach ($aPagePostIDs as $nPage => $sPostID) {
			if ($nCurrentPage==$nPage) {
				$sPageLinks.="<span class=\"CurrentPage\">$nPage</span> ";
			} else {
				$sPageLinks.=hHref("$PHP_SELF?strDPostID=$sPostID", "[$nPage]").' ';
			}
		}
		?>
		<div class="ForumPages"><?php echo $sPageLinks; ?></div>
		<div class="Forum">
			<div class="ForumInner">
		<?php
		foreach ($aPosts as $aFL) {
			$valPostID=$aFL[0];
			$valDate=$aFL[1];
			$valTime=$aFL[2];
			$valName=StoreToInput($aFL[3]);
			$valTheme=StoreToInput($aFL[4]);
			$valHomePage=StoreToInput($aFL[5]);
			$valEMail=StoreToInput($aFL[6]);
			$valComment=StoreToInput($aFL[7]);
			$valAdminComment=StoreToInput($aFL[8]);
			$valIP=$aFL[9];
			$valUserAgent=$aFL[10];
			$valFlag=$aFL[11];

			$dispEMail=$bAuth ? $valEMail : 'please@uthorize.yourself';
			$dispIP=$bAuth ? $valIP : 'IP Saved';
			$dispUserAgent=$bAuth ? $valUserAgent : 'User Agent Saved';

			$sUserFunctions=' '.hHref("$PHP_SELF?strDPostID=$valPostID", '[Link]');
			$sAdminFunctions='';
			if ($bAdmin) $sAdminFunctions .= ' '.hHref("$PHP_SELF?strAction=post&amp;strPostID=$valPostID", '[Edit]');
			if ($bAdmin) $sAdminFunctions .= ' '.hHref("$PHP_SELF?strAction=delete&amp;strPostID=$valPostID", '[Erase]');

			// Display post
			?><div class="PostHeader"><?php
			echo hHref('mailto:'.FilterDQuotes($dispEMail), hImg('/images/icons/em.gif', 'EMail', 'MessageIcon', '', '', attr('title', out($dispEMail)))).
				hHref(FilterDQuotes($valHomePage), hImg('/images/icons/hm.gif', 'HomePage', 'MessageIcon')).
				hImg('/images/icons/ip.png', 'IP', 'MessageIcon', '', '', attr('title', out($dispIP))).
				hImg('/images/icons/br.gif', 'UserAgent', 'MessageIcon', '', '', attr('title', out($dispUserAgent))).
				"$sUserFunctions $sAdminFunctions ".out($valDate).' '.out($valTime).'  <b>'.out($valName).'</b>: '.out($valTheme);
			?></div>
			<?php
				$sPostClass=($valFlag=='admin') ? 'PlainText PostText AdminText' : 'PlainText PostText';
			?><div class="<?php echo $sPostClass;?>"><?php echo FormatPost(out($valComment)); ?></div>
			<?php
			if (!empty($valAdminComment)) {?>
				<div class="PlainText PostText AdminText AdminHeader">�����������:</div>
				<div class="PlainText PostText AdminText"><?php echo FormatPost(out($valAdminComment)); ?></div>
				<?php
			}
		}
		?>
			</div><!-- ForumInner -->
		</div><!-- Forum -->
		<div class="ForumPages"><?php echo $sPageLinks; ?></div>
		<?php
	}
?>
</div><!-- Page -->
<?php
	PutPageFooter($strDMVNMail);
	// Auto redir via jscript
	if ($bRedir) echo('<html>'.hScript("open('$PHP_SELF', '_self');").'</html>');
	// Manual redir via jscript
	//if ($bRedir) echo('<html>'.llink("$PHP_SELF?strDPostID=$strDPostID", 'Do Redirect Now!').'</html>');

	// --------------------------------------------------------------------------------
	// Converts string from user input to our internal format for storing data
	function FDeath($strMsg) {
		echol(hPar("DEBUG: $strMsg", 'PlainText Info'));
		die();
	}
	// -------------------------------------------------------------
	// Reverses the actions of the previous function
	function FormatPost($strPost) {
		global $arrSmiles;
		foreach ($arrSmiles as $strSmile) {
			$sExt='png';
			if ($strSmile=='rolleyes' || $strSmile=='shocked') $sExt='gif';
			$strPost=str_replace("\$$strSmile\$", hImg("/images/smiles/$strSmile.$sExt", $strSmile, 'Smile'), $strPost);
		}
		$strPost=str_replace("\r\n", "<br/>", $strPost);
		$strPost=TraceURL($strPost);
		return $strPost;
	}

	// -------------------------------------------------------------
	function bURLSymbol($strSym) {
		return preg_match("/[a-zA-Z0-9\/\!+&#=%:?._-]/", $strSym);
	}

	function TraceURL($strS) {
		// We got htmlspecialchar-ed string, so we must decode it slightly
		$cstrHTTP='http://';
		$nPos=0;
		$strNewS='';
		$strS=str_replace('&amp;', '&MyAmp.', $strS);
		while (true) {
			$nPos=strpos($strS, $cstrHTTP);
			if ($nPos===false) break;
			$nCPos=$nPos+strlen($cstrHTTP);
			while ($nCPos < strlen($strS)) {
				if (bURLSymbol($strS[$nCPos])) $nCPos++; else break;
			}
			$strNewS.=str_replace('&MyAmp.', '&amp;', substr($strS, 0, $nPos));
			$strHLink=substr($strS, $nPos, $nCPos-$nPos);
			// there are no spaces in real HLink, so we use ' ' to avoid
			// prefix replacement
			$strRHLink=str_replace('&MyAmp.', '& ', $strHLink);
			$strRHLink=str_replace(' ', '', $strRHLink);
			if ($strRHLink[strlen($strRHLink)-1] == '.') {
				$strRHLink=substr($strRHLink, 0, strlen($strRHLink)-1);
			}
			// Visible link
			$strVHLink=str_replace('&MyAmp.', '&amp;', $strHLink);
			// Form hyperlink
			$strHLink=flink($strRHLink, $strVHLink);
			$strNewS.=$strHLink;
			$strS=substr($strS, $nCPos);
		}
		return $strNewS.str_replace('&MyAmp.', '&amp;', $strS);
	}
?>
