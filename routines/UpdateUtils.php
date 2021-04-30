<?php

class UpdateUtils {
    function __construct($dm) {
        $this->dm = $dm;
    }

    function updateAllUsersDay() {
        $users = $this->dm->getUsers();

        foreach ($users as $user) {
            $id = $user["id"];

            $this->updateUserDay($id, Date("Y-m-d"));
        }

    }

    function updateUserDay($id, $day) {
        echo "DATE $day - USER $id\n";

        echo "-- PARSING USER\n";

        $userdata = $this->parseStandard("users/$id/$id-$day-standard.html");

        if($userdata[0]) {
            echo "UPDATING USER : ".$userdata[0]["username"]."\n";
            $this->dm->updateUser($id, $userdata[0]["username"]);


            echo "INSERTING USER RANKS\n";
            $this->dm->ReplaceRanksData($id, $day, 
                $userdata[1]["keytaps"],
                $userdata[1]["clicks"],
                $userdata[1]["download"],
                $userdata[1]["upload"],
                $userdata[1]["uptime"],
            );
        }

        echo "-- PARSING COMPUTERS\n";

        $computers = $this->parseComputers("users/$id/$id-$day-computers.html");

        echo "INSERTING COMPUTERS\n";
        foreach($computers as $computer) {
            $this->dm->InsertComputerIfNotExists($id, $computer["name"], $computer["os"]);
        };

        echo "INTERSECTING COMPUTERS\n";
        // Get the intersection of db computers and those in the file
        $existingComputers = [];
        
        foreach($this->dm->getUserComputers($id) as $existingComputer) {
            foreach($computers as $computer) {
                if($existingComputer["name"] == $computer["name"]) {
                    $computer["id"] = $existingComputer["id"];
                    array_push($existingComputers, $computer);
                    continue;
                }
            }
        }

        $computerKeys = [];

        echo "INSERTING COMPUTERS DATA\n";
        
        foreach($existingComputers as $cp) {
            $this->dm->ReplaceComputerData($day,
                $cp["id"],
                $cp["pulses"],
                $cp["keytaps"],
                $cp["clicks"],
                $cp["download"],
                $cp["upload"],
                $cp["uptime"],
            );

            $computerKeys[$cp["name"]] = $cp["id"];
        }
        echo "-- PARSING APPS\n";
        $apps_results = $this->parseApps($computerKeys,"users/$id/$id-$day-apps.html");

        echo "INSERTING APPS\n";
        $already = $this->dm->getAppNames();
        foreach($apps_results[0] as $app) {
            if(!in_array($app["name"],$already)) {
                $this->dm->InsertApp($app["name"]);
            }
        }

        echo "MERGING APPS\n";
        $merges = $this->dm->getMergedApps();
        $apps_merged = [];
        foreach($apps_results[1] as $app_data) {
            $apps_merged[$app_data["app"]][$app_data["computer"]] = $app_data;
        }
        
        foreach($merges as $app_merge) {
            // merge children inside parents
            if(!isset($apps_merged[$app_merge["child"]])) continue;
            foreach($apps_merged[$app_merge["child"]] as $computer_c => $app_c) {
                // check if the computer used in child app exists in parent app
                if(in_array($computer_c, array_keys($apps_merged[$app_merge["parent"]]))) {
                    // if yes, merge the data
                    $apps_merged[$app_merge["parent"]][$computer_c]["keytaps"] += $app_c["keytaps"];
                    $apps_merged[$app_merge["parent"]][$computer_c]["clicks"] += $app_c["clicks"];
                    $apps_merged[$app_merge["parent"]][$computer_c]["uptime"] += $app_c["uptime"];
                } else {
                    // if not, create the field in the parent
                    $apps_merged[$app_merge["parent"]][$computer_c] = $app_c;
                }
            }
        }

        echo "INSERTING APPS DATA\n";
        foreach($apps_merged as $app_by_computer) {
            foreach($app_by_computer as $app) {
                
                $this->dm->ReplaceAppsData($day,
                    $app["computer"],
                    $app["app"],
                    $app["keytaps"],
                    $app["clicks"],
                    $app["uptime"]
                );
            }
        }

        // clean potential remaining data
        $this->dm->clearChildAppData();
    }

    function parseStandard($filename) {

        $user = [];
        $stats = [];

        $doc = $this->getDomFromFilename($filename);

        if(!$doc) return [[],[]];

        $title = $doc->getElementsByTagName("title")->item(0);
        $user["username"] = trim(explode('|',$title->textContent)[0]);

        $desc = $doc->getElementById("emotify");
        $lines = explode("<br>", $doc->saveHTML($desc));

        $stats["keytaps"] = str_replace(',','',explode("rd",explode("nd",explode("st",explode("th",explode(':',$lines[8])[1])[0])[0])[0])[0]);
        $stats["clicks"] = str_replace(',','',explode("rd",explode("nd",explode("st",explode("th",explode(':',$lines[9])[1])[0])[0])[0])[0]);
        $stats["download"] = str_replace(',','',explode("rd",explode("nd",explode("st",explode("th",explode(':',$lines[10])[1])[0])[0])[0])[0]);
        $stats["upload"] = str_replace(',','',explode("rd",explode("nd",explode("st",explode("th",explode(':',$lines[11])[1])[0])[0])[0])[0]);
        $stats["uptime"] = str_replace(',','',explode("rd",explode("nd",explode("st",explode("th",explode(':',$lines[12])[1])[0])[0])[0])[0]);

        return [$user, $stats];
    }

    function parseComputers($filename) {
        $computers = [];

        $doc = $this->getDomFromFilename($filename);

        if(!$doc) return [];

        $computer_table = $doc->getElementsByTagName("table")->item(0);

        $skip = 2;
        foreach($computer_table->getElementsByTagName("tr") as $tr) {
            if($skip != 0) {
                $skip--;
                continue;
            }

            $try_title = $tr->getElementsByTagName("a")->item(0);

            if(!$try_title) {
                $try_title = $tr->getElementsByTagName("span")->item(0);
            }

            if($try_title) {
                $name = $try_title->textContent;
                $os = "windows";

                $tds = $tr->getElementsByTagName("td");

                $pulses = str_replace(',','',trim($tds->item(2)->textContent));
                $keytaps = str_replace(',','',trim($tds->item(3)->textContent));
                $clicks = str_replace(',','',trim($tds->item(4)->textContent));
                $download = str_replace(',','',trim($tds->item(5)->textContent));
                $upload = str_replace(',','',trim($tds->item(6)->textContent));
                $uptime = trim($tds->item(7)->textContent);

                if(!$uptime) $uptime = "0s";

                array_push($computers, [
                    "name" => $name,
                    "os" => $os,
                    "pulses" => $pulses,
                    "keytaps" => $keytaps,
                    "clicks" => $clicks,
                    "download" => $download,
                    "upload" => $upload,
                    "uptime" => $this->dm->uptimeToHours($uptime)
                ]);
            }
        }

        return $computers;
    }

    function parseApps($computers, $filename) {
        $apps_data = [];
        $apps = [];

        $doc = $this->getDomFromFilename($filename);

        if(!$doc) return [[],[]];

        $app_table = $doc->getElementById("app_table");
        $last_app = null;
        $skip = true;
        foreach($app_table->getElementsByTagName("tr") as $tr) {
            if($skip) {
                $skip = false;
                continue;
            }
            $try_title = $tr->getElementsByTagName("a")->item(0);

            if($try_title) {
                $title = $try_title->textContent;
                $last_app = $title;
                
                array_push($apps, [
                    "name" => $title
                    ]);
                    
                $tds = $tr->getElementsByTagName("td");

                try {
                    $computer = trim($tds->item(1)->textContent);
                    if(in_array($computer, array_keys($computers))) {
                        $uptime = trim($tds->item(2)->textContent);
                        $keytaps = str_replace(',','',trim($tds->item(3)->textContent));
                        $clicks = str_replace(',','',trim($tds->item(4)->textContent));

                        if(!$uptime) $uptime = "0s";

            
                        array_push($apps_data, [
                            "app" => $title,
                            "keytaps" => $keytaps,
                            "clicks" => $clicks,
                            "uptime" => $this->dm->uptimeToHours($uptime),
                            "computer" => $computers[$computer],
                        ]);
                    }
                } catch(Exception $e) {
                    continue;
                } 
            } elseif($last_app) {
                $tds = $tr->getElementsByTagName("td");
                
                if(!$tds->item(1)) continue;
                $computer = trim($tds->item(1)->textContent);
                $uptime = trim($tds->item(2)->textContent);
                $keytaps = str_replace(',','',trim($tds->item(3)->textContent));
                $clicks = str_replace(',','',trim($tds->item(4)->textContent));

                if(in_array($computer, array_keys($computers))) {
                    array_push($apps_data, [
                        "app" => $last_app,
                        "keytaps" => $keytaps,
                        "clicks" => $clicks,
                        "uptime" => $this->dm->uptimeToHours($uptime),
                        "computer" => $computers[$computer],
                    ]);
                }
            }
        }

        return [$apps, $apps_data];
    }

    function getDomFromFilename($filename) {
        $contents = file_get_contents($filename);

        if(!$contents) {
            echo "Warning: $filename is empty !\n";
            return null;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($contents);
        libxml_use_internal_errors(false);

        return $doc;
    }
}