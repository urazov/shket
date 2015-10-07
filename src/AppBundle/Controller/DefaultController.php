<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/testdb", name="homepage")
     */
    public function testAction(Request $request)
    {
	    $host="192.168.88.254";
        $user="sa";
        $pwd="VFLFUFCRFH";
        $db_name = "DB_TEST";

	    $db = new \PDO("dblib:host=$host;dbname=$db_name", $user, $pwd);

        if(!$db){
            $status = 'fail';
        } else {
            $status = 'ok';
        }

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'status' => $status
        ));
    }
}
