<?php

require_once("../website/lib/import.php");
require_once("UpdateUtils.php");

$dm = new DataManager();
$update = new UpdateUtils($dm);

$users = $dm->getUsers();

foreach ($users as $user) {
    $id = $user["id"];

    $update->updateUserDay($id, Date("Y-m-d"));
}

