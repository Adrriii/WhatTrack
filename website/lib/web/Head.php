<?php

class Head extends PagePart {

    public function setContent() {
        $this->content = file_get_contents(Page::$project_path . "lib/html/head.html", FILE_USE_INCLUDE_PATH);
        $this->replace("%TITLE%", Page::$project_title);
    }
}