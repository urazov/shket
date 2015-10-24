<?php

namespace MainBundle\Controller;

use Exception;
use MainBundle\Utils\DB;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        try{
            $count = DB::getInstance()->getCountUser()['cnt'];
            return new Response($count);
        } catch (Exception $e) {
            return new Response(11255);
        }
    }

    public function countSchoolAction()
    {
        try{
            $count = DB::getInstance()->getCountSchool()['cnt'];
            return new Response($count);
        } catch (Exception $e) {
            return new Response(17);
        }
    }

    public function postAction(Request $request)
    {
        try{
            $parameters['fio'] = $request->get('fio');
            $parameters['phone'] = $request->get('phone');
            $parameters['email'] = $request->get('email');
            $parameters['msg'] = $request->get('msg');

            $message = Swift_Message::newInstance()
                ->setSubject('Письмо с сайта')
                ->setFrom('info@shket-it.ru', 'ШКЭТ')
                ->setTo('info@shket-it.ru')
                ->setBody($this->renderView(
                    'MainBundle:Main:mail.html.twig',
                    array('params' => $parameters)
                ),'text/html');

            $this->get('mailer')->send($message);

            return new Response('Отправлено');
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage());
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }
}
