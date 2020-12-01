<?php

require_once '../../config.php';
require_once '../lib/DataManager.php';

$dm = new DataManager();

$raw = $dm->getUserTotalHist($_REQUEST["user"],$_REQUEST["data"]);

$data = [
    "x" => [],
    "y" => [],
    "type" => "scatter",
];

foreach($raw as $d) {
    $data["x"][] = $d["day"];
    if($_REQUEST["data"] == "download" || $_REQUEST["data"] == "upload") {
        $data["y"][] = $d["d"] * (1024 * 1024);
    } else {
        $data["y"][] = $d["d"];
    }
}

echo json_encode([$data]);