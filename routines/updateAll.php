<?php

require_once("../website/lib/import.php");
require_once("UpdateUtils.php");

$dm = new DataManager();
$update = new UpdateUtils($dm);

$users = $dm->getUsers();
$day = Date("Y-m-d");

while(strtotime($day) >= strtotime("2020-08-28")) { // data start date

    foreach ($users as $user) {
        $id = $user["id"];

        $update->updateUserDay($id, $day);
    }

    $day = Date("Y-m-d", strtotime($day) - 86400);
}
