<?php

class Nav extends PagePart {

    public function setContent() {
        $this->content = file_get_contents(Page::$project_path . "lib/html/nav.html", FILE_USE_INCLUDE_PATH);
        
        $this->replace("%NAVBAR%",file_get_contents(Page::$project_path . "lib/html/nav_bar.html", FILE_USE_INCLUDE_PATH));

        if(isset($_SESSION["user"])) {
            $this->replace("%NAVUSER%",file_get_contents(Page::$project_path . "lib/html/nav_online.html", FILE_USE_INCLUDE_PATH));
            $this->replace("%USERNAME%",$_SESSION["user"]->{"username"});
            $this->replace("%USERID%", $_SESSION["user"]->{"id"});
        } else {
            $this->replace("%NAVUSER%",file_get_contents(Page::$project_path . "lib/html/nav_offline.html", FILE_USE_INCLUDE_PATH));
        }
    }
}