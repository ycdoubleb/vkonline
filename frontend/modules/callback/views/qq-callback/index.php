<?php
    require_once("/../OAuths/qqAPI/qqConnectAPI.php");
    $qc = new QC();
    $qc->qq_login();
    var_dump($qc);
    exit;