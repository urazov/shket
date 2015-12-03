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
        , cast(case when PATINDEX('%[^0-9]%',cls.name) = 0 then cls.name else LEFT(cls.name, LEN(cls.name)-1) end as int) as num_cls
        , case when PATINDEX('%[^0-9]%',cls.name) = 0 then null else right(cls.name, LEN(cls.name)-1) end as let_cls
        from cs_shket.user_in_scl_cls as uic, cs_shket.cls as cls
        where uic.scl_id in (
                select uic2.scl_id
                from cs_shket.user_in_scl_cls as uic2
                where uic2.usr_id = ?
                and uic2.del <> 1
        )
        and cls.cls_id = uic.cls_id
        and uic.del <> 1 and uic.cls_id <> 0 and cls.del <> 1
        order by num_cls, let_cls";

        $result = DB::getInstance()->getAll($query, [$user->getId()]);

        return $result;
    }

    public function getAllPupil($parameters)
    {
        $query = "select u.name, u.usr_id, cls.name as cls_name, u.is_bjt, u.bill, u.pass
, cast(case when PATINDEX('%[^0-9]%',cls.name) = 0 then cls.name else LEFT(cls.name, LEN(cls.name)-1) end as int) as num_cls
, case when PATINDEX('%[^0-9]%',cls.name) = 0 then null else right(cls.name, LEN(cls.name)-1) end as let_cls
            from cs_shket.usr u, cs_shket.USER_IN_SCL_CLS uc, cs_shket.cls cls
                where u.del <> 1 and uc.del <> 1 and cls.del <> 1
                    and u.usr_id = uc.usr_id
                    and uc.cls_id = cls.cls_id
                    and (uc.CLS_ID = ? or ? = -1)
                    and uc.scl_id = ?
                    and role_id = 1
                    order by num_cls, let_cls, u.name asc";

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

    public function getPupilEntersInfo($parameters)
    {
        $query =  "Select ROW_NUMBER() over (order by u.name) as rn, u.NAME as name,
        (
                select direct
                from (
                        SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                        from cs_shket.ent ent
                        where ent.usr_id = u.USR_ID
                        and ent.ddate between ? and ?
                        and ent.del <> 1
                ) res
                where res.num = 1
        ) direct,
        (
                select CONVERT(nvarchar, ddate, 104) as dt
                from (
                        SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                        from cs_shket.ent ent
                        where ent.usr_id = u.USR_ID
                        and ent.ddate between ? and ?
                        and ent.del <> 1
                ) res
                where res.num = 1
        ) dt,
        (
                select CONVERT(nvarchar, ddate, 108) as tm
                from (
                        SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                        from cs_shket.ent ent
                        where ent.usr_id = u.USR_ID
                        and ent.ddate between ? and ?
                        and ent.del <> 1
                ) res
                where res.num = 1
        ) tm, u.usr_id
            from CS_SHKET.USR u
            inner join CS_SHKET.USER_IN_SCL_CLS uc on u.USR_ID = uc.USR_ID
            where uc.SCL_ID = ? and u.del <> 1 and uc.del <> 1
              and (uc.CLS_ID = ? or ? = -1) and u.role_id = 1
            order by rn
            ";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $date_from, $date_to,
            $date_from, $date_to,
            $date_from, $date_to,
            $parameters['school_id'], $parameters['class_id'], $parameters['class_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getStaffEntersInfo($parameters)
    {
        $query =  "select ROW_NUMBER() over (order by r.name) as rn, r.* from (
                    select distinct u.NAME as name,
                    (
                            select direct
                            from (
                                    SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                                    from cs_shket.ent ent
                                    where ent.usr_id = u.USR_ID
                                    and ent.ddate between ? and ?
                                    and ent.del <> 1
                            ) res
                            where res.num = 1
                    ) direct,
                    (
                            select CONVERT(nvarchar, ddate, 104) as dt
                            from (
                                    SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                                    from cs_shket.ent ent
                                    where ent.usr_id = u.USR_ID
                                    and ent.ddate between ? and ?
                                    and ent.del <> 1
                            ) res
                            where res.num = 1
                    ) dt,
                    (
                            select CONVERT(nvarchar, ddate, 108) as tm
                            from (
                                    SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                                    from cs_shket.ent ent
                                    where ent.usr_id = u.USR_ID
                                    and ent.ddate between ? and ?
                                    and ent.del <> 1
                            ) res
                            where res.num = 1
                    ) tm, u.usr_id
                        from CS_SHKET.USR u
                        inner join CS_SHKET.USER_IN_SCL_CLS uc on u.USR_ID = uc.USR_ID
                        where uc.SCL_ID = ? and u.del <> 1 and uc.del <> 1
                          and u.role_id <> 1 and u.role_id <> 2
                ) as r";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $date_from, $date_to,
            $date_from, $date_to,
            $date_from, $date_to,
            $parameters['school_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getTeacherEntersInfo($parameters)
    {
        $query =  "select ROW_NUMBER() over (order by r.name) as rn, r.* from (
                select distinct u.NAME as name,
                (
                        select direct
                        from (
                                SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                                from cs_shket.ent ent
                                where ent.usr_id = u.USR_ID
                                and ent.ddate between ? and ?
                                and ent.del <> 1
                        ) res
                        where res.num = 1
                ) direct,
                (
                        select CONVERT(nvarchar, ddate, 104) as dt
                        from (
                                SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                                from cs_shket.ent ent
                                where ent.usr_id = u.USR_ID
                                and ent.ddate between ? and ?
                                and ent.del <> 1
                        ) res
                        where res.num = 1
                ) dt,
                (
                        select CONVERT(nvarchar, ddate, 108) as tm
                        from (
                                SELECT *, ROW_NUMBER() OVER(ORDER BY ent.ddate desc) num
                                from cs_shket.ent ent
                                where ent.usr_id = u.USR_ID
                                and ent.ddate between ? and ?
                                and ent.del <> 1
                        ) res
                        where res.num = 1
                ) tm, u.usr_id
                    from CS_SHKET.USR u
                    inner join CS_SHKET.USER_IN_SCL_CLS uc on u.USR_ID = uc.USR_ID
                    where uc.SCL_ID = ? and u.del <> 1 and uc.del <> 1
                      and u.role_id = 2


        ) as r";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $date_from, $date_to,
            $date_from, $date_to,
            $date_from, $date_to,
            $parameters['school_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getPupilsBalance($parameters)
    {
        $query =  "Select u.NAME as name, cls.name as class_name, u.usr_id as user_id
            , (select sum(summ) from cs_shket.blnc where del <> 1 and usr_id = u.usr_id ) as blnc
            , cast(case when PATINDEX('%[^0-9]%',cls.name) = 0 then cls.name else left(cls.name, LEN(cls.name)-1) end as int) as num_cls
            , case when PATINDEX('%[^0-9]%',cls.name) = 0 then null else right(cls.name, LEN(cls.name)-1) end as let_cls
            from CS_SHKET.USR u
            inner join CS_SHKET.USER_IN_SCL_CLS uc on u.USR_ID = uc.USR_ID
            inner join cs_shket.cls as cls on cls.cls_id = uc.cls_id
            where uc.SCL_ID = ? and u.del <> 1 and uc.del <> 1
              and (uc.CLS_ID = ? or ? = -1) and uc.del <> 1
              and u.role_id = 1
            order by num_cls, let_cls, u.name asc
        ";

        $result = DB::getInstance()->getAll($query, [
            $parameters['school_id'], $parameters['class_id'], $parameters['class_id']
        ]);

        return $result;
    }

    public function getBalanceDetail($parameters)
    {
        $query =  "Select CONVERT(nvarchar, blnc.ddate, 104) as dt, CONVERT(nvarchar, blnc.ddate, 108) as tm
            , blnc.ddate, acr.name as type_name, u.name as user_name
            , case when blnc.summ < 0 then abs(blnc.SUMM) else null end as minus
            , case when blnc.summ >= 0 then blnc.SUMM else null end as plus
            ,(select sum(b.SUMM)
                from CS_SHKET.BLNC b
                where b.USR_ID = u.usr_id
                  and b.DDATE <= blnc.ddate
                  and b.del <> 1
            ) as bal, cls.name as cls_name
            from cs_shket.usr u
            inner join cs_shket.user_in_scl_cls uic on uic.usr_id = u.usr_id
            inner join cs_shket.blnc blnc on u.usr_id = blnc.usr_id
            inner join cs_shket.cls cls on uic.cls_id = cls.cls_id
            inner join cs_shket.acr acr on acr.acr_id = blnc.acr_id
            where u.usr_id = ?
              and u.role_id = 1
              and blnc.del <> 1
              and (acr.acr_id = ? or ? = -1)
              and cast(blnc.ddate as date) between ? and ?
            order by blnc.ddate desc
        ";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = DB::getInstance()->getAll($query, [
            $parameters['user_id'], $parameters['acr_id'], $parameters['acr_id'], $date_from, $date_to
        ]);

        return $result;
    }
}