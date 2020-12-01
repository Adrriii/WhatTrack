<?php 
class Database {

    private $pdo;
    private $result;

    public function __construct(){
        global $APP_CONFIG;
        try {            
            $this->pdo = new PDO("mysql:host=".$APP_CONFIG["db"]["host"].";dbname=".$APP_CONFIG["db"]["db"].";charset=utf8", $APP_CONFIG["db"]["user"], $APP_CONFIG["db"]["passwd"]);
        } catch(Exception $e){
            die("Could not access database. Tell Adri asap ! $e");
        }
    } 

    public function last() {
        return $this->pdo->lastInsertId();
    }

    public function query($sql, $opt = null, $mode = PDO::FETCH_BOTH){
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute($opt);
            $this->result = $query->fetchAll($mode);
            return true;
        } catch(Exception $e){
            echo "SQLException: $e";
            return false;
        }
    }

    public function fast($sql, $opt = null, $i = null, $mode = PDO::FETCH_BOTH, $echo_query = false){
        
        if($echo_query) {
            $str = $sql;
            if($opt)
                foreach($opt as $i => $s)
                    $str = str_replace($i, $s, $str);
                
            echo "$str\n";
        }
        $this->query($sql, $opt, $mode);
        return $this->result;
    }
}