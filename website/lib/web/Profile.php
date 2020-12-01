<?php

class Profile extends Page {

    public function __construct($userid) {
        $this->userid = $userid;
    }

    public function setContent() {
        $user = Page::$dm->getUser($this->userid);

        if(!$user) $user = Page::$dm->getUserByName($this->userid);

        if(!$user) die("Not found");
        $this->content = file_get_contents(Page::$project_path . "lib/html/profile.html", FILE_USE_INCLUDE_PATH);

        $this->replace("%USERNAME%", $user["username"]);
        $this->replace("%USERID%", $user["id"]);
    }
}