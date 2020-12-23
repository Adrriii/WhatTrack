<?php

class Profile extends Page {

    public function __construct($userid) {
        $this->userid = $userid;
    }

    public function setContent() {
        $user = Page::$dm->getUser($this->userid);
        $today = Page::$dm->getUserTodayStats($this->userid);

        if(!$user) $user = Page::$dm->getUserByName($this->userid);

        if(!$user) die("Not found");
        $this->content = file_get_contents(Page::$project_path . "lib/html/profile.html", FILE_USE_INCLUDE_PATH);

        $this->replace("%USERNAME%", $user["username"]);
        $this->replace("%USERID%", $user["id"]);

        $this->replace("%TODAY_KEYS%", number_format($today["keytaps"]));
        $this->replace("%TODAY_CLICKS%", number_format($today["clicks"]));
        $this->replace("%TODAY_DOWNLOAD%", Page::$dm->formatBytes($today["download"]*1000000));
        $this->replace("%TODAY_UPLOAD%", Page::$dm->formatBytes($today["upload"]*1000000));
        $this->replace("%TODAY_UPTIME%", Page::$dm->hoursToUptime($today["uptime"]));

        $this->setAppList();
    }

    public function setAppList() {
        $apps = Page::$dm->getAppsNames();

        $option = "<option value='%NAME%'>";
        $options = "";
        foreach($apps as $app) {
            $options .= str_replace("%NAME%", $app["name"], $option);
        }
        $this->replace("%APPLIST%", $options);
    }
}