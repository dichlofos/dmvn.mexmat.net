<?php
  // -------------------------------------------------------------
  // Несколько слов по-русски в кодировке Windows
  // для того, чтобы частотный анализатор Far-а правильно
  // понимал, что этот файл на самом деле представляет собой
  // файл в кодировке Windows.
  // -------------------------------------------------------------
  // Скрипт написан ]DichlofoS[ Systems, 2006-2010
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
  function bCheckMature($strL)
  {
    $strL = ' '.myStrToLower($strL).' ';
    $arrMat[] = ' хуй';
    $arrMat[] = ' хуя';
    $arrMat[] = ' хуе';
    $arrMat[] = ' хую';
    $arrMat[] = ' пизд';
    $arrMat[] = ' муда';
    $arrMat[] = ' бляд';
    $arrMat[] = ' бля ';
    $arrMat[] = ' пидор';
    $arrMat[] = ' еб';
    $arrMat[] = ' ёб';

    foreach ($arrMat as $strMat)
      if (strpos($strL, $strMat) !== false) return true;
    return false;
  }

  // -------------------------------------------
  // Search word by data
  function FindInData($arrItem, $strNeedle, $nSIndex)
  {
    if ($strNeedle == '') { echo 'Assertion failed in FindInData()! '; die(); }
    // Поиск данного элемента во всей строке базы
    for ($i = $nSIndex; $i < count($arrItem); $i++)
      if (strpos(myStrToLower($arrItem[$i]), $strNeedle) !== false)
        return true;
    // Искали-искали, ничего так и не нашли
    return false;
  }

  // -------------------------------------------
  // Total search of words by data
  function FindIn($arrSearch, $arrItem, $nSIndex)
  {
    // Верим, что нашли всё (если что-то не нашли, сбросим)
    foreach ($arrSearch as $strSearchKey => $arrSearchEl)
    {
      // считаем, что нерасширенный элемент найден, если
      // найдена либо вся его группа расширения, либо расширитель
      $bGroupFound = false; // либо тут, либо тут (хоть где-то)
              
      $bExpandedFound = true;
      foreach ($arrSearchEl as $strNeedle)
        if (!FindInData($arrItem, $strNeedle, $nSIndex))
        {
          $bExpandedFound = false;
          break;
        }

      $bGroupFound = $bGroupFound || $bExpandedFound;
      $bGroupFound = $bGroupFound || FindInData($arrItem, $strSearchKey, $nSIndex);
              
      if (!$bGroupFound) return false;
    }
    return true;
  }

  // -------------------------------------------
  // Search filter
  function SearchFilter($strIn)
  {
    // ASSERT: $strIn must be in 'lowercase', non-empty string
    global $strSearchReplaceFileName;

    if ($strIn == '') {echo 'ASSERT in SearchFilter();'; die();}
    
    $arrOut = array();
    $bReplaced = false;
    
    $fRep = fopen($strSearchReplaceFileName, 'r');
    if (!$fRep) die();
    // TODO: file open failure
    while (!feof($fRep))
    {
      $strRep = trim(fgets($fRep));
      if ($strRep == '') continue;
      $arrRep = explode('|', $strRep);
      if (count($arrRep) != 2)
      {
        echo "Invalid line count in replacement table!";
        die();
      }
      $arrSE = explode(',', $arrRep[0]);
      foreach ($arrSE as $strSE)
        if ($strIn == $strSE)
        {
          // Идея множественного поиска: замена слова может быть массивом
          // Формат записи: через запятую:
          // матан,матана|математическ,анализ
          if ($arrRep[1] != '')
          {
            $arrRE = explode(',', $arrRep[1]);
            foreach ($arrRE as $strRE) $arrOut[] = $strRE;
          }
          $bReplaced = true;
          break;
        }
      if ($bReplaced) break;
    }
    fclose($fRep);

    if (!$bReplaced) $arrOut[] = $strIn;
    return $arrOut;
  }

  // Processing Post Variables
  $strAction = ProcessStringPostVar('strAction');
  $txtSearchString = ProcessStringPostVar('txtSearchString');
  $section = ProcessStringPostVar('section', '0'); 
    
  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
?>
<TABLE width="95%" align="center">
  <TR>
    <TD>
      <?php DisplayPage($CurrentMenuItem, $arrCat, $section); ?>
    </TD>
  </TR>
<?php
  echol('<tr><td align="center">');
  echol(
  hoForm('frmSearch', "$PHP_SELF?strAction=search").
   hTable(hRow(
    hcell('Слова для поиска:', 'InForm Bold').
    hCell(hInput('txtSearchString', 'text', 'txtSearch')).
    hCell(hInput('subSearch', 'submit', 'subSubmit', 'Поиск'))
    )).
   hcForm());
  echol('</td></tr>');

  echol(hScript("document.frmSearch.txtSearchString.focus();"));
  
  if ($strAction == 'search' && $txtSearchString != "")
  {
    $txtSearchString = FilterLowASCII($txtSearchString);

    $strFilteredChars = '|!?:,;.';

    for ($i = 0; $i < strlen($strFilteredChars); $i++)
      $txtSearchString = str_replace($strFilteredChars[$i], ' ', $txtSearchString);

    echol(hRow(hCell('Запрос: '.hBold(out($txtSearchString)), 'PlainText Info')));


    $fSLog = fopen($strSearchLogFileName, 'ab');
    if ($fSLog)
    {
      $strDate = date("Y.m.d", time()+0);
      $strTime = date("H:i:s", time()+0);
      WriteLine($fSLog, "$REMOTE_ADDR ($strDate, $strTime)|$txtSearchString");
      fclose($fSLog);
    }

    if ($txtSearchString == 'dzen' || $txtSearchString == 'дзен')
      echol(hRow(hCell('Всё, что Вы ищете, Вы уже нашли...', 'PlainText Info')));
    elseif (bCheckMature($txtSearchString))
      echol(hRow(hCell('@#$, @ $%^#@!$%*(-%$ @# !%$?', 'PlainText Info')));
    else
    {
      $txtSearchString = myStrToLower($txtSearchString);
      $arrPreSearch = explode(' ', $txtSearchString);

      // Now we get words (maybe empty) without spaces, so we should filter them
      // and convert them to 'lowercase'
      $arrSearch = array();
      foreach ($arrPreSearch as $strPreSearch)
      {
        $strPreSearch = trim($strPreSearch);
        if ($strPreSearch != '')
        {
          // SearchFilter return array, maybe empty
          $arrSearchEl = SearchFilter($strPreSearch);
          if (count($arrSearchEl))
            $arrSearch[$strPreSearch] = $arrSearchEl;
          // Пример: матан->математическ,анализ
          // Пример: в,на,за,о,до,с,из,за,и->[null]
          // Такие не вносим в расширители, тем самым игнорируем
        } 
      }
      $strSS = '';
      foreach ($arrSearch as $strSearchKey => $arrSearchEl)
      {
        $strSSEl = '';
        foreach ($arrSearchEl as $strSearchEl)
          $strSSEl .= "$strSearchEl ";
        $strSS .= $strSearchKey.'=['.trim($strSSEl).'] ';
      }

      echol(hRow(hCell('Обработанный запрос (с использованием DCIPM): '.hBold(out($strSS)), 'PlainText Info')));

      echo '<tr><td>';
      // ---------------------------------------------
      // Search by site data items
      // ---------------------------------------------
      echo hPar('Результаты поиска по сайту', 'SubTitle');
      $nDocCount = 0;
      echo '<table width="100%" border="0">';
      foreach ($arrCat as $strCatName)
      {
        $fData = fopen("data/$strCatName.dat", 'r');
        while (!feof($fData))
        {
          $strItemData = trim(fgets($fData));
          $arrItem = explode('|', $strItemData);
          if (count($arrItem) < 4) continue;
          if ($arrItem[3] != '.section.' && $arrItem[3] != '.textblock.' && $arrItem[3] != '.newsblock.')
          {
            // This is not a section/textblock/newsblock description
            if (FindIn($arrSearch, $arrItem, 2))
            { 
              $nDocCount++;
              echo(hRow(
               hCell(flink("{$arrItem[0]}.php#{$arrItem[4]}", hBold("[{$arrItem[1]}]")), 'PlainText').
               hCell(' ', '', '10px').
               hCell(flink("{$arrItem[0]}.php#{$arrItem[4]}", $arrItem[2]), 'PlainText', '100%')));
            }
          }
        }
        fclose($fData);
      }
      echo '</table>';
      if ($nDocCount == 0)
        echol(hPar(hBold('Поиск не дал результатов'), 'PlainText Info'));
      else
        echol(hPar('Найдено документов: '.hBold($nDocCount), 'PlainText Info'));
      // ---------------------------------------------
      // Search by forum
      // ---------------------------------------------
      echo hPar('Результаты поиска по форуму', 'SubTitle');
      $nDocCount = 0;

      echo '<table width="100%" border="0">';
      
      $fForumData = fopen($strForumFileName, 'r');
      while (!feof($fForumData))
      {
        $strFL = trim(fgets($fForumData));
        if ($strFL == '') continue;
        $arrFL = explode('|', $strFL);

        if (FindIn($arrSearch, $arrFL, 1))
        { 
          $nDocCount++;
          if ($arrFL[4] == '') $arrFL[4] = '[Тема не указана]';
          echo(hRow(hCell(llink("forum.php?strDPostID={$arrFL[0]}", "{$arrFL[1]} {$arrFL[2]} ".hBold($arrFL[3]).": {$arrFL[4]}"), 'PlainText')));
        } 
      }
      fclose($fForumData);
      echo '</table>';
      if ($nDocCount == 0)
        echol(hPar(hBold('Поиск не дал результатов'), 'PlainText Info'));
      else
        echol(hPar('Найдено записей: '.hBold($nDocCount), 'PlainText Info'));
      
      echo '</td></tr>';
    }
  }
?>
</table>
<?php
  PutPageFooter($strDMVNMail);
?>
