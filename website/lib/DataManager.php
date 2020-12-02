<?php

require_once("Database.php");

class DataManager {

    public $database;

    private $table_user = "`user`";

    public function __construct() {

        $this->database = new Database();
    }

    public function fast($req, $opt = null, $echo_query = false) {
        if($echo_query)
            return $this->database->fast($req, $opt, null, PDO::FETCH_BOTH, true);
            
        return $this->database->fast($req, $opt);
    }

    function httpGet($url, $headers = null) {
        $curl = curl_init($url);
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getUser($userid) {
        $opt = [
            ":id" => $userid,
        ];

        $req = "SELECT * FROM ".$this->table_user." WHERE id = :id";
        $res = $this->fast($req,$opt);
        if(isset($res[0])) return $res[0];
        return null;
    }

    public function getUserByName($username) {
        $opt = [
            ":username" => $username,
        ];

        $req = "SELECT * FROM ".$this->table_user." WHERE username = :username";
        $res = $this->fast($req,$opt);
        if(isset($res[0])) return $res[0];
        return null;
    }

    public function getUsers() {
        $req = "SELECT * FROM ".$this->table_user;
        $res = $this->fast($req);
        return $res;
    }

    public function getAppNames() {
        $req = "SELECT * FROM app";
        $appnames = [];
        foreach($this->fast($req) as $app) {
            $appnames[] = $app["name"];
        }
        return $appnames;
    }

    public function updateUser($id, $username) {
        $opt = [
            ":id" => $id,
            ":username" => $username,
        ];

        $req = "UPDATE user SET username = :username WHERE id = :id";
        $this->fast($req, $opt);
    }

    public function InsertComputerIfNotExists($user, $name, $os) {
        $computers = $this->getUserComputers($user);
        foreach($computers as $cp) {
            if($cp["name"] == $name) {
                return;
            }
        }

        $opt = [
            ":user" => $user,
            ":os" => $os,
            ":name" => $name,
        ];

        $req = "INSERT INTO computer (user, name, os) VALUES (:user, :name, :os)";
        return $this->fast($req, $opt);
    }

    public function InsertApp($name) {
        $opt = [
            ":name" => $name
        ];

        $req = "INSERT INTO app (name) VALUES (:name)";

        $this->fast($req, $opt);
    }

    public function ReplaceComputerData($day, $computer, $pulses, $keytaps, $clicks, $download, $upload, $uptime) {
        $opt = [
            ":day" => $day,
            ":computer" => $computer,
            ":pulses" => $pulses,
            ":keytaps" => $keytaps,
            ":clicks" => $clicks,
            ":download" => $this->convertToMBytes($download),
            ":upload" => $this->convertToMBytes($upload),
            ":uptime" => $uptime,
        ];
        
        $req = "REPLACE INTO computer_stats (day,computer,pulses,keytaps,clicks,download,upload,uptime)
        VALUES (:day, :computer, :pulses, :keytaps, :clicks, :download, :upload, :uptime)";

        $this->fast($req, $opt);
    }

    public function ReplaceRanksData($user, $day, $keytaps, $clicks, $download, $upload, $uptime) {
        $opt = [
            ":user" => $user,
            ":day" => $day,
            ":keytaps" => $keytaps,
            ":clicks" => $clicks,
            ":download" => $download,
            ":upload" => $upload,
            ":uptime" => $uptime,
        ];
        
        $req = "REPLACE INTO user_ranks (day,user,keytaps,clicks,download,upload,uptime)
        VALUES (:day, :user, :keytaps, :clicks, :download, :upload, :uptime)";

        $this->fast($req, $opt);
    }

    public function ReplaceAppsData($day, $computer, $app, $keytaps, $clicks, $uptime) {

        if($uptime<1) return;

        $opt = [
            ":day" => $day,
            ":computer" => $computer,
            ":app" => $app,
            ":keytaps" => $keytaps,
            ":clicks" => $clicks,
            ":uptime" => $uptime,
        ];

        $req = "REPLACE INTO app_stats (day,computer,app,keytaps,clicks,uptime)
        VALUES (:day,:computer,:app,:keytaps,:clicks,:uptime)";

        $this->fast($req, $opt);
    }

    public function getUserComputers($userid) {
        $opt = [
            ":id" => $userid,
        ];

        $req = "SELECT * FROM computer WHERE user = :id";
        return $this->fast($req, $opt);
    }

    public function getUserTotalHist($id,$data) {
        $opt = [
            ":id" => $id,
        ];
        
        switch($data) {
            case "keytaps":
            case "clicks":
            case "uptime":
            case "download":
            case "upload":
                break;
            default:
                $data = "keytaps";
        }

        $req = "SELECT day,SUM($data) as d FROM computer_stats WHERE computer IN (SELECT id FROM computer WHERE user = :id) GROUP BY day";

        return $this->fast($req, $opt);
    }

    public function getUserRanksHist($id) {
        $opt = [
            ":id" => $id,
        ];

        $req = "SELECT * FROM user_ranks WHERE user = :id";
        return $this->fast($req, $opt);
    }

    public function getUserAppsHist($id, $data) {
        $opt = [
            ":id" => $id,
        ];

        switch($data) {
            case "keytaps":
            case "clicks":
            case "uptime":
            case "download":
            case "upload":
                break;
            default:
                $data = "keytaps";
        }

        $req = "SELECT day,app,SUM($data) as d FROM app_stats WHERE computer IN (SELECT id FROM computer WHERE user = :id) GROUP BY day,app ORDER BY day DESC, d DESC";

        return $this->fast($req, $opt);
    }

    public function getUserComputersHist($id) {
        $opt = [
            ":user" => $id,
        ];

        $req = "SELECT * FROM computer as c, computer_stats as cs WHERE c.id = cs.computer AND user = :user ORDER BY day DESC";

        return $this->fast($req, $opt);
    }

    public function getMergedApps() {
        $req = "SELECT * FROM app_merge";
        return $this->fast($req);
    }

    public function clearChildAppData() {
        $req = "DELETE FROM app_stats WHERE app in (SELECT child FROM app_merge)";
        $this->fast($req);
    }

    function convertToMBytes(string $from): ?int {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from,-2));
    
        //B or no suffix
        if(is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }
    
        $exponent = array_flip($units)[$suffix] ?? null;
        if($exponent === null) {
            return null;
        }
    
        return ($number * (1024 ** $exponent)) / (1024 * 1024);
    }

    function uptimeToHours($date) {
        $date = strrev($date);
    
        $i = 0;
        $nb = strlen($date);
    
        $year = "";
        $week = "";
        $day = "";
        $hour = "";
    
        $case = "autre";
        while ($i < $nb) {
            $char = substr($date, $i, 1);
            $i++;
    
            if ($char == "s" || $char == "m" || $char == "h" || $char == "d" || $char == "w" || $char == "y") {
                switch ($char) {
                    case "h":
                        $case = "hour";
                        break;
                    case "d":
                        $case = "day";
                        break;
                    case "w":
                        $case = "week";
                        break;
                    case "y":
                        $case = "year";
                        break;
                    default:
                        $case = "skip";
                }
            } else {
                switch ($case) {
                    case "hour":
                        $hour = $char . $hour;
                        break;
                    case "day":
                        $day = $char . $day;
                        break;
                    case "week":
                        $week = $char . $week;
                        break;
                    case "year":
                        $year = $char . $year;
                        break;
                }
            }
        }
    
        return (intval($year) * 52.1429 * 7 * 24) + (intval($week) * 7 * 24) + (intval($day) * 24) + intval($hour);
    }
}