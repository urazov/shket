<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Food\DBFood;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FoodController extends Controller
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

            $parameters = [
                'current_date' => date("d-m-Y"),
                'last_date' => date("d-m-Y", time() - 7 * 24 * 60 * 60),
                'school' => DBFood::getInstance()->getUserInfo($user),
                'mail_name' => $user->getFullName(),
                'mail_phone' => $user->getPhone(),
                'mail_email' => $user->getEmail()
            ];
            return $this->render('CabinetBundle:Food:index.html.twig', $parameters);
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

            $result = DBFood::getInstance()->getUserInfo($user);

            $template_parameters['info'] = $result;

            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();
            $template_parameters['usr_id'] = $user->getId();

            $ava_path = realpath($this->container->getParameter('kernel.root_dir').'/../web/users/').'/'.$user->getId().'/avatar.jpg';
            if(file_exists($ava_path)){
                $template_parameters['avatar'] = $ava_path;
            }

            return $this->render('CabinetBundle:Food:user_information.html.twig', $template_parameters);
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
                'school_id' => $request->get('school_id')
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

        } catch (Exception $e){
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function classesAction(Request $request)
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
                'school_id' => $request->get('school_id')
            ];

            $all_classes = DBFood::getInstance()->getClassInfo($parameters, false);

            return $this->render('CabinetBundle:Food/zakaz:zakaz_classes.html.twig', [
                'classes' => $all_classes
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
                'school_id' => $request->get('school_id'),
                'class_id' => $request->get('class_id'),
            ];

            $class_info = DBFood::getInstance()->getClassInfo($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $food_info_by_class[] = DBFood::getInstance()->getFoodInfo($parameters);
            }

            $parameters['class_id'] = $request->get('class_id');
            $conclusion = DBFood::getInstance()->getConclusion($parameters);

            return $this->render('CabinetBundle:Food/zakaz:zakaz_report.html.twig', [
                'class_info' => $class_info,
                'food_info_by_class' => isset($food_info_by_class) ? $food_info_by_class : [],
                'conclusion' => $conclusion,
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
                'school_id' => $request->get('school_id'),
                'class_id' => $request->get('class_id'),
            ];

            $class_info = DBFood::getInstance()->getClassInfo($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $food_info_by_period[] = DBFood::getInstance()->getFoodInfoByPeriod($parameters);
            }
            $parameters['class_id'] = $request->get('class_id');
            $conclusion_food_by_period = DBFood::getInstance()->getConclusionByPeriod($parameters);

            return $this->render('CabinetBundle:Food/pitanie:pitanie_report.html.twig', [
                'class_info' => $class_info,
                'food_info_by_period' => isset($food_info_by_period) ? $food_info_by_period : [],
                'conclusion_by_period' => $conclusion_food_by_period,
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function firstAction(Request $request)
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
                'school_id' => $request->get('school_id')
            ];

            $class_info = DBFood::getInstance()->getInfoForFirstRep($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $result = DBFood::getInstance()->getFirstInfoByPeriod($parameters);

                if(count($result) > 1){
                    $class_result[] = $class;
                    $food_info_by_period[] = $result;
                }
            }

            $conclusion_food_rep_first = DBFood::getInstance()->getConclusionFirstByPeriod($parameters);

            return $this->render('CabinetBundle:Food/rep1:rep1_report.html.twig', [
                'class_info' => isset($class_result) ? $class_result : [],
                'food_info_by_period' => isset($food_info_by_period) ? $food_info_by_period : [],
                'parameters' => $parameters,
                'conclusion_food_rep_first' => $conclusion_food_rep_first
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function secondAction(Request $request)
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
                'school_id' => $request->get('school_id')
            ];

            $class_info = DBFood::getInstance()->getInfoForSecondRep($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $result = DBFood::getInstance()->getSecondInfoByPeriod($parameters);

                if(count($result) > 1){
                    $class_result[] = $class;
                    $food_info_by_period[] = $result;
                }
            }

            $conclusion_food_rep_second = DBFood::getInstance()->getConclusionSecondByPeriod($parameters);

            return $this->render('CabinetBundle:Food/rep2:rep2_report.html.twig', [
                'class_info' => isset($class_result) ? $class_result : [],
                'food_info_by_period' => isset($food_info_by_period) ? $food_info_by_period : [],
                'parameters' => $parameters,
                'conclusion_food_rep_second' => $conclusion_food_rep_second
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function thirdAction(Request $request)
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
                'school_id' => $request->get('school_id')
            ];

            $class_info = DBFood::getInstance()->getInfoForThirdRep($parameters);
            foreach($class_info as $class){
                $parameters['class_id'] = $class['cls_id'];
                $food_info_by_period[] = DBFood::getInstance()->getThirdInfoByPeriod($parameters);
            }

            $conclusion_food_rep_third = DBFood::getInstance()->getConclusionThirdByPeriod($parameters);

            return $this->render('CabinetBundle:Food/rep3:rep3_report.html.twig', [
                'class_info' => $class_info,
                'food_info_by_period' => isset($food_info_by_period) ? $food_info_by_period : [],
                'parameters' => $parameters,
                'conclusion_food_rep_third' => $conclusion_food_rep_third
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

}
