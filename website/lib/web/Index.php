<?php

class Index extends Page {

    public function setContent() {
        $this->content = file_get_contents(Page::$project_path . "lib/html/index.html", FILE_USE_INCLUDE_PATH);
        
        $lines = "";
        foreach(Page::$dm->getUsersCurrentStats() as $user) {
            $lines .= $this->addUserLine($user);
        }

        $this->replace("%USER_LIST%", $lines);
    }

    public function addUserLine($user) {
        $html = file_get_contents(Page::$project_path . "lib/html/index_userline.html", FILE_USE_INCLUDE_PATH);

        $html = str_replace("%ID%", $user["id"], $html);
        $html = str_replace("%USERNAME%", $user["username"], $html);
        $html = str_replace("%KEYS%", number_format($user["keytaps"]), $html);
        $html = str_replace("%CLICKS%", number_format($user["clicks"]), $html);
        $html = str_replace("%DOWN%", Page::$dm->formatBytes($user["download"]*1000000), $html);
        $html = str_replace("%UP%", Page::$dm->formatBytes($user["upload"]*1000000), $html);
        $html = str_replace("%UPTIME%", Page::$dm->hoursToUptime($user["uptime"]), $html);

        return $html;
    }
}