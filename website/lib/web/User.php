<?php

class User extends Page {

    public $user;

    public function setContent() {

        $this->user = Page::$dm->getUser($_REQUEST["u"]);

        if(!$this->user) {
            $this->content = file_get_contents(Page::$project_path . "lib/html/user_notfound.html", FILE_USE_INCLUDE_PATH);
            return;
        }

        $this->content = file_get_contents(Page::$project_path . "lib/html/user.html", FILE_USE_INCLUDE_PATH);

        $this->replace("%USERNAME%", $this->user["username"]);
        
        $scoreContent = "";
        $phase = 0;

        foreach(Page::$dm->GetUserStages($this->user["id"]) as $score) {
            if($phase != $score["phase_number"]) {
                $phase = $score["phase_number"];

                $scoreContent .= "<h4 class='phase_separator'>Phase $phase : ".$score["phase"]."</h4>";
            }
            $scoreContent .= (new NewScore($score, $score, 1))->get();
        }

        if(!$scoreContent) {
            $scoreContent .= "<h4 class='phase_separator'>Aucun score pour le moment !</h4>";
        }

        if(isset($_SESSION["user"]) && $this->user["id"] == $_SESSION["user"]->{'id'}) {
            $onlinepanel = "<div class='refresh_scores'>
                                <h4>Rafraîchir ses scores</h4>
                                <form action='/new_scores.php'>
                                <input type='text' name='match_mp' placeholder='Match MP URL...'>
                                <input type='submit'>
                                </form>
                            </div>";

            $this->replace("%ONLINE%",$onlinepanel);
        } else {
            $this->replace("%ONLINE%","");
        }

        $this->replace("%SCORES%", $scoreContent);
    }
}