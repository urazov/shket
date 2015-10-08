<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NewsController extends Controller
{
    public function indexAction($news_title)
    {
        return $this->render('MainBundle:News:' . $news_title . '.html.twig');
    }
}
