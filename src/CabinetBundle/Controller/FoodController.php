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

            return $this->render('CabinetBundle:Food:mainmenu_report.html.twig', [
                'all_complex_on_date' => $all_complex_on_date,
                'details_of_complex' => $details_of_complex,
                'all_groups_of_meal' => $all_groups_of_meal,
                'meals_of_group' => $meals_of_group
            ]);

        } catch (Exception $e){
            return new Response($e->getMessage());
        }
    }

}
