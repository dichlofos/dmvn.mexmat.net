<?php
  // ------------------------------------------------------------------------------------
  // sdg-service.php
  // This is part of Site Data Generator (PHP Version)
  // (C) Copyright by ]DichlofoS[ Systems, Inc, 2005
  // ------------------------------------------------------------------------------------
  
  error_reporting(E_ALL);

  function strReadTextFile($strFileName)
  {
    $fText = fopen($strFileName, "r");
    if (!$fText) die("Fatal: cannot open file $strFileName!");
    $strContent = fread($fText, filesize($strFileName));
    $strContent .= " ";
    $strContent = str_replace("\n", " ", $strContent);
    $strContent = str_replace("\r", " ", $strContent);
    $strContent = str_replace("\t", " ", $strContent);
    return $strContent;
  }
  // ------------------------------------------------------------------------------------
  // 0 1 1 1 0   Levels example
  // { a b c }
  function nBLevelStrPos($strContent, $nLevel, $chSymbol, $nStartIndex)
  {
    $nInitialLevel = $nLevel;
    for ($i = $nStartIndex; $i < strlen($strContent); $i++)
    {
      $chSym = $strContent[$i];
      if ($chSym == '}') $nLevel--;
      if ($chSym == $chSymbol && $nLevel == $nInitialLevel) return $i;
      if ($chSym == '{') $nLevel++;
    }
    return -1;
  }  
  // ------------------------------------------------------------------------------------
  function bIsAlpha($chSym)
  {
    return ($chSym >= 'A' && $chSym <= 'Z' || $chSym >= 'a' && $chSym <= 'z');
  }
  // ------------------------------------------------------------------------------------
  function strGetCommandName($strContent, $nSlashPos)
  {
    $strName = "";
    for ($i = $nSlashPos + 1; $i < strlen($strContent); $i++)
    {
      $chSym = $strContent[$i];
      if (!bIsAlpha($chSym))
      {
        if (!$strName) // single-character command
        {
          $strName = $chSym;
          return $strName;
        }
        else return $strName; // non-terminal: we should stop scan
      }
      else $strName .= $chSym;
    }
  }
  // ------------------------------------------------------------------------------------
  function arrReadArgs($strContent, &$nIndex, $nLevel, $nArgCount, &$bError)
  {
    $bError = false;
    $arrArgs = "";
    for ($i = 0; $i < $nArgCount; $i++)
    {
      while ($nIndex < strlen($strContent))
      {
        $chSym = $strContent[$nIndex];
        if ($chSym != ' ') break; else $nIndex++;
      }
      if ($nIndex >= strlen($strContent)) // we're out of range
      {
        $bError = true;
        return ""; 
      }
      else
      {
        if ($strContent[$nIndex] != '{') // we're dealing with one-character argument
        {
          $arrArgs[$i] = $strContent[$nIndex];
          $nIndex++;
          continue;
        }
        else // we're dealing with {...} argument
        {
          if (($nClosingPos = nBLevelStrPos($strContent, $nLevel, '}', $nIndex)) < 0)
          {
            $bError = true;
            return ""; // closing bracket not found
          }
          $arrArgs[$i] = substr($strContent, $nIndex+1, $nClosingPos - $nIndex - 1);
          $nIndex = $nClosingPos + 1;
        }
      }
    }
    return $arrArgs;
  }
  // ------------------------------------------------------------------------------------
?>
