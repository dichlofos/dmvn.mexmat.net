<?php
    extract($_SERVER);
    extract($_ENV);
    extract($_GET);
    extract($_POST);
    extract($_REQUEST);

    include "common.php";
    error_reporting(E_ALL);

    $nCounter = 0;
    $nFailed = 0;

    function CheckTrue($bCond) {
        global $nCounter;
        global $nFailed;
        $nCounter++;
        if ($bCond)
            return;
        $nFailed++;
        echo "Test $nCounter failed.\r\n";
    }

    function CheckFalse($bCond) {
        CheckTrue(!$bCond);
    }

    CheckTrue(bEMailValid('test@com.com'));
    CheckTrue(bEMailValid('test1@com.com'));
    CheckTrue(bEMailValid('test_2@com.com'));
    CheckTrue(bEMailValid('victorStar0dub@c_om.com'));
    CheckFalse(bEMailValid('#victorStar0dub@c_om.com'));
    CheckFalse(bEMailValid('autoZ@'));
    CheckFalse(bEMailValid('autoZ'));
    CheckFalse(bEMailValid('.'));
    CheckFalse(bEMailValid('@'));
    CheckFalse(bEMailValid('au..to@test.com$'));

    if ($nFailed)
        exit(1);
