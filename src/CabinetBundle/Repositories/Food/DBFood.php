<?php

namespace CabinetBundle\Repositories\Food;

use MainBundle\Security\User;
use MainBundle\Utils\DB;

class DBFood
{
    private static $db_instance;

    private static $instance = null;

    private function __construct()
    {
        self::$db_instance = DB::getInstance();
    }

    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new DBFood();
        }
        return self::$instance;
    }

    public static function getUserInfo(User $user)
    {
        $query = "SELECT DISTINCT s.SCL_ID, s.NAME, c.cls_id, c.name
                  FROM CS_SHKET.USR u
                       INNER JOIN CS_SHKET.USER_IN_SCL_CLS uc on u.USR_ID = uc.USR_ID
                       INNER JOIN CS_SHKET.CLS c on c.SCL_ID = uc.SCL_ID AND c.CLS_ID = uc.cls_id
                       INNER JOIN CS_SHKET.scl s on s.SCL_ID = uc.SCL_ID
                  WHERE u.ROLE_ID = ? and u.del <> 1 and uc.del<> 1 and c.del <> 1 and s.del <> 1
                  AND u.USR_ID = ?";

        $result = self::$db_instance->getAll($query, [$user->getRoleId(), $user->getId()]);

        return $result;
    }

}