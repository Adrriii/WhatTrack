<?php

require_once '../../config.php';
require_once '../lib/DataManager.php';

$dm = new DataManager();

$raw = $dm->getUserAppsHist($_REQUEST["user"],$_REQUEST["data"]);

function cssColorForString($input)
{
  return '#' . substr(md5($input), 0, 6);
}

$datas = [];

$apps_seen = [];
$last_day = null;
$day_count = 0;

foreach($raw as $d) {
    if(!in_array($d["app"], array_keys($apps_seen))) {
        $id = count($datas);
        
        $apps_seen[$d["app"]] = $id;

        $datas[$apps_seen[$d["app"]]] = [
            "name" => $d["app"],
            "marker" => [
                "color" => cssColorForString($d["app"]."c"),
            ],
            "x" => [],
            "y" => [],
            "type" => "scatter",
        ];
    }

    if($d["day"] != $last_day) {
        $day_count = 0;
        $last_day = $d["day"];
    }
    $day_count++;
    if($day_count > 10) continue;

    $datas[$apps_seen[$d["app"]]]["x"][] = $d["day"];
    $datas[$apps_seen[$d["app"]]]["y"][] = $d["d"];
}

echo json_encode($datas);