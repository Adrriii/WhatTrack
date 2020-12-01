<?php

require_once '../../config.php';
require_once '../lib/DataManager.php';

$dm = new DataManager();

$raw = $dm->getUserRanksHist($_REQUEST["user"]);

$datas = [];

$datas[] = [
    "name" => "Keytaps",
    "x" => [],
    "y" => [],
    "type" => "scatter",
];
$datas[] = [
    "name" => "Clicks",
    "x" => [],
    "y" => [],
    "type" => "scatter",
];
$datas[] = [
    "name" => "Download",
    "x" => [],
    "y" => [],
    "type" => "scatter",
];
$datas[] = [
    "name" => "Upload",
    "x" => [],
    "y" => [],
    "type" => "scatter",
];
$datas[] = [
    "name" => "Uptime",
    "x" => [],
    "y" => [],
    "type" => "scatter",
];

foreach($raw as $d) {
    for($i = 0; $i<5; $i++) {
        $datas[$i]["x"][] = $d["day"];
    }
    
    $datas[0]["y"][] = $d["keytaps"];
    $datas[1]["y"][] = $d["clicks"];
    $datas[2]["y"][] = $d["download"];
    $datas[3]["y"][] = $d["upload"];
    $datas[4]["y"][] = $d["uptime"];
}

echo json_encode($datas);