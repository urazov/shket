<?php

namespace CabinetBundle\Controller;

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
        $user = $this->getUser();
        return new Response($user->getFullName());
    }
}
