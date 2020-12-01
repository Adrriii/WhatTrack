<?php

require_once '../../config.php';
require_once '../lib/DataManager.php';

$dm = new DataManager();

$raw = $dm->getUserComputersHist($_REQUEST["user"],$_REQUEST["data"]);

function cssColorForString($input)
{
  return '#' . substr(md5($input), 0, 6);
}

$datas = [];

$cs_seen = [];

foreach($raw as $d) {
    if(!in_array($d["computer"], array_keys($cs_seen))) {
        $id = count($datas);
        
        $cs_seen[$d["computer"]] = $id;

        $datas[$cs_seen[$d["computer"]]] = [
            "name" => $d["name"],
            "marker" => [
                "color" => cssColorForString($d["name"]."c"),
            ],
            "x" => [],
            "y" => [],
            "type" => "scatter",
        ];
    }

    $datas[$cs_seen[$d["computer"]]]["x"][] = $d["day"];

    if($_REQUEST["data"] == "download" || $_REQUEST["data"] == "upload") {
        $datas[$cs_seen[$d["computer"]]]["y"][] = $d[$_REQUEST["data"]] * (1024 * 1024);
    } else {
        $datas[$cs_seen[$d["computer"]]]["y"][] = $d[$_REQUEST["data"]];
    }
}

echo json_encode($datas);