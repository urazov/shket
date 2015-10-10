<?php

namespace CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FoodController extends Controller
{
    public function indexAction()
    {
        return $this->render('CabinetBundle:Food:index.html.twig');
    }
}
