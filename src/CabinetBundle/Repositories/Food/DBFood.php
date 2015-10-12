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

}