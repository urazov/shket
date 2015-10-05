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
        $host="Vpn.shket-it.ru:1433";
        $user="sa";
        $pwd="VFLFUFCRFH";
        $db_name = "DB_SHKET";

        $conn = mssql_connect ($host,$user,$pwd)
        or die ("Can't connect to Microsoft SQL Server");

        mssql_select_db($db_name, $conn) or die ("Can't select databes");


        var_dump($conn);
        exit;

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }
}
