<?php

namespace CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TeacherController extends Controller
{
    public function indexAction()
    {
        return $this->render('CabinetBundle:Teacher:index.html.twig');
    }
}
