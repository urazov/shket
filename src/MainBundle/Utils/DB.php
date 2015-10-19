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


    public function getFirst($sql, array $params, $fetch_type = PDO::FETCH_ASSOC)
    {
        $idx = 1;
        $this->stmt = $this->handler->prepare($sql);
        foreach($params as $param){
            $this->bindParam($idx, $param);
            $idx++;
        }
        if($this->stmt->execute()){
            return $this->fetchFirst($fetch_type);
        }
        return [];
    }

    public function getAll($sql, array $params, $fetch_type = PDO::FETCH_ASSOC)
    {
        $idx = 1;
        $this->stmt = $this->handler->prepare($sql);
        foreach($params as $param){
            $this->bindParam($idx, $param);
            $idx++;
        }
        if($this->stmt->execute()){
            return $this->fetchAll($fetch_type);
        }
        return [];
    }

    /**
     * @param $username
     * @return array|bool
     */
    public function getUserData($username)
    {
        $query = "select a.usr_id, a.pass, a.name as usr_name, isnull(a.limit, -1) as limit,
                  a.role_id, p.name as prt_name, isnull(p.tlph, a.tlph) as tlph, isnull(p.email, a.email) as email
                  , (select sum(summ) from cs_shket.blnc where del <> 1 and usr_id = a.usr_id ) as blnc, p.prt_id
                  from cs_shket.usr a left join cs_shket.prt p on a.prt_id = p.prt_id and p.del <> 1
                    where a.del <> 1
                    and a.bill = ?";

        return $this->getFirst($query, [$username]);
    }

    private function fetchFirst($FLAG = null)
    {
        $utf_values = [];
        $result = $this->stmt->fetch($FLAG);
        if($result){
            foreach($result as $key => $value){
                $utf_values[$key] = iconv("windows-1251", "utf-8", $value);
            }
            return $utf_values;
        }
        return [];
    }

    private function fetchAll($FLAG = null)
    {
        $utf_result = [];
        $utf_values = [];
        $result = $this->stmt->fetchAll($FLAG);
        if($result){
            foreach($result as $idx => $values){
                foreach($values as $key => $value){
                    $utf_values[$key] = iconv("windows-1251", "utf-8", $value);
                }
                $utf_result[$idx] = $utf_values;
            }
            return $utf_result;
        }
        return [];
    }

    private function bindParam($num, $value)
    {
        $p = iconv("utf-8", "windows-1251", $value);
        $this->stmt->bindParam($num, $p);
    }
}