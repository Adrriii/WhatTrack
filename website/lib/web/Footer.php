<?php

class Footer extends PagePart {

    public function setContent() {
        $this->content = file_get_contents(Page::$project_path . "lib/html/footer.html", FILE_USE_INCLUDE_PATH);
    }
}