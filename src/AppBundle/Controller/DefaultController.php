<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/test", name="homepage")
     */
    public function indexAction(Request $request)
    {
	$host="192.168.88.254";
        $user="sa";
        $pwd="VFLFUFCRFH";
        $db_name = "DB_TEST";

	$db = new \PDO("dblib:host=192.168.88.254;dbname=DB_TEST", $user, $pwd);

	var_dump($db);exit;
        exit;

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }
}
