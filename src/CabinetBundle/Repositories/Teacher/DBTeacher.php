<?php

namespace CabinetBundle\Repositories\Teacher;

use MainBundle\Security\User;
use MainBundle\Utils\DB;
use PDO;

class DBTeacher
{
    private static $db_instance;

    private static $instance = null;

    private function __construct()
    {
        self::$db_instance = DB::getInstance();
    }

    /**
     * @return DBTeacher
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new DBTeacher();
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

    public function getClassInfo($parameters, $preset_class = true)
    {
        $query =  "select c.cls_id, c.name
                       , CAST(SUBSTRING(c.name, 1, PATINDEX('%[^0-9]%',c.name)-1) as int) as num_cls
                       , SUBSTRING(c.name, PATINDEX('%[^0-9]%',c.name), LEN(c.name)) as let_cls
                    from cs_shket.cls c
                    where c.del <> 1
                      and c.CLS_ID <> 0
                      and exists (
                        select null
                        from cs_shket.USER_IN_SCL_CLS uc
                        where uc.usr_id = ?
                          and uc.scl_id = c.scl_id
                          and uc.cls_id = c.cls_id
                          and uc.del <> 1
                      )";

        $query_params = [$parameters['user_id']];

        if($preset_class){
            $query .= "and (cls_id = ? or ? = -1)";
            $query_params = [$parameters['user_id'], $parameters['class_id'], $parameters['class_id']];
        }

        $query .= "order by num_cls, let_cls";

        $result = DB::getInstance()->getAll($query, $query_params);
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
               and exists ( select null from CS_SHKET.USER_IN_SCL_CLS where usr_id = ? and scl_id = uc.scl_id and cls_id = uc.cls_id)
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

        $result = DB::getInstance()->getAll($query, [
            $date, $parameters['school_id'], $parameters['class_id'], $parameters['class_id'], $parameters['user_id']
        ], PDO::FETCH_NUM);

        return $result;
    }
}