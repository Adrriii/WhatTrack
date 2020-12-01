<?php

require_once("../website/lib/import.php");
require_once("UpdateUtils.php");

$dm = new DataManager();
$update = new UpdateUtils($dm);

$update->updateAllUsersDay();
