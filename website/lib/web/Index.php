<?php

class Index extends Page {

    public function setContent() {
        $this->content = file_get_contents(Page::$project_path . "lib/html/index.html", FILE_USE_INCLUDE_PATH);
    }
}