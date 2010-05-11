<?php
	// -------------------------------------------------------------
	// ��������� ���� ��-������ � ��������� Windows
	// ��� ����, ����� ��������� ���������� Far-� ���������
	// �������, ��� ���� ���� �� ����� ���� ������������ �����
	// ���� � ��������� Windows.
	// -------------------------------------------------------------
	// ������ ������� ]DichlofoS[ Systems, 2006-2010
	// -------------------------------------------------------------

	extract($_SERVER);
	extract($_ENV);
	extract($_GET);
	extract($_POST);
	extract($_REQUEST);
		
	include "common.php";
	if ($bDebugEnabled) error_reporting(E_ALL);

	$lblHeader = 'DMVN WebSite Search Engine';

	$CurrentMenuItem = $mnuSearch;

	// -------------------------------------------
	// Check mature words
	function bCheckMature($strL) {
		$strL = ' '.myStrToLower($strL).' ';
		$arrMat[] = ' ���';
		$arrMat[] = ' ���';
		$arrMat[] = ' ���';
		$arrMat[] = ' ���';
		$arrMat[] = ' ����';
		$arrMat[] = ' ����';
		$arrMat[] = ' ����';
		$arrMat[] = ' ��� ';
		$arrMat[] = ' �����';
		$arrMat[] = ' ��';
		$arrMat[] = ' ��';

		foreach ($arrMat as $strMat) {
			if (strpos($strL, $strMat) !== false) return true;
		}
		return false;
	}

	// -------------------------------------------
	// Search word by data
	function FindInData($arrItem, $strNeedle, $nSIndex) {
		if ($strNeedle == '') { echo 'Assertion failed in FindInData()! '; die(); }
		// ����� ������� �������� �� ���� ������ ����
		for ($i = $nSIndex; $i < count($arrItem); $i++) {
			if (strpos(myStrToLower($arrItem[$i]), $strNeedle) !== false) return true;
		}
		// ������-������, ������ ��� � �� �����
		return false;
	}

	// -------------------------------------------
	// Total search of words by data
	function FindIn($arrSearch, $arrItem, $nSIndex) {
		// �����, ��� ����� �� (���� ���-�� �� �����, �������)
		foreach ($arrSearch as $strSearchKey => $arrSearchEl) {
			// �������, ��� ������������� ������� ������, ����
			// ������� ���� ��� ��� ������ ����������, ���� �����������
			$bGroupFound = false; // ���� ���, ���� ��� (���� ���-��)
							
			$bExpandedFound = true;
			foreach ($arrSearchEl as $strNeedle) {
				if (!FindInData($arrItem, $strNeedle, $nSIndex)) {
					$bExpandedFound = false;
					break;
				}
			}

			$bGroupFound = $bGroupFound || $bExpandedFound;
			$bGroupFound = $bGroupFound || FindInData($arrItem, $strSearchKey, $nSIndex);
							
			if (!$bGroupFound) return false;
		}
		return true;
	}

	// -------------------------------------------
	// Search filter
	function SearchFilter($strIn) {
		// ASSERT: $strIn must be in 'lowercase', non-empty string
		global $strSearchReplaceFileName;

		if (empty($strIn)) { echo 'ASSERT in SearchFilter();'; die(); }
		
		$arrOut = array();
		$bReplaced = false;
		
		$fRep = fopen($strSearchReplaceFileName, 'r');
		if (!$fRep) die();
		// TODO: file open failure
		while (!feof($fRep)) {
			$strRep = trim(fgets($fRep));
			if ($strRep == '') continue;
			$arrRep = explode('|', $strRep);
			if (count($arrRep) != 2) {
				echo "Invalid line count in replacement table!";
				die();
			}
			$arrSE = explode(',', $arrRep[0]);
			foreach ($arrSE as $strSE) {
				if ($strIn == $strSE)
				{
					// ���� �������������� ������: ������ ����� ����� ���� ��������
					// ������ ������: ����� �������:
					// �����,������|������������,������
					if (!empty($arrRep[1])) {
						$arrRE = explode(',', $arrRep[1]);
						foreach ($arrRE as $strRE) $arrOut[] = $strRE;
					}
					$bReplaced = true;
					break;
				}
			}
			if ($bReplaced) break;
		}
		fclose($fRep);

		if (!$bReplaced) $arrOut[]=$strIn;
		return $arrOut;
	}

	// Processing Post Variables
	$strAction = ProcessStringPostVar('strAction');
	$txtSearchString = ProcessStringPostVar('txtSearchString');
	$section = ProcessStringPostVar('section', '0'); 
		
	PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
	DisplayPage($CurrentMenuItem, $arrCat, $section);
?>
<div class="Page">
	<form action="search.php">
		<div class="Search">
			<input type="hidden" name="strAction" value="search" />
			<span class="InForm Bold">����� ��� ������:</span>
			<input type="text" name="txtSearchString" id="txtSearchString" />
			<input type="submit" value="�����" class="subSubmit"/>
		</div>
	</form>
	<script type="text/javascript">
		{ var e=ById('txtSearchString'); if (e) e.focus(); }
	</script>
	<?php
	if ($strAction=='search' && !empty($txtSearchString)) {
		$txtSearchString=FilterLowASCII($txtSearchString);

		$strFilteredChars = '|!?:,;.';

		for ($i = 0; $i < strlen($strFilteredChars); $i++) {
			$txtSearchString=str_replace($strFilteredChars[$i], ' ', $txtSearchString);
		}
		echol(hPar('������: '.hBold(out($txtSearchString)), 'PlainText Info'));

		$fSLog=fopen($strSearchLogFileName, 'ab');
		if ($fSLog) {
			$strDate = date("Y.m.d", time()+0);
			$strTime = date("H:i:s", time()+0);
			WriteLine($fSLog, "$REMOTE_ADDR ($strDate, $strTime)|$txtSearchString");
			fclose($fSLog);
		}

		if ($txtSearchString=='dzen' || $txtSearchString=='����') {
			echol(hRow(hCell('��, ��� �� �����, �� ��� �����...', 'PlainText Info')));
		} elseif (bCheckMature($txtSearchString)) {
			echol(hRow(hCell('@#$, @ $%^#@!$%*(-%$ @# !%$?', 'PlainText Info')));
		} else {
			$txtSearchString = myStrToLower($txtSearchString);
			$arrPreSearch = explode(' ', $txtSearchString);

			// Now we get words (maybe empty) without spaces, so we should filter them
			// and convert them to 'lowercase'
			$arrSearch = array();
			foreach ($arrPreSearch as $strPreSearch) {
				$strPreSearch = trim($strPreSearch);
				if (!empty($strPreSearch)) {
					// SearchFilter return array, maybe empty
					$arrSearchEl = SearchFilter($strPreSearch);
					if (count($arrSearchEl))
						$arrSearch[$strPreSearch] = $arrSearchEl;
					// ������: �����->������������,������
					// ������: �,��,��,�,��,�,��,��,�->[null]
					// ����� �� ������ � �����������, ��� ����� ����������
				} 
			}
			$strSS='';
			foreach ($arrSearch as $strSearchKey => $arrSearchEl) {
				$strSSEl = '';
				foreach ($arrSearchEl as $strSearchEl) $strSSEl .= "$strSearchEl ";
				$strSS .= $strSearchKey.'=['.trim($strSSEl).'] ';
			}

			echol(hPar('������������ ������ (� �������������� DCIPM): '.hBold(out($strSS)), 'PlainText Info'));

			// ---------------------------------------------
			// Search by site data items
			// ---------------------------------------------
			echo hPar('���������� ������ �� �����', 'Subtitle');
			$nDocCount=0;
			
			foreach ($arrCat as $strCatName) {
				$fData = fopen("data/$strCatName.dat", 'r');
				while (!feof($fData)) {
					$strItemData = trim(fgets($fData));
					$arrItem = explode('|', $strItemData);
					if (count($arrItem) < 4) continue;
					if ($arrItem[3] != '.section.' && $arrItem[3] != '.textblock.' && $arrItem[3] != '.newsblock.') {
						// This is not a section/textblock/newsblock description
						if (FindIn($arrSearch, $arrItem, 2)) {
						if ($nDocCount==0) {
							echo "<div class=\"SearchResults\">\r\n";
						}
						$nDocCount++;
						$sFile=$arrItem[0];
						$sAnchor=$arrItem[4];
						$sSections=$arrItem[1];
						$sTitle=$arrItem[2];
						?>
						<div class="SearchResult">
							<span class="TitleSection TSSearch">
								<?php echo flink("$sFile.php#$sAnchor", '['.MakeRange($sSections).']'); ?>
							</span>
							<span style="display: inline-block; width: 10px">&nbsp;</span>
							<span class="PlainTitle PTSearch">
								<?php echo flink("$sFile.php#$sAnchor", "$sTitle"); ?>
							</span>
						</div>
						<?php
						}
					}
				}
				fclose($fData);
			}
			if ($nDocCount) {
				echo "</div>\r\n";
				echol(hPar('������� ����������: '.hBold($nDocCount), 'PlainText Info'));
			} else {
				echol(hPar(hBold('����� �� ��� �����������'), 'PlainText Info'));
			}
			// ---------------------------------------------
			// Search by forum
			// ---------------------------------------------
			echol(hPar('���������� ������ �� ������', 'Subtitle'));
			$nDocCount = 0;

			$fForumData = fopen($strForumFileName, 'r');
			while (!feof($fForumData)) {
				$strFL = trim(fgets($fForumData));
				if ($strFL == '') continue;
				$arrFL = explode('|', $strFL);

				if (FindIn($arrSearch, $arrFL, 1)) { 
					if ($nDocCount==0) {
						echo "<div class=\"SearchResults\">\r\n";
					}
					$nDocCount++;
					$sPostID=$arrFL[0];
					$sDate=$arrFL[1];
					$sTime=$arrFL[2];
					$sName=$arrFL[3];
					$sSubject=$arrFL[4];
					if (empty($sSubject)) $sSubject='[���� �� �������]';
					?>
					<div class="SearchResult">
						<span class="PlainTitle PTSearch">
							<?php echo llink("forum.php?strDPostID=$sPostID", "$sDate&nbsp;&nbsp;&nbsp;$sTime&nbsp;&nbsp;&nbsp;<b>$sName</b>: $sSubject"); ?>
						</span>
					</div>
					<?php
				} 
			}
			fclose($fForumData);
			
			if ($nDocCount) {
				echo '</div>'; // search results table2
				echol(hPar('������� �������: '.hBold($nDocCount), 'PlainText Info'));
			} else {
				echol(hPar(hBold('����� �� ��� �����������'), 'PlainText Info'));
			}
		}
	}
?>
</div>
<?php
	PutPageFooter($strDMVNMail);
?>
