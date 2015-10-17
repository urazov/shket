<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Food\DBFood;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FoodController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();

        $parameters = [
            'current_date' => date("d-m-Y"),
            'school' => DBFood::getInstance()->getUserInfo($user)
        ];
        return $this->render('CabinetBundle:Food:index.html.twig', $parameters);
    }

    public function userInformationAction()
    {
        try{
            $user = $this->getUser();
            $result = DBFood::getInstance()->getUserInfo($user);

            $template_parameters['info'] = $result;
            $template_parameters['default_scl_id'] = $result[0]['SCL_ID'];
            $template_parameters['default_scl_name'] = $result[0]['NAME'];

            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();

            return $this->render('CabinetBundle:Food:user_information.html.twig', $template_parameters);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function mainmenuAction(Request $request)
    {
        try{
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
                'details_of_complex' => $details_of_complex,
                'all_groups_of_meal' => $all_groups_of_meal,
                'meals_of_group' => $meals_of_group
            ]);

        } catch (Exception $e){
            return new Response($e->getMessage());
        }
    }

    public function classesAction(Request $request)
    {
        try{
            $parameters = [
                'school_id' => $request->get('school_id')
            ];

            $all_classes = DBFood::getInstance()->getClassInfo($parameters, false);

            return $this->render('CabinetBundle:Food/zakaz:zakaz_classes.html.twig', [
                'classes' => $all_classes
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
                'food_info_by_class' => $food_info_by_class,
                'conclusion' => $conclusion,
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
                'food_info_by_period' => $food_info_by_period,
                'conclusion_by_period' => $conclusion_food_by_period,
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function firstAction(Request $request)
    {
        try{
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
                'class_info' => $class_result,
                'food_info_by_period' => $food_info_by_period,
                'parameters' => $parameters,
                'conclusion_food_rep_first' => $conclusion_food_rep_first
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function secondAction(Request $request)
    {
        try{
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
                'class_info' => $class_result,
                'food_info_by_period' => $food_info_by_period,
                'parameters' => $parameters,
                'conclusion_food_rep_second' => $conclusion_food_rep_second
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function thirdAction(Request $request)
    {
        try{
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
                'food_info_by_period' => $food_info_by_period,
                'parameters' => $parameters,
                'conclusion_food_rep_third' => $conclusion_food_rep_third
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

}
