<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Food\DBFood;
use CabinetBundle\Repositories\Teacher\DBTeacher;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();

        $result = DBTeacher::getInstance()->getUserInfo($user);

        $this->get('session')->set('default_scl_id', $result[0]['SCL_ID']);
        $this->get('session')->set('default_scl_name', $result[0]['NAME']);

        $parameters = [
            'current_date' => date("d-m-Y"),
            'school' => $result
        ];

        return $this->render('CabinetBundle:Teacher:index.html.twig', $parameters);
    }

    public function userInformationAction()
    {
        try{
            $user = $this->getUser();
            $result = DBTeacher::getInstance()->getUserInfo($user);

            $template_parameters['info'] = $result;

            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();

            return $this->render('CabinetBundle:Teacher:user_information.html.twig', $template_parameters);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function mainmenuAction(Request $request)
    {
        try{
            $parameters = [
                'date' => $request->get('date'),
                'school_id' => $this->get('session')->get('default_scl_id')
            ];

            $all_complex_on_date = DBFood::getInstance()->getAllComplexOnDate($parameters);
            foreach($all_complex_on_date as $idx => $complex){
                $details_of_complex[$idx] = DBFood::getInstance()->getDetailsOfComplex(array_merge($parameters, ['meal_id' => $complex['MEAL_ID']]));
            }

            $all_groups_of_meal = DBFood::getInstance()->getAllGroupsOfMeals($parameters);
            foreach($all_groups_of_meal as $idx => $group){
                $meals_of_group[$idx] = DBFood::getInstance()->getAllMealsOfGroup(array_merge($parameters, ['gmeal_id' => $group['GMEAL_ID']]));
            }

            return $this->render('CabinetBundle:Food/menu:mainmenu_report.html.twig', [
                'all_complex_on_date' => $all_complex_on_date,
                'details_of_complex' => isset($details_of_complex) ? $details_of_complex : [],
                'all_groups_of_meal' => $all_groups_of_meal,
                'meals_of_group' => isset($meals_of_group) ? $meals_of_group : []
            ]);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function zakazAction(Request $request)
    {
        try{
            $parameters = [
                'date' => $request->get('date'),
                'school_id' => $this->get('session')->get('default_scl_id'),
                'class_id' => $request->get('class_id'),
                'user_id' => $this->getUser()->getId()
            ];

            $class_info = DBTeacher::getInstance()->getClassInfo($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $food_info_by_class[] = DBFood::getInstance()->getFoodInfo($parameters);
            }

            $parameters['class_id'] = $request->get('class_id');
            $conclusion = DBTeacher::getInstance()->getConclusion($parameters);

            return $this->render('CabinetBundle:Teacher/zakaz:zakaz_report.html.twig', [
                'class_info' => $class_info,
                'food_info_by_class' => isset($food_info_by_class) ? $food_info_by_class : [],
                'conclusion' => $conclusion,
                'teacher_name' => $this->getUser()->getFullName()
            ]);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function pitanieAction(Request $request)
    {
        try{
            $parameters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'school_id' => $this->get('session')->get('default_scl_id'),
                'class_id' => $request->get('class_id'),
                'user_id' => $this->getUser()->getId()
            ];

            $class_info = DBTeacher::getInstance()->getClassInfo($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $food_info_by_period[] = DBFood::getInstance()->getFoodInfoByPeriod($parameters);
            }
            $parameters['class_id'] = $request->get('class_id');
            $conclusion_food_by_period = DBTeacher::getInstance()->getConclusionByPeriod($parameters);

            return $this->render('CabinetBundle:Teacher/pitanie:pitanie_report.html.twig', [
                'class_info' => $class_info,
                'food_info_by_period' => isset($food_info_by_period) ? $food_info_by_period : [],
                'conclusion_by_period' => $conclusion_food_by_period,
                'teacher_name' => $this->getUser()->getFullName()
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function tabelAction(Request $request)
    {
        try{
            $parameters = [
                'month' => $request->get('month'),
                'year' => $request->get('year'),
                'school_id' => $this->get('session')->get('default_scl_id'),
                'class_id' => $request->get('class_id'),
                'teacher_id' => $this->getUser()->getId()
            ];

            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $parameters['month'], $parameters['year']);

            $pupils = DBTeacher::getInstance()->getAllPupil($parameters);

            $parameters['date_to'] = $parameters['year'] . '-' . ($parameters['month']+1) . '-01';
            $parameters['date_from'] = $parameters['year'] . '-' . $parameters['month'] . '-01';

            foreach($pupils as $pupil){
                $user_total = 0;
                $food_user_date = [];
                $parameters['pupil_id'] = $pupil['usr_id'];
                $food_date_info = DBTeacher::getInstance()->getFoodCountForPupil($parameters);
                foreach($food_date_info as $key => $date){
                    $food_user_date[$date['date']] = $date['cnt'];
                }

                for($day = 1; $day <= $days_in_month; $day++){
                    $date = $parameters['year'] . '-' . $parameters['month'] . '-' . ($day<10 ? '0'.$day : $day);
                    if(array_key_exists($date, $food_user_date)){
                        $result[$pupil['usr_id']][$day] = $food_user_date[$date];
                        $user_total += $food_user_date[$date];
                    } else {
                        $result[$pupil['usr_id']][$day] = 0;
                    }
                }
                $result[$pupil['usr_id']][$day] = $user_total;
            }

            $itog_total = 0;
            $parameters['date_to'] = $parameters['year'] . '-' . ($parameters['month']+1) . '-01';
            $parameters['date_from'] = $parameters['year'] . '-' . $parameters['month'] . '-01';
            $itog_result = DBTeacher::getInstance()->getFoodCountItog($parameters);
            foreach($itog_result as $key => $date){
                $itog_result_date[$date['date']] = $date['cnt'];
            }
            for($day = 1; $day <= $days_in_month; $day++){
                $date = $parameters['year'] . '-' . $parameters['month'] . '-' . ($day<10 ? '0'.$day : $day);
                if(array_key_exists($date, $itog_result_date)){
                    $itog[$day] = $itog_result_date[$date];
                    $itog_total += $itog[$day];
                } else {
                    $itog[$day] = 0;
                }
            }
            $itog[$day] = $itog_total;

            return $this->render('CabinetBundle:Teacher/tabel:tabel_report.html.twig', [
                'result' => isset($result) ? $result : [],
                'pupils' => $pupils,
                'days' => $days_in_month,
                'total' => isset($itog) ? $itog : []
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function listAction(Request $request)
    {
        try{
            $parameters = [
                'school_id' => $this->get('session')->get('default_scl_id'),
                'class_id' => $request->get('class_id'),
                'teacher_id' => $this->getUser()->getId()
            ];

            $result = DBTeacher::getInstance()->getUserList($parameters);

            return $this->render('CabinetBundle:Teacher/list:list_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

}
