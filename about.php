<?php
    extract($_SERVER);
    extract($_ENV);
    extract($_GET);
    extract($_POST);
    extract($_REQUEST);

    include "common.php";
    if ($bDebugEnabled)
        error_reporting(E_ALL);

    if (!isset($section))
        $section = "0";

    $CurrentMenuItem = $mnuAbout;
    PutPageHeader($arrMenuFiles, $arrMenuTitles, $arrMenuColors, $CurrentMenuItem, $arrCat, $strSLU, $section);
?>
    <div class="Page" style="text-align: right">
        <div style="padding-bottom: 5px; padding-top: 5px;">
            <span class="PlainText" style="width: 300px; display: inline-block;">
                � �� ����� ��� ��� ���������� ���,<br />
                ����� ��� ���� &#8212; �� �������� �����,<br />
                � �� ���� ��� ��������, ��������, ��������,<br />
                ��� �����, ��� ������, � ��� ����� ���...
            </span>
        </div>
        <span class="PlainText" style="width: 120px; display: inline-block; font-style: italic;">
            �.&nbsp;������
        </span>
    </div><!-- page top -->
<?php
    DisplayPage($CurrentMenuItem, $arrCat, $section);
?>
    <div class="Page">
        <p class="Subtitle">�� ������� �������</p>
        <div class="PlainTextFP">
            ������ ��� ������� ����� 2003 ���� ����������� �� ���� �������� (����� ��� ��������������)
            �������� � �������� ������������ (DMVN&nbsp;Corp). � ��� ��� ����� ���� ������, ������ ��������
            ������ �� ����������. ���� ���� ����� � ���� �� ��������� �������, �� ����� �����������
            � ������ ������ (���������� ��� �� ������ ���������� ������ �������� ��� ��� ����������
            � �������). �������, �� ������� ����� �������� �������� ���������� ����������� ������
            ����������� �� ���� ���������� �������� ��������� �������. ����� ��������, ���
            �������� ��������������� ���������� ����������� ������ �� ����������� ������� ��
            ������ ������. ����� ������ � ������ ���������� ��������� (������ �������� ��
            �� ������ ������������� ��������� �� ����� �������, �������, � ���������,
            �� ��, ��� �����������, ����� ��������).<br />
            ���� � ��� ���� ����������� ��������� � ����, ��� ���� ������� �����������:<br />
            <ul class="PlainTextFP">
                <li><strong>Mail:</strong> <?php echo llink('mailto:'.$strDMVNMail, $strDMVNMail); ?><br />
                    ������ ��� �� ���� ������������ ��� ��������, ��������� � ��������� �����, �����������
                    ����� ���������� �&nbsp;�.&nbsp;�. ��������� �������� �� ��� �������������� ������ �����.
                </li>
                <li><strong>Telegram:</strong> @dichlofos<br />
                    ��� ������ �� ����������� ������������. �� �������: ������ &#8212; � �����!
                </li>
            </ul>
        </div>
    </div><!-- page bottom -->
<?php
    PutPageFooter($strDMVNMail);
