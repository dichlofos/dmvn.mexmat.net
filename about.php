<?php
  extract($_SERVER);
  extract($_ENV);
  extract($_GET);
  extract($_POST);
  extract($_REQUEST);
    
  include "common.php";
  if ($bDebugEnabled) error_reporting(E_ALL);

  if (!isset($section)) $section = "0";

  $CurrentMenuItem = $mnuAbout;
  PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
?>
	<table class="Page" summary="�������">
		<tr>
			<td>&nbsp;</td>
			<td colspan="2" class="PlainText" style="width: 300px">
				� �� ����� ��� ��� ���������� ���,<br />
				����� ��� ���� &#8212; �� �������� �����,<br />
				� �� ���� ��� ��������, ��������, ��������,<br />
				��� �����, ��� ������, � ��� ����� ���...<br />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="width: 200px">&nbsp;</td>
			<td class="PlainText" style="width: 100px; font-style: italic">�.&nbsp;������</td>
		</tr>
	</table><!-- page top -->
<?php
  DisplayPage($CurrentMenuItem, $arrCat, $section);
?>
	<div class="Page">
		<p class="Subtitle">�� ������� �������</p>
		<div class="PlainTextFP">
			������ ��� ������� ����� 2003 ���� ����������� �� ���� �������� (����� ��� ��������������)
			�������� � �������� ������������ (DMVN Corp). � ��� ��� ����� ���� ������, ������ ��������
			������ �� ����������. ���� ���� ����� � ���� �� ��������� �������, �� ����� ����
			� ������ (���������� ��� �� ������ ���������� �������� �������� ��� ��� ����������
			� �������). �������, �� ������� ����� �������� �������� ���������� ����������� ������
			����������� �� ���� ���������� �������� ��������� �������. ����� ��������, ���
			�������� ��������������� ���������� ����������� ������ �� ����������� ������� ��
			������ ������. ����� ������ � ������ ���������� ��������� (������ ����� ��������,
			��� �������� �� �� ������ ������������� ��������� �� ����� �������, �������, � ���������,
			�� ��, ��� �����������, ����� ��������).<br />
			���� � ��� ���� ����������� ��������� � ����, ��� ���� ������� �����������:<br />
			<ul class="PlainTextFP">
				<li><strong>Mail:</strong> <?php echo llink('mailto:'.$strDMVNMail, $strDMVNMail); ?><br /> 
					������ ��� �� ���� ������������ ��� ��������, ��������� � ��������� �����, �����������
					����� ���������� �&nbsp;�.&nbsp;�. ��������� �������� �� ��� �������������� ������ �����.
				</li>
				<li><strong>ICQ:</strong> 2-4-4:6-3-3:8-1-7<br />
					���� ���� ����� ���������, ������ ����� ������ ��� ������, �� �����������, ��� ��������-��� �������� ���
					�������� �� ������� ������ (�� ����� ������� �� �� ������ ������������, � ���� ��������� �� ������ �� ���).
				</li>
			</ul>
		</div>
	</div><!-- page bottom -->
<?php
  PutPageFooter($strDMVNMail);
?>
