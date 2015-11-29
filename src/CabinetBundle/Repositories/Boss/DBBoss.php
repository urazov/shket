<?php

namespace CabinetBundle\Repositories\Boss;

use MainBundle\Security\User;
use MainBundle\Utils\DB;
use PDO;

class DBBoss
{
    /**
     * @var DB
     */
    private static $db_instance;

    private static $instance = null;

    private function __construct()
    {
        self::$db_instance = DB::getInstance();
    }

    /**
     * @return DBBoss
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new DBBoss();
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

        $result = DB::getInstance()->getAll($query, [$user->getRoleId(), $user->getId()]);

        return $result;
    }

    public function getAllClasses(User $user)
    {
        $query = "select distinct cls.cls_id, cls.name
        , CAST(SUBSTRING(cls.name, 1, case when PATINDEX('%[^0-9]%',cls.name) = 0 then LEN(cls.name) else PATINDEX('%[^0-9]%',cls.name)-1 end) as int) as num_cls
        from cs_shket.user_in_scl_cls as uic, cs_shket.cls as cls
        where uic.scl_id in (
                select uic2.scl_id
                from cs_shket.user_in_scl_cls as uic2
                where uic2.usr_id = ?
                and uic2.del <> 1
        )
        and cls.cls_id = uic.cls_id
        and uic.del <> 1 and uic.cls_id <> 0 and cls.del <> 1
        order by num_cls asc";

        $result = DB::getInstance()->getAll($query, [$user->getId()]);

        return $result;
    }

    public function getAllPupil($parameters)
    {
        $query = 'select u.name, u.usr_id, cls.name as cls_name
            from cs_shket.usr u, cs_shket.USER_IN_SCL_CLS uc, cs_shket.cls cls
                where u.del <> 1 and uc.del <> 1
                    and u.usr_id = uc.usr_id
                    and uc.cls_id = cls.cls_id
                    and (uc.CLS_ID = ? or ? = -1)
                    and uc.scl_id = ?
                    and role_id = 1
                    order by u.name asc';

        $result = DB::getInstance()->getAll($query, [$parameters['class_id'], $parameters['class_id'], $parameters['school_id']]);
        return $result;
    }

    public function getFoodCountItog($parameters)
    {
        $query = "
            select SUM(cnt) as cnt, ADATE as date
            from cs_shket.RLS, cs_shket.USER_IN_SCL_CLS uc
            where (uc.CLS_ID = ? or ? = -1)
              and rls.usr_id = uc.usr_id and rls.del <> 1 and uc.del <> 1
              and ADATE >= ?
              and ADATE < ?
              and is_cplx = 1
              and is_bjt = 1
              group by ADATE
        ";

        $result = DB::getInstance()->getAll($query, [
            $parameters['class_id'], $parameters['class_id'], $parameters['date_from'], $parameters['date_to']
        ]);

        return $result;
    }

    public function getFoodCountForPupil($parameters)
    {
        $query = 'select SUM(CNT) as cnt, ADATE as date
                    from CS_SHKET.RLS
                    where USR_ID = ?
                      and del <> 1
                      and ADATE >= ?
                      and ADATE < ?
                      and is_cplx = 1
                      group by ADATE';

        $result = DB::getInstance()->getAll($query, [
            $parameters['pupil_id'], $parameters['date_from'], $parameters['date_to']
        ]);

        return $result;
    }

    public function getFoodCountForBjtPupil($parameters)
    {
        $query = 'select SUM(rls.CNT) as cnt, rls.ADATE as date
                    from CS_SHKET.RLS rls, cs_shket.meal meal
                    where rls.USR_ID = ?
                      and meal.meal_id = rls.meal_id
                      and meal.is_complex = 1
                      and rls.ADATE >= ?
                      and rls.ADATE < ?
                      and rls.is_cplx = 1 and rls.del <> 1 and rls.is_bjt = 1 and meal.del <> 1
                      group by ADATE';

        $result = DB::getInstance()->getAll($query, [
            $parameters['pupil_id'], $parameters['date_from'], $parameters['date_to']
        ]);

        return $result;
    }

    public function getFoodBjtCountItog($parameters)
    {
        $query = "
            select SUM(cnt) as cnt, ADATE as date
            from cs_shket.rls, cs_shket.USER_IN_SCL_CLS uc, cs_shket.meal meal
            where (uc.CLS_ID = ? or ? = -1)
              and rls.usr_id = uc.usr_id and rls.del <> 1 and uc.del <> 1 and meal.del <> 1
              and ADATE >= ?
              and ADATE < ?
              and is_cplx = 1
              and is_bjt = 1
              and meal.meal_id = rls.meal_id
              and meal.is_complex = 1
              group by ADATE
        ";

        $result = DB::getInstance()->getAll($query, [
            $parameters['class_id'], $parameters['class_id'], $parameters['date_from'], $parameters['date_to']
        ]);

        return $result;
    }
}