<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Food\DBFood;
use CabinetBundle\Repositories\Pupil\DBPupil;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PupilController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();

        $result = DBPupil::getInstance()->getUserInfo($user);

        $this->get('session')->set('inf_bal', $result[0]['inf_bal']);
        $this->get('session')->set('inf_ent', $result[0]['inf_ent']);
        $this->get('session')->set('inf_eat', $result[0]['inf_eat']);
        $this->get('session')->set('trf_id', $result[0]['TRF_ID']);
        $this->get('session')->set('balance', $user->getBalance());
        $this->get('session')->set('scl_id', $result[0]['scl_id']);

        $types = DBPupil::getInstance()->getAllType(['user_id' => $user->getId()]);

        $parameters = [
            'current_date' => date("d-m-Y"),
            'money_types' => $types
        ];

        return $this->render('CabinetBundle:Pupil:index.html.twig', $parameters);
    }

    public function userInformationAction()
    {
        try{

            $user = $this->getUser();

            $result = DBPupil::getInstance()->getUserInfo($user);

            $available_tarifs = DBPupil::getInstance()->getAvailableTarifs($user->getParentId());

            $template_parameters['usr_id'] = $user->getId();
            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['school'] = $result[0]['scl_name'];
            $template_parameters['class_name'] = $result[0]['cls_name'];
            $template_parameters['parent_name'] = $user->getParentName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();
            $template_parameters['trf_name'] = $result[0]['NAME'];
            $template_parameters['trf_cost'] = $result[0]['COST'];
            $template_parameters['trf_bal'] = $result[0]['inf_bal'];
            $template_parameters['parent_id'] = $user->getParentId();
            $template_parameters['balance'] = $user->getBalance();
            $template_parameters['limit'] = $user->getLimit();
            $template_parameters['available_tarifs'] = $available_tarifs;

            return $this->render('CabinetBundle:Pupil:user_information.html.twig', $template_parameters);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function menuAction(Request $request)
    {
        try{
            $parameters = [
                'date' => $request->get('date'),
                'inf_eat' => $this->get('session')->get('inf_eat'),
                'balance' => $this->get('session')->get('balance'),
                'school_id' => $this->get('session')->get('scl_id'),
            ];

            if(empty($parameters['inf_eat'])){
                return new Response("<div class='row report-subtitle'>В вашем тарифе отсутсвует данная функциональность</div>");
            }

            if($parameters['balance'] < 0){
                return new Response("<div class='row report-subtitle'>На вашем счете недостаточно средств для просмотра данной информации</div>");
            }

            $all_complex_on_date = DBFood::getInstance()->getAllComplexOnDate($parameters);
            foreach($all_complex_on_date as $idx => $complex){
                $details_of_complex[$idx] = DBFood::getInstance()->getDetailsOfComplex(array_merge($parameters, ['meal_id' => $complex['MEAL_ID']]));
            }

            $all_groups_of_meal = DBFood::getInstance()->getAllGroupsOfMeals($parameters);
            foreach($all_groups_of_meal as $idx => $group){
                $meals_of_group[$idx] = DBFood::getInstance()->getAllMealsOfGroup(array_merge($parameters, ['gmeal_id' => $group['GMEAL_ID']]));
            }

            return $this->render('CabinetBundle:Pupil/menu:mainmenu_report.html.twig', [
                'all_complex_on_date' => $all_complex_on_date,
                'details_of_complex' => isset($details_of_complex) ? $details_of_complex : [],
                'all_groups_of_meal' => $all_groups_of_meal,
                'meals_of_group' => isset($meals_of_group) ? $meals_of_group : [],
                'params' => $parameters
            ]);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function pitanieAction(Request $request)
    {
        try{
            $user = $this->getUser();

            $parameters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'inf_eat' => $this->get('session')->get('inf_eat'),
                'balance' => $this->get('session')->get('balance'),
                'school_id' => $this->get('session')->get('scl_id'),
                'user_id' => $user->getId()
            ];

            if(empty($parameters['inf_eat'])){
                return new Response("<div class='row report-subtitle'>В вашем тарифе отсутсвует данная функциональность</div>");
            }

            if($parameters['balance'] < 0){
                return new Response("<div class='row report-subtitle'>На вашем счете недостаточно средств для просмотра данной информации</div>");
            }

            $result = DBPupil::getInstance()->getPupilPitanie($parameters);

            return $this->render('CabinetBundle:Pupil/pitanie:pitanie_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function enterAction(Request $request)
    {
        try{
            $user = $this->getUser();

            $parameters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'inf_ent' => $this->get('session')->get('inf_ent'),
                'balance' => $this->get('session')->get('balance'),
                'school_id' => $this->get('session')->get('scl_id'),
                'user_id' => $user->getId()
            ];

            if(empty($parameters['inf_ent'])){
                return new Response("<div class='row report-subtitle'>В вашем тарифе отсутсвует данная функциональность</div>");
            }

            if($parameters['balance'] < 0){
                return new Response("<div class='row report-subtitle'>На вашем счете недостаточно средств для просмотра данной информации</div>");
            }

            $result = DBPupil::getInstance()->getPupilEnter($parameters);

            return $this->render('CabinetBundle:Pupil/enter:enter_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function moneyAction(Request $request)
    {
        try{
            $user = $this->getUser();

            $parameters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'inf_bal' => $this->get('session')->get('inf_bal'),
                'balance' => $this->get('session')->get('balance'),
                'school_id' => $this->get('session')->get('scl_id'),
                'user_id' => $user->getId(),
                'type' =>$request->get('type')
            ];

            if(empty($parameters['inf_bal'])){
                return new Response("<div class='row report-subtitle'>В вашем тарифе отсутсвует данная функциональность</div>");
            }

            if($parameters['balance'] < 0){
                return new Response("<div class='row report-subtitle'>На вашем счете недостаточно средств для просмотра данной информации</div>");
            }

            $result = DBPupil::getInstance()->getPupilMoney($parameters);

            return $this->render('CabinetBundle:Pupil/money:money_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

    public function updateLimitAction(Request $request)
    {
        try{
            $user_id = $this->getUser()->getId();
            $new_limit = $request->get('limit');

            DBPupil::getInstance()->updateLimit($user_id, $new_limit);

            return new Response("1");
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }

}
