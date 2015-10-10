<?php

namespace MainBundle\Utils;

use PDO;
use PDOStatement;

class DB
{
    const HOST = "192.168.88.254";
    const USER = "sa";
    const PASS = "VFLFUFCRFH";
    const DB_NAME = "DB_TEST";

    private $handler;

    /**
     * @var PDOStatement
     */
    private $stmt;

    private static $instance = null;

    private function __construct()
    {
        $this->handler = new PDO("dblib:host=". self::HOST .";dbname=" . self::DB_NAME, self::USER, self::PASS);
    }

    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new DB();
        }
        return self::$instance;
    }


    public function getFirst($sql, array $params)
    {
        $this->stmt = $this->handler->prepare($sql);
        foreach($params as $param){
            $this->bindParam(1, $param);
        }
        if($this->stmt->execute()){
            return $this->fetchFirst(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function getAll($sql, array $params)
    {
        $this->stmt = $this->handler->prepare($sql);
        foreach($params as $param){
            $this->bindParam(1, $param);
        }
        if($this->stmt->execute()){
            return $this->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * @param $username
     * @return array|bool
     */
    public function getUserData($username)
    {
        $query = 'SELECT * FROM CS_SHKET.USR WHERE BILL = ?';
        return $this->getFirst($query, [$username]);
    }

    private function fetchFirst($FLAG = null)
    {
        $utf_values = [];
        $result = $this->stmt->fetch($FLAG);
        foreach($result as $key => $value){
            $utf_values[$key] = iconv("windows-1251", "utf-8", $value);
        }
        return $utf_values;
    }

    private function fetchAll($FLAG = null)
    {
        $utf_result = [];
        $utf_values = [];
        $result = $this->stmt->fetchAll($FLAG);
        foreach($result as $idx => $values){
            foreach($values as $key => $value){
                $utf_values[$key] = iconv("windows-1251", "utf-8", $value);
            }
            $utf_result[$idx] = $utf_values;
        }
        return $utf_result;
    }

    private function bindParam($num, $value)
    {
        $p = iconv("utf-8", "windows-1251", $value);
        $this->stmt->bindParam($num, $p);
    }
}