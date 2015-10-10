<?php

namespace MainBundle\Utils;

use PDO;

class DB
{
    private $handler;

    private static $instance = null;

    private function __construct()
    {
        $host="192.168.88.254";
        $user="sa";
        $pwd="VFLFUFCRFH";
        $db_name = "DB_TEST";

        $this->handler = new PDO("dblib:host=$host;dbname=$db_name", $user, $pwd);
    }

    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new DB();
        }
        return self::$instance;
    }


    public function getUserData($username)
    {
        $query = 'SELECT * FROM CS_SHKET.USR WHERE BILL = ?';
        $stmt = $this->handler->prepare($query);
        if($stmt->execute([$username])){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}