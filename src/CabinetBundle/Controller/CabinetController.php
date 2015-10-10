<?php

namespace CabinetBundle\Controller;

use Exception;
use MainBundle\Utils\DB;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CabinetController extends Controller
{
    public function indexAction()
    {
        if($user = $this->getUser()){
            $roles = $user->getRoles();
            if(in_array('ROLE_PUPIL', $roles)) return $this->redirectToRoute('cabinet_pupil');
            if(in_array('ROLE_TEACHER', $roles)) return $this->redirectToRoute('cabinet_teacher');
            if(in_array('ROLE_BOSS', $roles)) return $this->redirectToRoute('cabinet_boss');
            if(in_array('ROLE_FOOD', $roles)) return $this->redirectToRoute('cabinet_food');
            if(in_array('ROLE_CLIENT', $roles)) return $this->redirectToRoute('cabinet_client');
        }
        return $this->redirectToRoute('main_homepage');
    }

    public function userInformationAction()
    {
        try{
            $user = $this->getUser();

            //request for teacher
            $query = "SELECT DISTINCT s.SCL_ID, s.NAME, c.cls_id, c.name
                  FROM CS_SHKET.USR u
                       INNER JOIN CS_SHKET.USER_IN_SCL_CLS uc on u.USR_ID = uc.USR_ID
                       INNER JOIN CS_SHKET.CLS c on c.SCL_ID = uc.SCL_ID AND c.CLS_ID = uc.cls_id
                       INNER JOIN CS_SHKET.scl s on s.SCL_ID = uc.SCL_ID
                  WHERE u.ROLE_ID = ? and u.del <> 1 and uc.del<> 1 and c.del <> 1 and s.del <> 1
                  AND u.USR_ID = ?";

            $result = DB::getInstance()->getAll($query, [$user->getRoleId(), $user->getId()]);

            $template_parameters['info'] = $result;
            $template_parameters['default_scl_id'] = $result[0]['SCL_ID'];
            $template_parameters['default_scl_name'] = $result[0]['NAME'];

            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();

            return $this->render('CabinetBundle:Cabinet:user_information.html.twig', $template_parameters);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }
}
