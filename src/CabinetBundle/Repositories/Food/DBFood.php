<?php

namespace CabinetBundle\Repositories\Food;

use MainBundle\Security\User;
use MainBundle\Utils\DB;
use PDO;

class DBFood
{
    private static $db_instance;

    private static $instance = null;

    private function __construct()
    {
        self::$db_instance = DB::getInstance();
    }

    /**
     * @return DBFood
     */
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

    public function getAllComplexOnDate(array $parameters)
    {
        $query = "SELECT DMEAL.MEAL_ID, MEAL.name, dMEAL.COST
                  FROM CS_SHKET.dMEAL, CS_SHKET.MEAL
                    where MEAL.MEAL_ID = DMEAL.MEAL_ID
                      and ADATE = ?
                      and MEAL.IS_COMPLEX = 1
                      and meal.MEAL_ID <> 0
                      and dMEAL.SCL_ID = ?
                      and MEAL.DEL <> 1 and DMEAL.DEL <> 1";

        $result = self::$db_instance->getAll($query, [$parameters['date'], $parameters['school_id']]);
        return $result;
    }

    public function getDetailsOfComplex(array $parameters)
    {
        $query = "SELECT meal.MEAL_ID, MEAL.name, dMEAL.WGHT, dMEAL.PROTEIN, dMEAL.FAT, dMEAL.CARB, dMEAL.KKAL
            FROM CS_SHKET.dMEAL, CS_SHKET.MEAL
            where MEAL.MEAL_ID = DMEAL.MEAL_ID
            and ADATE = ?
            and MEAL.IS_COMPLEX = 0
            and dMEAL.SCL_ID = ?
            and dmeal.MAIN_MEAL_ID = ?
            and MEAL.DEL <> 1 and DMEAL.DEL <> 1";

        $result = self::$db_instance->getAll($query, [$parameters['date'], $parameters['school_id'], $parameters['meal_id']]);
        return $result;
    }

    public function getAllGroupsOfMeals(array $parameters)
    {
        $query = "SELECT distinct GMEAL.GMEAL_ID, GMEAL.NAME
            FROM CS_SHKET.dMEAL, CS_SHKET.MEAL, cs_shket.gmeal
            where MEAL.MEAL_ID = DMEAL.MEAL_ID
            and MEAL.GMEAL_ID = GMEAL.GMEAL_ID
            and ADATE = ?
            and MEAL.IS_COMPLEX = 0
            and meal.MEAL_ID <> 0 and dmeal.main_meal_id = 0
            and dMEAL.SCL_ID = ?
            and GMEAL.DEL <> 1
            and MEAL.DEL <> 1 and DMEAL.DEL <> 1";

        $result = self::$db_instance->getAll($query, [$parameters['date'], $parameters['school_id']]);
        return $result;
    }

    public function getAllMealsOfGroup(array $parameters)
    {
        $query = "SELECT meal.MEAL_ID, MEAL.name, dmeal.cost, dMEAL.WGHT, dMEAL.PROTEIN, dMEAL.FAT , dMEAL.CARB, dMEAL.KKAL
            FROM CS_SHKET.dMEAL, CS_SHKET.MEAL
            where MEAL.MEAL_ID = DMEAL.MEAL_ID
            and MEAL.GMEAL_ID = ?
            and ADATE = ?
            and MEAL.IS_COMPLEX = 0
            and meal.MEAL_ID <> 0
            and dmeal.main_meal_id = 0
            and dMEAL.SCL_ID = ?
            and MEAL.DEL <> 1 and DMEAL.DEL <> 1";

        $result = self::$db_instance->getAll($query, [$parameters['gmeal_id'], $parameters['date'], $parameters['school_id']]);
        return $result;
    }

    public function getClassInfo($parameters, $preset_class = true)
    {
        $query = "select c.cls_id, c.name
                       , CAST(SUBSTRING(c.name, 1, case when PATINDEX('%[^0-9]%',c.name) = 0 then LEN(c.name)
                                                        else PATINDEX('%[^0-9]%',c.name)-1
                                                    end) as int) as num_cls
                       , SUBSTRING(c.name, PATINDEX('%[^0-9]%',c.name), LEN(c.name)) as let_cls
                       , (select distinct min(usr.name)
                            from cs_shket.usr
                               , cs_shket.USER_IN_SCL_CLS b
                            where usr.role_id = 2
                              and b.scl_id = c.scl_id
                              and b.cls_id = c.cls_id
                              and b.usr_id = usr.usr_id
                              and b.del <> 1 and usr.del <> 1) as teacher
                    from cs_shket.cls c
                   where c.del <> 1 and c.scl_id = ?
                    and c.CLS_ID <> 0";

        $query_params = [$parameters['school_id']];

        if($preset_class){
            $query .= "and (cls_id = ? or ? = -1)";
            $query_params = [$parameters['school_id'], $parameters['class_id'], $parameters['class_id']];
        }

        $query .= "order by num_cls, let_cls";

        $result = self::$db_instance->getAll($query, $query_params);
        return $result;
    }

    public function getFoodInfo($parameters)
    {
        $query = "
        with x as (
            Select m.NAME as name
              , r.COST as price
              , dm.WGHT as wght
              , (case
                when r.IS_BJT = 1 then 0
                else r.CNT
                 end) as cnt_comm
              , (case
                when r.IS_BJT = 1 then 0
                else r.COST*r.CNT
                 end) as price_comm
              , (case
                when r.IS_BJT = 1 then r.CNT
                else 0
                 end) as cnt_bjt
              , (case
                when r.IS_BJT = 1 then r.COST_BJT*r.cnt
                else 0
                 end) as add_price_bjt
              ,(case
                when r.IS_BJT = 1 then r.COST_BJT*r.CNT
                else r.COST*r.cnt
                 end) as total_price
              , r.IS_BJT as is_bjt
              , m.IS_COMPLEX as is_complex
              , r.ADATE as dtm_nf
             from CS_SHKET.RLS r
             inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
             inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID
             inner join CS_SHKET.USER_IN_SCL_CLS uc on r.USR_ID = uc.USR_ID
             inner join CS_SHKET.USR u on r.USR_ID = u.USR_ID and u.ROLE_ID = 1
             where r.ADATE = ?
               and uc.CLS_ID = ?
               and uc.SCL_ID = ?
               and r.del <> 1 and dm.del <> 1 and m.del <> 1 and uc.del<> 1 and u.del <> 1
        )


        Select ROW_NUMBER() over (order by y.is_complex desc, y.name) as rn
          , upper(substring(replace(y.name, 'ЯЯЯ', ''), 1, 1)) + lower(substring(replace(y.name, 'ЯЯЯ', ''), 2, LEN(y.name))) as name
          , y.price as price
          , y.wght as wght
          , y.cnt_comm as cnt_comm
          , y.sum_comm as sum_comm
          , y.cnt_bjt as cnt_bjt
          , y.add_price_bjt as add_price_bjt
          , y.total_price as total_price
         from (
                Select distinct x.name as name
                  , x.price as price
                  , x.wght as wght
                  , SUM(x.cnt_comm) over (partition by x.name, x.price) as cnt_comm
                  , SUM(x.price_comm) over (partition by x.name, x.price) as sum_comm
                  , SUM(x.cnt_bjt) over (partition by x.name, x.price) as cnt_bjt
                  , SUM(x.add_price_bjt) over (partition by x.name, x.price) as add_price_bjt
                  , SUM(x.total_price) over (partition by x.name, x.price) as total_price
                  , x.is_complex as is_complex
                  , x.dtm_nf as dtm_nf
                 from x

                UNION

                Select distinct 'ЯЯЯTOTAL:' as name
                  , null as price
                  , '' as wght
                  , isnull(SUM(x.cnt_comm), 0) as cnt_comm
                  , isnull(SUM(x.price_comm), 0) as sum_comm
                  , isnull(SUM(x.cnt_bjt), 0) as cnt_bjt
                  , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
                  , isnull(SUM(x.total_price), 0) as total_price
                  ,  0 as is_complex
                  , '2001-01-01' as dtm_nf
                 from x
         ) y
         order by y.dtm_nf desc, y.name ";

        $date = substr($parameters['date'], -4)."-".substr($parameters['date'], 3, 2)."-".substr($parameters['date'], 0, 2);

        $result = self::$db_instance->getAll($query, [
            $date, $parameters['class_id'], $parameters['school_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getConclusion($parameters)
    {
        $query = "with x as (
            Select m.NAME as name
              , r.COST as price
              , dm.WGHT as wght
              , (case
                when r.IS_BJT = 1 then 0
                else r.CNT
                 end) as cnt_comm
              , (case
                when r.IS_BJT = 1 then 0
                else r.COST*r.CNT
                 end) as price_comm
              , (case
                when r.IS_BJT = 1 then r.CNT
                else 0
                 end) as cnt_bjt
              , (case
                when r.IS_BJT = 1 then r.COST_BJT*r.cnt
                else 0
                 end) as add_price_bjt
              ,(case
                when r.IS_BJT = 1 then r.COST_BJT*r.CNT
                else r.COST*r.cnt
                 end) as total_price
              , r.IS_BJT as is_bjt
              , m.IS_COMPLEX as is_complex
             from CS_SHKET.RLS r
             inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
             inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID
             inner join CS_SHKET.USER_IN_SCL_CLS uc on r.USR_ID = uc.USR_ID
             inner join CS_SHKET.USR u on r.USR_ID = u.USR_ID and u.ROLE_ID = 1
             where r.ADATE = ?
               and uc.scl_id = ?
               and (cls_id = ? or ? = '-1')
            )

            Select ROW_NUMBER() over (order by y.is_complex desc, y.name) as rn
              , y.price as price
              , y.wght as wght
              , y.cnt_comm as cnt_comm
              , y.sum_comm as sum_comm
              , y.cnt_bjt as cnt_bjt
              , y.add_price_bjt as add_price_bjt
              , y.total_price as total_price
             from (
                Select distinct 'ЯЯЯTOTAL:' as name
                  , null as price
                  , '' as wght
                  , isnull(SUM(x.cnt_comm), 0) as cnt_comm
                  , isnull(SUM(x.price_comm), 0) as sum_comm
                  , isnull(SUM(x.cnt_bjt), 0) as cnt_bjt
                  , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
                  , isnull(SUM(x.total_price), 0) as total_price
                  ,  0 as is_complex
                 from x
             ) y
            ";

        $date = substr($parameters['date'], -4)."-".substr($parameters['date'], 3, 2)."-".substr($parameters['date'], 0, 2);

        $result = self::$db_instance->getAll($query, [
            $date, $parameters['school_id'], $parameters['class_id'], $parameters['class_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getFoodInfoByPeriod($parameters)
    {
        $query = "
        with x as (
            Select convert(nvarchar, CAST(r.adate as datetime), 104) as dt
             , m.NAME
             , r.COST as price
             , (case
              when r.IS_BJT = 1 then 0
              else r.CNT
                   end) as cnt_comm
             , (case
              when r.IS_BJT = 1 then 0
              else r.COST*r.CNT
                   end) as price_comm
                , (case
              when r.IS_BJT = 1 then r.CNT
              else 0
                end) as cnt_bjt
                , (case
              when r.IS_BJT = 1 then r.COST_BJT*r.cnt
              else 0
                   end) as add_price_bjt
             ,(case
              when r.IS_BJT = 1 then r.COST_BJT*r.CNT
              else r.COST*r.cnt
                end) as total_price
              , r.IS_BJT as is_bjt
              , m.IS_COMPLEX as is_complex
              , r.ADATE as dtm_nf
             from CS_SHKET.RLS r
             inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
             inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID
             inner join CS_SHKET.USER_IN_SCL_CLS uc on r.USR_ID = uc.USR_ID
             inner join CS_SHKET.USR u on r.USR_ID = u.USR_ID
             where uc.CLS_ID = ?
               and uc.SCL_ID = ?
               and r.del <> 1 and dm.del <> 1 and m.del <> 1 and uc.del<> 1 and u.del <> 1
               and r.ADATE between ? and ?
        )


            Select y.dt as dt
              , upper(substring(y.name, 1, 1)) + lower(substring(y.name, 2, LEN(y.name))) as name
              , y.price as price
              , y.cnt_comm as cnt_comm
              , y.sum_comm as sum_comm
              , y.cnt_bjt as cnt_bjt
              , y.add_price_bjt as add_price_bjt
              , y.total_price as total_price
             from (
                    Select distinct x.dt
                     , x.name as name
                      , x.price as price
                      , SUM(x.cnt_comm) over (partition by x.name, x.price, x.dt) as cnt_comm
                      , SUM(x.price_comm) over (partition by x.name, x.price, x.dt) as sum_comm
                      , SUM(x.cnt_bjt) over (partition by x.name, x.price, x.dt) as cnt_bjt
                      , SUM(x.add_price_bjt) over (partition by x.name, x.price, x.dt) as add_price_bjt
                      , SUM(x.total_price) over (partition by x.name, x.price, x.dt) as total_price
                      , x.is_complex as is_complex
                      , x.dtm_nf as dtm_nf
                     from x

                   UNION

                    Select distinct 'TOTAL:' as dt
                     , '' as name
                      , null as price
                      , isnull(SUM(x.cnt_comm), 0) as cnt_comm
                      , isnull(SUM(x.price_comm), 0) as sum_comm
                      , isnull(SUM(x.cnt_bjt), 0) as cnt_bjt
                      , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
                      , isnull(SUM(x.total_price), 0) as total_price
                      ,  0 as is_complex
                      , '2001-01-01' as dtm_nf
                     from x

             ) y
              order by y.dtm_nf desc, y.name
            ";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = self::$db_instance->getAll($query, [
             $parameters['class_id'], $parameters['school_id'], $date_from, $date_to
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getConclusionByPeriod($parameters)
    {
        $query = "with x as (
                Select convert(nvarchar, CAST(r.adate as datetime), 104) as dt
                 , m.NAME
                 , r.COST as price
                 , (case
                  when r.IS_BJT = 1 then 0
                  else r.CNT
                       end) as cnt_comm
                 , (case
                  when r.IS_BJT = 1 then 0
                  else r.COST*r.CNT
                       end) as price_comm
                    , (case
                  when r.IS_BJT = 1 then r.CNT
                  else 0
                    end) as cnt_bjt
                    , (case
                  when r.IS_BJT = 1 then r.COST_BJT*r.cnt
                  else 0
                       end) as add_price_bjt
                 ,(case
                  when r.IS_BJT = 1 then r.COST_BJT*r.CNT
                  else r.COST*r.cnt
                    end) as total_price
                  , r.IS_BJT as is_bjt
                  , m.IS_COMPLEX as is_complex
                  , r.ADATE as dtm_nf
                 from CS_SHKET.RLS r
                 inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
                 inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID
                 inner join CS_SHKET.USER_IN_SCL_CLS uc on r.USR_ID = uc.USR_ID
                 inner join CS_SHKET.USR u on r.USR_ID = u.USR_ID
                 where r.ADATE between ? and ?
                   and uc.scl_id = ?
                   and (cls_id = ? or ? = -1)
        )
        Select
            isnull(SUM(x.price_comm), 0) as sum_comm
          , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
          , isnull(SUM(x.total_price), 0) as total_price
         from x";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = self::$db_instance->getAll($query, [
            $date_from, $date_to, $parameters['school_id'], $parameters['class_id'], $parameters['class_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getInfoForFirstRep($parameters)
    {
        $query = "
              select c.cls_id
              , c.name
              , (select distinct usr.name
                from cs_shket.usr
                 , cs_shket.USER_IN_SCL_CLS b
                where usr.role_id = 2
                  and b.scl_id = c.scl_id
                  and b.cls_id = c.cls_id
                  and b.usr_id = usr.usr_id
                  and b.del <> 1 and c.cls_id <> 0
                  and c.del <> 1) as teacher
                from cs_shket.cls c
                where c.del <> 1
                  and c.scl_id = ?
                  and c.CLS_ID <> 0
                  and exists (
                    select null
                    from CS_SHKET.USER_IN_SCL_CLS uc
                    inner join CS_SHKET.RLS r on r.USR_ID = uc.USR_ID
                    inner join CS_SHKET.MEAL m on m.MEAL_ID = r.MEAL_ID and m.IS_COMPLEX = 1
                    where uc.SCL_ID = c.SCL_ID
                      and uc.CLS_ID = c.CLS_ID
                      and r.ADATE between ? and ?
                  )
                order by CAST(SUBSTRING(c.name, 1, PATINDEX('%[^0-9]%',c.name)-1) as int)
                             , SUBSTRING(c.name, PATINDEX('%[^0-9]%',c.name), LEN(c.name))";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = self::$db_instance->getAll($query, [
            $parameters['school_id'], $date_from, $date_to
        ], PDO::FETCH_ASSOC);

        return $result;
    }

    public function getFirstInfoByPeriod($parameters)
    {
        $query =
            "with x as (
                    Select convert(nvarchar, CAST(r.ddate as datetime), 104) as dt
                        , r.COST
                        , (case
                            when r.IS_BJT = 1 then 0
                            else r.CNT
                            end) as cnt_comm
                          , (case
                            when r.IS_BJT = 1 then 0
                            else r.COST*r.CNT
                            end) as price_comm
                          , (case
                            when r.IS_BJT = 1 then r.CNT
                            else 0
                            end) as cnt_bjt
                          , (case
                            when r.IS_BJT = 1 then r.COST_BJT*r.cnt
                            else 0
                            end) as add_price_bjt
                          ,(case
                            when r.IS_BJT = 1 then r.COST_BJT*r.CNT
                            else r.COST*r.cnt
                            end) as total_price
                      , r.IS_BJT as is_bjt
                      , m.IS_COMPLEX as is_complex
                      , cast(r.ddate as date) ddate, m.name
                     from CS_SHKET.RLS r
                     inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
                     inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID and m.IS_COMPLEX = 1
                     inner join CS_SHKET.USER_IN_SCL_CLS uc on r.USR_ID = uc.USR_ID
                     inner join CS_SHKET.USR u on r.USR_ID = u.USR_ID
                     where r.ADATE between ? and ?
                       and uc.CLS_ID = ?
                       and uc.SCL_ID = ?
                       and r.del <> 1 and dm.del <> 1 and m.del <> 1 and uc.del<> 1 and u.del <> 1
                       )


                    Select  y.dt as dt
                                    , y.name
                            , y.price as price
                            , y.cnt_comm as cnt_comm
                            , y.sum_comm as sum_comm
                            , y.cnt_bjt as cnt_bjt
                            , y.add_price_bjt as add_price_bjt
                            , y.total_price as total_price
                        from (
                    Select x.dt as dt
                            , x.name
                        , x.COST as price
                        , SUM(x.cnt_comm) over (partition by x.dt, x.cost) as cnt_comm
                          , SUM(x.price_comm) over (partition by x.dt, x.cost) as sum_comm
                          , SUM(x.cnt_bjt) over (partition by x.dt, x.cost) as cnt_bjt
                          , SUM(x.add_price_bjt) over (partition by x.dt, x.cost) as add_price_bjt
                          , SUM(x.total_price) over (partition by x.dt, x.cost) as total_price
                              , ddate
                     from x

                    UNION

                    Select distinct 'TOTAL:' as dt
                            , null as name
                        , null as price
                        , isnull(SUM(x.cnt_comm), 0) as cnt_comm
                      , isnull(SUM(x.price_comm), 0) as sum_comm
                      , isnull(SUM(x.cnt_bjt), 0) as cnt_bjt
                      , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
                      , isnull(SUM(x.total_price), 0) as total_price
                      ,  '1900-01-01' as ddate
                     from x
                     ) y
                     order by y.ddate desc, y.price";

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = self::$db_instance->getAll($query, [
            $date_from, $date_to, $parameters['class_id'], $parameters['school_id']
        ], PDO::FETCH_NUM);

        return $result;
    }

    public function getConclusionFirstByPeriod($parameters)
    {
        $query = "with x as (
            Select convert(nvarchar, CAST(r.adate as datetime), 104) as dt
             , m.NAME as name
             , r.COST
             , (case
              when r.IS_BJT = 1 then 0
              else r.CNT
              end) as cnt_comm
               , (case
              when r.IS_BJT = 1 then 0
              else r.COST*r.CNT
              end) as price_comm
               , (case
              when r.IS_BJT = 1 then r.CNT
              else 0
              end) as cnt_bjt
               , (case
              when r.IS_BJT = 1 then r.COST_BJT*r.cnt
              else 0
              end) as add_price_bjt
               ,(case
              when r.IS_BJT = 1 then r.COST_BJT*r.CNT
              else r.COST*r.cnt
              end) as total_price
              , r.IS_BJT as is_bjt
              , m.IS_COMPLEX as is_complex
              , r.ADATE as dtm_nf
             from CS_SHKET.RLS r
             inner join CS_SHKET.DMEAL dm on dm.MEAL_ID = r.MEAL_ID and r.ADATE = dm.ADATE and dm.MAIN_MEAL_ID = 0
             inner join CS_SHKET.MEAL m on m.MEAL_ID = dm.MEAL_ID and m.IS_COMPLEX = 1
             where r.ADATE between ? and ?
               and r.SCL_ID = ?
               )

            Select distinct
                isnull(SUM(x.price_comm), 0) as sum_comm
              , isnull(SUM(x.add_price_bjt), 0) as add_price_bjt
              , isnull(SUM(x.total_price), 0) as total_price
             from x " ;

        $date_from = substr($parameters['date_from'], -4)."-".substr($parameters['date_from'], 3, 2)."-".substr($parameters['date_from'], 0, 2);
        $date_to = substr($parameters['date_to'], -4)."-".substr($parameters['date_to'], 3, 2)."-".substr($parameters['date_to'], 0, 2);

        $result = self::$db_instance->getAll($query, [
            $date_from, $date_to, $parameters['school_id']
        ], PDO::FETCH_NUM);

        return $result;
    }
}