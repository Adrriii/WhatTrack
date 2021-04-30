<?php

abstract class Page extends PagePart {

    static public $project_path;
    static public $project_url;
    static public $project_title = "WhatTrack";
    
    static public $dm;

    public function getPage() {
        $head = new Head();
        $nav = new Nav();
        $foot = new Footer();
        return $head->get() . $nav->get() . $this->get() . $foot->get();
    }

}

abstract class PagePart {

    public $content;

    static public $dm;

    abstract public function setContent();

    public function replace($key, $content) {
        $this->content = str_replace($key, $content, $this->content);
    }

    public function get() {
        $this->setContent();
        $this->replace("%WEBSITE_LINK%", Page::$project_url);
        $this->replace("%WEBSITE_TITLE%", Page::$project_title);
        return $this->content;
    }

}