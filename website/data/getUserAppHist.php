<?php

require_once '../../config.php';
require_once '../lib/DataManager.php';

$dm = new DataManager();

$raw = $dm->getUserAppHist($_REQUEST["user"],$_REQUEST["app"]);

$types = ["keytaps", "clicks", "uptime"];
$datas = [];

foreach($types as $t) {
    $datas[$t] = [
        "name" => $t,
        "x" => [],
        "y" => [],
        "type" => "scatter",
    ];
}

foreach($raw as $d) {
    foreach($types as $t) {
        $datas[$t]["x"][] = $d["day"];
        $datas[$t]["y"][] = $d["$t"];
    }
}

echo json_encode($datas);