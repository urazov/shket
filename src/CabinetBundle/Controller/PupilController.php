<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Pupil\DBPupil;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        return $this->render('CabinetBundle:Pupil:index.html.twig');
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
}
