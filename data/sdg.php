<?php
  // ------------------------------------------------------------------------------------
  // sdg.php
  // This is part of Site Data Generator (PHP Version)
  // (C) Copyright by ]DichlofoS[ Systems, Inc, 2005-2009
  // 2009.07: Now it is PHP5-compatible
  // ------------------------------------------------------------------------------------
  
  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);

  include "../service/service.php";
  include "../gcommon.php";

  error_reporting(E_ALL);

  include "sdg-service.php";
  include "sdg-implementation.php";
  include "sdg-class-impl.php";


  // ------------------------------------------------------------------------------------
  function Death($strMessage)
  {
    RepError($strMessage);
    echo "    </table>\r\n  </body>\r\n</html>";
    die;
  }
  // ------------------------------------------------------------------------------------
  function nExtCommandArgCount($strName)
  {
    if ($strName == 'category') return 1;
    if ($strName == 'section') return 1;
    if ($strName == 'item') return 1;
    if ($strName == 'textblock') return 1;
    if ($strName == 'newsblock') return 1;
    return -1;
  }
  // ------------------------------------------------------------------------------------
  function ExecuteExtCommand($strCommand, $arrArgs)
  {
    $strFuncName = 'func_ext_exec_'.$strCommand;
    $strFuncName($arrArgs);
  }
  // ------------------------------------------------------------------------------------
  function strParseContent($strContent)
  {
    $strOutput = "";
    
    $nIndex = 0;
    $nBLevel = 0;
    while (($nSlashPos = nBLevelStrPos($strContent, $nBLevel, '\\', $nIndex)) >= 0)
    {
      $strOutput .= substr($strContent, $nIndex, $nSlashPos - $nIndex);
      $strCommandName = strGetCommandName($strContent, $nSlashPos);
      $nIndex = $nSlashPos + 1 + strlen($strCommandName);
      // Here we stand beyond the command
      if (($nArgCount = nIntCommandArgCount($strCommandName)) < 0)
        Death("Undefined control sequence <b>$strCommandName</b> detected!");

      $arrArgs = arrReadArgs($strContent, $nIndex, $nBLevel, $nArgCount, $bError);
      // Note that $strContent[$index] may be undefined after this
      if (count($arrArgs) < $nArgCount)
        Death("Argument count mismatch in <b>$strCommandName</b>: want $nArgCount, have ".count($arrArgs).".");
      if ($bError)
        Death("Error occured while parsing command <b>$strCommandName</b>: have you missed the }?");
      $arrParsedArgs = "";
      // Main recursion stack
      if (!ArrEmpty($arrArgs))
        for ($i = 0; $i < count($arrArgs); $i++)
          $arrParsedArgs[$i] = strParseContent($arrArgs[$i]);
      // Here we must glue all returned into one!
      $strFuncName = 'func_int_exec_'.strTransliterateCommand($strCommandName);
      $strOutput .= $strFuncName($arrParsedArgs);
    }
    $strOutput .= substr($strContent, $nIndex);
    return $strOutput;
  }
  // ------------------------------------------------------------------------------------
  function strGetDispTime()
  {
    $nMonth = date("n");
    $arrMonths[0] = ' [unknown] ';
    $arrMonths[1] = ' €нвар€ ';
    $arrMonths[2] = ' феврал€ ';
    $arrMonths[3] = ' марта ';
    $arrMonths[4] = ' апрел€ ';
    $arrMonths[5] = ' ма€ ';
    $arrMonths[6] = ' июн€ ';
    $arrMonths[7] = ' июл€ ';
    $arrMonths[8] = ' августа ';
    $arrMonths[9] = ' сент€бр€ ';
    $arrMonths[10] = ' окт€бр€ ';
    $arrMonths[11] = ' но€бр€ ';
    $arrMonths[12] = ' декабр€ ';
    return date('j').$arrMonths[$nMonth].date('Y').' года';
  }

  function strGetDispTimeEx()
  {
    $nMonth = date("n");
    $arrMonths[0] = ' [unknown] ';
    $arrMonths[1] = ' €нвар€ ';
    $arrMonths[2] = ' феврал€ ';
    $arrMonths[3] = ' марта ';
    $arrMonths[4] = ' апрел€ ';
    $arrMonths[5] = ' ма€ ';
    $arrMonths[6] = ' июн€ ';
    $arrMonths[7] = ' июл€ ';
    $arrMonths[8] = ' августа ';
    $arrMonths[9] = ' сент€бр€ ';
    $arrMonths[10] = ' окт€бр€ ';
    $arrMonths[11] = ' но€бр€ ';
    $arrMonths[12] = ' декабр€ ';
    return date('j').$arrMonths[$nMonth].date('Y').' года, '.date('H').' ч '.date('i').' мин';
  }


  session_start();

  if (!session_is_registered($strSNAdminRights))
  {
    header("Location: /cpl.php");
    exit();
  }
?>
<html>
  <head>
    <title>
      Site Data Generator by ]DichlofoS[. (C) 1994-2009, DMVN Corporation. All rights reversed
    </title>
  </head>
  <body>
    <table style="Font-Family: Tahoma; Font-Size: 12">
<?php



  if (!isset($ref))
    Death("Reference page is not set!");

  if ($ref == "")
    Death("Reference page is not set!");


  $itemI = new CItem;
  $secI = new CSec;
  $textblockI = new CTextBlock;
  $newsblockI = new CNewsBlock;

  // <GLOBAL.INIT>
  $arrItems = NULL;
  $arrSections = NULL;
  $arrTextBlocks = NULL;
  $arrNewsBlocks = NULL;

  $strCategory = "";
  
  // </GLOBAL.INIT>

  // Erase 'cutime' file
  $fCUTime = fopen("cutime.dat", "w");
  fclose($fCUTime);
  
  $arrSDGList = fileCutEOL("../sdg.list");

  $strCatName = substr($ref, 0, strlen($ref) - 4);

  $bCatFound = false;
  foreach ($arrSDGList as $strSDGFName)
    if ($strSDGFName == $strCatName) $bCatFound = true;

  if (!$bCatFound)
    Death("No category '$strCatName' found!");


  // <INIT>

  $arrItems = NULL;
  $arrSections = NULL;
  $arrTextBlocks = NULL;
  $arrNewsBlocks = NULL;

  $strCategory = "";

  $strSDGFName = $strCatName;

  $fOut = fopen("$strSDGFName.dat", "w");
  if (!$fOut) Death("Cannot open output file for writing!");
    
  // </INIT>

  RepMsg("Processing <b>$strSDGFName</b>...");
   
  $strContent = strReadTextFile("../content/$strSDGFName/!$strSDGFName.tex");

  // Reading external commands
  $nIndex = 0;
  $nBLevel = 0;
  while (($nSlashPos = nBLevelStrPos($strContent, $nBLevel, '\\', $nIndex)) >= 0)
  {
    $strCommandName = strGetCommandName($strContent, $nSlashPos);
    $nIndex = $nSlashPos + 1 + strlen($strCommandName);
    // Here we stand beyond the command
    if (($nArgCount = nExtCommandArgCount($strCommandName)) < 0)
      Death("Undefined external control sequence <b>$strCommandName</b> detected!");
  
    $arrArgs = arrReadArgs($strContent, $nIndex, $nBLevel, $nArgCount, $bError);
    // note that $strContent[$index] may be undefined after this
    if (count($arrArgs) < $nArgCount)
      Death("Argument count mismatch in <b>$strCommandName</b>: want $nArgCount, have ".count($arrArgs).".");
    if ($bError)
      Death("Error occured while parsing external command <b>$strCommandName</b>: have you missed the }?");
    ExecuteExtCommand($strCommandName, $arrArgs);
  }

  // Checking gathered information

  if (!ArrEmpty($arrItems))
    for ($i = 0; $i < count($arrItems); $i++)
    {
      $nMax = $i;
      for ($j = $i + 1; $j < count($arrItems); $j++)
      {
        if (!ArrEmpty($arrItems[$j]->arrRes))
        {
          if ($arrItems[$j]->arrRes[0]->nTime > $arrItems[$nMax]->arrRes[0]->nTime)
            $nMax = $j;
        }
      }
      // $nMax found
      if ($i != $nMax)
      {
        $itemTemp = $arrItems[$nMax];
        $arrItems[$nMax] = $arrItems[$i];
        $arrItems[$i] = $itemTemp;
      }
    }

  if (!ArrEmpty($arrSections)) {
    foreach ($arrSections as $secOut)
    {
      $secOut->TrimFields();
      $strOut = $strCategory.'|'.$secOut->strSection.'|'.$secOut->strTitle.'|.section.';
      WriteLine($fOut, $strOut);
    }
  }
  if (!ArrEmpty($arrTextBlocks)) {
    foreach ($arrTextBlocks as $textblockOut)
    {
      $textblockOut->TrimFields();
      $strOut = $strCategory.'|'.$textblockOut->strCaption.'|'.$textblockOut->strText.'|.textblock.|'.$textblockOut->strCaptionColor.'|'.$textblockOut->strTextColor;
      WriteLine($fOut, $strOut);
    }
  }
  if (!ArrEmpty($arrNewsBlocks)) {
    foreach ($arrNewsBlocks as $newsblockOut)
    {
      $newsblockOut->TrimFields();
      $strOut = $strCategory.'|'.$newsblockOut->strDate.'|'.$newsblockOut->strNText.'|.newsblock.';
      WriteLine($fOut, $strOut);
    }
  }

  if (!ArrEmpty($arrItems)) {
    foreach ($arrItems as $itemOut)
    {
      $itemOut->TrimFields();      
      if (!$itemOut->strSection) Death("SECTION specification is missing ITEM!");
      if (!$itemOut->strTitle) Death("TITLE specification is missing in ITEM!");
      $strSearchID = RandomString(32);
      $strOut = $strCategory.'|'.$itemOut->strSection.'|'.$itemOut->strTitle.'|'.$itemOut->strDesc.'|'.$strSearchID;
      RepMsg($strOut);
      if (!ArrEmpty($itemOut->arrRes))
        foreach ($itemOut->arrRes as $resOut)
        {
          if (!$resOut->strName || !$resOut->strDesc) Death("File name of ".$resOut->strName."' or file description missing!");
          $strOut .= '|'.$resOut->strName.'|'.$resOut->strGetDispSize().'|'.$resOut->strGetDispTime().'|'.$resOut->strDesc;
        }
      WriteLine($fOut, $strOut);
    }
  }

  $strCategoryUpdateTime = "";
  if (!ArrEmpty($arrItems))
  {
    $itemOut = $arrItems[0];
    if (!ArrEmpty($itemOut->arrRes))
    {
      $resOut = $itemOut->arrRes[0];        
      $strCategoryUpdateTime = $resOut->strGetDispTime();
    }
  }
  $fCUTime = fopen("cutime.dat", "a");
  WriteLine($fCUTime, "$strCategory|$strCategoryUpdateTime");
  fclose($fCUTime);
  fclose($fOut);
  
  $fGlobal = fopen("global.inc", "w");
  WriteLine($fGlobal, '<?php $strSLU = "'.strGetDispTimeEx().'"; ?>');
  fclose($fGlobal);

?>
    </table>
  </body>
</html>
<?php
//  echo('<HTML><SCRIPT language="javascript">open("../'.$ref.'", "_self");</SCRIPT></HTML>');
?>