<?php
  // ------------------------------------------------------------------------------------
  // sdg-class-impl.php
  // This is part of Site Data Generator (PHP Version)
  // (C) Copyright by ]DichlofoS[ Systems, Inc, 2005
  // ------------------------------------------------------------------------------------

  error_reporting(E_ALL);

  // ------------------------------------------------------------------------------------
  class CRes
  {
    var $strName;
    var $strDesc;
    var $nTime;
    var $nSize;

    function CRes()
    {
      $this->Clear();
    }

    function Clear()
    {
      $this->strName = "";
      $this->strDesc = "";
      $this->nTime = 0;
      $this->nSize = 0;
    }

    function TrimFields()
    {
      $this->strName = trim($this->strName);
      $this->strDesc = trim($this->strDesc);
    }

    function Display()
    {
      RepMsg("CItem::CRes::Display()->Name: ".$this->strName.",");
      RepMsg("CItem::CRes::Display()->Desc: ".$this->strDesc.",");
      RepMsg("CItem::CRes::Display()->Time: ".$this->nTime."(".$this->strGetDispTime().").");
      RepMsg("CItem::CRes::Display()->Size: ".$this->nSize."(".$this->strGetDispSize().").");
      RepMsg("");
    }

    function strGetDispSize()
    {
      $strSize = "";
      $strS = round($this->nSize / 1024);

      while (strlen($strS) > 3)
      {
        $strSize = " ".substr($strS, strlen($strS) - 3, 3).$strSize;
        $strS = substr($strS, 0, strlen($strS) - 3);
      }
      $strSize = $strS.$strSize;

      if ($this->nSize < 1024) return "1K";

      return $strSize."K";
    }

/* 
    function strGetDispTime()
    {
      $nMonth = date("n", $this->nTime);
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
      return date('j', $this->nTime).$arrMonths[$nMonth].date('Y', $this->nTime).' года';
    }
*/
		// web-optimizing date-time display
		function strGetDispTime() {
			return date('d.m.Y', $this->nTime);
    }

  }

  // ------------------------------------------------------------------------------------
  class CItem
  {
    var $strTitle;
    var $strDesc;
    var $strSection;
    var $arrRes;

    function CItem()
    {
        $this->Clear();
    }

    function Clear()
    {
      $this->strTitle = "";
      $this->strDesc = "";
      $this->strSection = "";
      $this->arrRes = NULL;
    }

    function TrimFields()
    {
      $this->strTitle = trim($this->strTitle);
      $this->strDesc = trim($this->strDesc);
      $this->strSection = trim($this->strSection);
      if (!ArrEmpty($this->arrRes))
        foreach ($this->arrRes as $resI) $resI->TrimFields();
    }

    function Display()
    {
      RepMsg("CItem::Display()->Title: ".$this->strTitle.";");
      RepMsg("CItem::Display()->Desc: ".$this->strDesc.";");
      RepMsg("CItem::Display()->Section: ".$this->strSection.";");
      if (!ArrEmpty($this->arrRes)) {
        foreach ($this->arrRes as $resI) $resI->Display();
      }
    }
    
    function PostprocessLigatures()
    {
      // We should replace ligatures in the following order:
      // ~
      $this->strTitle = str_replace("~", "&nbsp;", $this->strTitle);
      $this->strDesc =  str_replace("~", "&nbsp;", $this->strDesc);
    }

  }

  // ------------------------------------------------------------------------------------
  class CSec
  {
    var $strTitle;
    var $strSection;

    function CSec()
    {
      $this->Clear();
    }

    function Clear()
    {
      $this->strTitle = "";
      $this->strSection = "";
    }

    function TrimFields()
    {
      $this->strTitle = trim($this->strTitle);
      $this->strSection = trim($this->strSection);
    }

    function Display()
    {
      RepMsg("CSec::Display()->Title: ".$this->strTitle.";");
      RepMsg("CSec::Display()->Section: ".$this->strSection.";");
    }
    
    function PostprocessLigatures()
    {
      // We should replace ligatures in the following order:
      // ~
      $this->strTitle = str_replace("~", "&nbsp;", $this->strTitle);
    }

  }

  // ------------------------------------------------------------------------------------
  class CTextBlock
  {
    var $strCaption;
    var $strCaptionColor;
    var $strText;
    var $strTextColor;

    function CTextBlock()
    {
      $this->Clear();
    }

    function Clear()
    {
      $this->strCaption = "";
      $this->strCaptionColor = "";
      $this->strText = "";
      $this->strTextColor = "";
    }

    function TrimFields()
    {
      $this->strCaption = trim($this->strCaption);
      $this->strText = trim($this->strText);
    }

    function Display()
    {
      RepMsg("CTextBlock::Display()->Caption: ".$this->strCaption.";");
      RepMsg("CTextBlock::Display()->CaptionColor: ".$this->strCaptionColor.";");
      RepMsg("CTextBlock::Display()->Text: ".$this->strText.";");
      RepMsg("CTextBlock::Display()->TextColor: ".$this->strTextColor.";");
    }
    
    function PostprocessLigatures()
    {
      // We should replace ligatures in the following order:
      // ~
      $this->strCaption = str_replace("~", "&nbsp;", $this->strCaption);
      $this->strText = str_replace("~", "&nbsp;", $this->strText);
    }

  }

  // ------------------------------------------------------------------------------------
  class CNewsBlock
  {
    var $strDate;
    var $strNText;

    function CTextBlock()
    {
      $this->Clear();
    }

    function Clear()
    {
      $this->strDate = "";
      $this->strNText = "";
    }

    function TrimFields()
    {
      $this->strDate = trim($this->strDate);
      $this->strNText = trim($this->strNText);
    }

    function Display()
    {
      RepMsg("CNewsBlock::Display()->Date: ".$this->strDate.";");
      RepMsg("CNewsBlock::Display()->NText: ".$this->strNText.";");
    }
    
    function PostprocessLigatures()
    {
      // We should replace ligatures in the following order:
      // ~
      $this->strDate = str_replace("~", "&nbsp;", $this->strDate);
      $this->strNText = str_replace("~", "&nbsp;", $this->strNText);
    }

  }
?>