<?php

namespace MainBundle\Controller;

use MainBundle\Utils\DB;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    public function indexAction($page = null)
    {
        $page = is_null($page) ? 'index' : $page;
        return $this->render('MainBundle:Main:'. $page .'.html.twig');
    }

    public function countUserAction()
    {
        $count = DB::getInstance()->getCountUser()['cnt'];
        return new Response($count);
    }

    public function countSchoolAction()
    {
        $count = DB::getInstance()->getCountSchool()['cnt'];
        return new Response($count);
    }
}
