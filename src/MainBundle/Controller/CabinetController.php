<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CabinetController extends Controller
{
    public function indexAction()
    {
        return $this->render('MainBundle:Main:index.html.twig');
    }
}
