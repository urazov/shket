<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction($page = null)
    {
        $page = is_null($page) ? 'index' : $page;
        return $this->render('MainBundle:Main:'. $page .'.html.twig');
    }
}
