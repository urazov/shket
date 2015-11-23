<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Food\DBFood;
use CabinetBundle\Repositories\Pupil\DBPupil;
use CabinetBundle\Repositories\Teacher\DBTeacher;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TeacherController extends Controller
{
    public function indexAction()
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $result = DBTeacher::getInstance()->getUserInfo($user);

            $this->get('session')->set('default_scl_id', $result[0]['SCL_ID']);

            $parameters = [
                'current_date' => date("d-m-Y"),
                'current_year' => date("Y", time()),
                'current_month' => date("m", time()),
                'last_date' => date("d-m-Y", time() - 7 * 24 * 60 * 60),
                'school' => $result,
                'mail_name' => $user->getFullName(),
                'mail_phone' => $user->getPhone(),
                'mail_email' => $user->getEmail(),
            ];

            if(count($result) == 1){
                $parameters['default_class_id'] = $result[0]['cls_id'];
            }


            return $this->render('CabinetBundle:Teacher:index.html.twig', $parameters);
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function userInformationAction()
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $result = DBTeacher::getInstance()->getUserInfo($user);

            $template_parameters['info'] = $result;

            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();
            $template_parameters['usr_id'] = $user->getId();
            $ava_path = realpath($this->container->getParameter('kernel.root_dir').'/../web/users/').'/'.$user->getId().'/avatar.jpg';
            if(file_exists($ava_path)){
                $template_parameters['avatar'] = $ava_path;
            }

            return $this->render('CabinetBundle:Teacher:user_information.html.twig', $template_parameters);
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function mainmenuAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

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
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function zakazAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

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
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function pitanieAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

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
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function tabelAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

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
            $itog_result_date = [];
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
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function listAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

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
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function balanceAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $parameters = [
                'school_id' => $this->get('session')->get('default_scl_id'),
                'class_id' => $request->get('class_id'),
                'teacher_id' => $this->getUser()->getId()
            ];

            $result = DBTeacher::getInstance()->getPupilList($parameters);

            return $this->render('CabinetBundle:Teacher/balance:balance_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function enterDetailAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $parameters = [
                'user_id' => $request->get('user_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to')
            ];

            $result = DBPupil::getInstance()->getPupilEnter($parameters);

            return $this->render('CabinetBundle:Pupil/enter:enter_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function enterAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $parameters = [
                'school_id' => $this->get('session')->get('default_scl_id'),
                'class_id' => $request->get('class_id'),
                'teacher_id' => $this->getUser()->getId(),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = DBTeacher::getInstance()->getPupilEntersInfo($parameters);

            return $this->render('CabinetBundle:Teacher/enter:enter_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function changeBjtAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $parameters['user_id'] = $request->get('user_id');

            $is_bjt = DBTeacher::getInstance()->changePupilBjt($parameters);

            return new Response($is_bjt);
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response(0);
        }
    }
}
