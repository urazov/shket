<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction($page = null)
    {
        if(!is_null($page)){
            return $this->render('MainBundle:Main:'. $page .'.html.twig');
        }
        return $this->render('MainBundle:Main:index.html.twig');
    }
}
