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

    public function getPupilPitanie($parameters)
    {
        $query = "with x as (
            Select convert(nvarchar, CAST(r.adate as datetime), 104) as dt
            , m.NAME
            , r.COST
            , r.CNT
            , (case when r.IS_BJT = 1 then 0 else r.COST*r.CNT end) as price_comm
            , (case when r.IS_BJT = 1 then r.COST_BJT*r.cnt else 0 end) as add_price_bjt
            , r.adate
            from CS_SHKET.RLS r
             inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
             inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID
             inner join CS_SHKET.USER_IN_SCL_CLS uc on r.USR_ID = uc.USR_ID
             inner join CS_SHKET.USR u on r.USR_ID = u.USR_ID
             where r.ADATE between ? and ?
               and u.usr_id = ?
               and r.del <> 1 and dm.del <> 1 and m.del <> 1 and uc.del<> 1 and u.del <> 1
       )

            Select y.dt
                   , upper(substring(y.name, 1, 1)) + lower(substring(y.name, 2, LEN(y.name))) as name
                   , y.price as price
                   , y.cnt as cnt
                   , y.price_comm as price_comm
                   , y.add_price_bjt as add_price_bjt
              from ( Select x.dt as dt
                        , x.NAME as name
                        , x.COST as price
                        , x.CNT as cnt
                        , x.price_comm as price_comm
                        , x.add_price_bjt as add_price_bjt
                        , x.adate
                        from x

                     UNION

                     Select distinct 'TOTAL:' as dt
                     , null as name
                     , null as price
                     , isnull(SUM(x.cnt), 0) as cnt
                     , isnull(SUM(x.price_comm), 0) as price_comm
                     , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
                     , '01-01-1999' as adate
                     from x
                  ) y
             order by y.adate desc, y.name";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $date_from, $date_to, $parameters['user_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getPupilEnter($parameters)
    {
        $query = "SELECT CONVERT(nvarchar, ddate, 104) as dt, CONVERT(nvarchar, ddate, 108) as tm, [DIRECT] as direct
             FROM CS_SHKET.ENT
            where USR_ID = ?
               and cast(DDATE as date) between ? and ?
             order by ddate desc";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $parameters['user_id'], $date_from, $date_to
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getAllType($parameters)
    {
        $query = "select acr_id, name
               from cs_shket.acr
             where exists ( select null from cs_shket.blnc where acr_id = acr.acr_id and usr_id = ?)";

        $result = DB::getInstance()->getAll($query, [$parameters['user_id']]);

        return $result;
    }

    public function getPupilMoney($parameters)
    {
        $query = "select convert(char(23), DDATE, 104), ACR.NAME
                         , case when b.SUMM < 0 then abs(b.SUMM) else null end
                         , case when b.SUMM >= 0 then b.SUMM else null end
                         , (select sum(BLNC.SUMM)
                              from CS_SHKET.BLNC
                             where USR_ID = b.USR_ID
                               and DDATE <= b.DDATE and del <> 1)
                      from CS_SHKET.BLNC b, CS_SHKET.ACR
                     where b.USR_ID = ?
                       and B.ACR_ID = ACR.ACR_ID
                       and (acr.acr_id = ? or ? = -1)
                       and cast(b.ddate as date) between ? and ?
                       and acr.del <> 1
                       and b.del <> 1
                  order by DDATE desc, ABS(b.summ)";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $parameters['user_id'], $parameters['type'], $parameters['type'], $date_from, $date_to
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function updateLimit($user_id, $new_limit)
    {
        $query = "update cs_shket.usr set mdate = ?, limit = ?, upd = 1 where usr_id = ?";
        DB::getInstance()->getFirst($query, [date('Y-m-d H:i:s'), $new_limit, $user_id]);
    }

    public function updateInfo($parameters)
    {
        $query = "update cs_shket.usr set mdate = ?, limit = ?, name = ?, new_trf_id = ?, upd = 1 where usr_id = ?";
        DB::getInstance()->getFirst($query, [
            date('Y-m-d H:i:s'),
            $parameters['limit'],
            $parameters['pupil_name'],
            $parameters['tarif_id'],
            $parameters['user_id']
        ]);

        $query = "update cs_shket.prt set mdate = ?, name = ?, tlph = ?, email = ?, upd = 1 where prt_id = ?";
        DB::getInstance()->getFirst($query, [
            date('Y-m-d H:i:s'),
            $parameters['parent_name'],
            $parameters['phone'],
            $parameters['email'],
            $parameters['parent_id']
        ]);

    }

    public function getUserInfoById($user_id)
    {
        $query = " select usr.name as user_name, a.scl_id, a.cls_id, scl.name as scl_name, cls.name as cls_name, tr.NAME, tr.COST, tr.TRF_ID, tr.inf_bal, tr.inf_ent, tr.inf_eat
                   from cs_shket.USER_IN_SCL_CLS a, cs_shket.cls, cs_shket.scl, cs_shket.usr
                      , (select trf.trf_id, trf.NAME, trf.COST, usr.USR_ID
                              , inf_bal, INF_ENT, INF_EAT
                           from CS_SHKET.TRf, CS_SHKET.USR
                          where usr.TRF_ID = trf.TRF_ID ) tr
                  where a.del <> 1 and cls.del <> 1 and scl.del <> 1
                    and a.usr_id = ? and a.scl_id = scl.scl_id
                    and a.scl_id = cls.scl_id
                    and a.cls_id = cls.cls_id
                    and usr.usr_id = a.usr_id
                    and tr.USR_ID = a.USR_ID";

        $result = DB::getInstance()->getAll($query, [$user_id], PDO::FETCH_ASSOC);
        return $result;
    }
}