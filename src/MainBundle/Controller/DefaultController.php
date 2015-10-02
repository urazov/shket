<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('MainBundle:Default:index.html.twig', array('name' => $name));
    }

    public function testAction()
    {
        var_dump(123);exit;
    }

}
