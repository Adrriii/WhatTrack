<?php

require_once("../website/lib/import.php");
require_once("UpdateUtils.php");

$dm = new DataManager();
$update = new UpdateUtils($dm);

$users = $dm->getUsers();

echo "--Updating ".count($users)." users\n";

foreach ($users as $user) {
    $id = $user["id"];

    exec("mkdir users/$id 2> /dev/null");   

    echo "User $id : ";
    file_put_contents("users/$id/$id-".date("Y-m-d")."-apps.html",      getHTML("https://whatpulse.org/tabs/user/apps/$id/?"));
    echo "apps, ";
    file_put_contents("users/$id/$id-".date("Y-m-d")."-computers.html", getHTML("https://whatpulse.org/tabs/user/computers/$id/?"));
    echo "computers, ";
    file_put_contents("users/$id/$id-".date("Y-m-d")."-standard.html",  getHTML("https://whatpulse.org/stats/users/$id/standard/"));
    echo "standard.\n";
} 

$update->updateAllUsersDay();

function getHTML($address) {
    $ch = curl_init(); 

    curl_setopt($ch, CURLOPT_URL, $address); 

    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 

    $content = curl_exec ($ch); 

    curl_close ($ch); 

    return $content;
}