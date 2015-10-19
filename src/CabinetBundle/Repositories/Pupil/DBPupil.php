<?php

namespace CabinetBundle\Repositories\Pupil;

use MainBundle\Security\User;
use MainBundle\Utils\DB;
use PDO;

class DBPupil
{
    private static $db_instance;

    private static $instance = null;

    private function __construct()
    {
        self::$db_instance = DB::getInstance();
    }

    /**
     * @return DBPupil
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new DBPupil();
        }
        return self::$instance;
    }

    public static function getUserInfo(User $user)
    {
        $query = " select a.scl_id, a.cls_id, scl.name as scl_name, cls.name as cls_name, tr.NAME, tr.COST, tr.TRF_ID, tr.inf_bal, tr.inf_ent, tr.inf_eat
                   from cs_shket.USER_IN_SCL_CLS a, cs_shket.cls, cs_shket.scl
                      , (select trf.trf_id, trf.NAME, trf.COST, usr.USR_ID
                              , inf_bal, INF_ENT, INF_EAT
                           from CS_SHKET.TRf, CS_SHKET.USR
                          where usr.TRF_ID = trf.TRF_ID ) tr
                  where a.del <> 1 and cls.del <> 1 and scl.del <> 1
                    and a.usr_id = ? and a.scl_id = scl.scl_id
                    and a.scl_id = cls.scl_id
                    and a.cls_id = cls.cls_id
                    and tr.USR_ID = a.USR_ID";

        $result = DB::getInstance()->getAll($query, [$user->getId()], PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAvailableTarifs($parent_id)
    {
        $query = "Select distinct y.TRF_ID, y.NAME
                   from (Select  (case
                        when COUNT (u.USR_ID) over (partition by p.prt_id) > 2
                        then 3
                        else COUNT (u.USR_ID) over (partition by p.prt_id)
                        end) as trf_type
                       , p.PRT_ID
                       , u.USR_ID
                       , u.TRF_ID
                      from CS_SHKET.TRF t
                      inner join CS_SHKET.USR u on u.TRF_ID = t.TRF_ID
                      inner join CS_SHKET.PRT p on u.PRT_ID = p.PRT_ID) x
                   inner join (Select t.TRF_ID
                        , t.NAME
                        , (case
                         when t.NAME like '%50%'
                         then 3
                         when t.NAME like '%30%'
                         then 2
                         else 1
                           end) as trf_type
                       from CS_SHKET.TRF t where t.visible = 1 and trf_id <> '000000004') y on y.trf_type = x.trf_type
                   where x.PRT_ID = ?
                   order by y.NAME";

        $result = DB::getInstance()->getAll($query, [$parent_id], PDO::FETCH_ASSOC);
        return $result;
    }
}