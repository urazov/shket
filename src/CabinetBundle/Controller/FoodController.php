<?php

namespace CabinetBundle\Controller;

use CabinetBundle\Repositories\Food\DBFood;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FoodController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();

        $parameters = [
            'current_date' => date("d.m.Y"),
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

}
