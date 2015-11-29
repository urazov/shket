<?php

namespace CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CabinetBundle\Repositories\Boss\DBBoss;
use CabinetBundle\Repositories\Teacher\DBTeacher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BossController extends Controller
{
    public function indexAction()
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $result = DBBoss::getInstance()->getUserInfo($user);

            $all_classes = DBBoss::getInstance()->getAllClasses($user);

            $parameters = [
                'current_year' => date("Y", time()),
                'current_month' => date("m", time()),
                'current_date' => date("d-m-Y"),
                'last_date' => date("d-m-Y", time() - 7 * 24 * 60 * 60),
                'mail_name' => $user->getFullName(),
                'mail_phone' => $user->getPhone(),
                'mail_email' => $user->getEmail(),
                'school' => $result,
                'classes' => $all_classes
            ];

            return $this->render('CabinetBundle:Boss:index.html.twig', $parameters);
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function userInformationAction()
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $result = DBBoss::getInstance()->getUserInfo($user);

            $template_parameters['info'] = $result;

            $template_parameters['full_name'] = $user->getFullName();
            $template_parameters['phone'] = $user->getPhone();
            $template_parameters['email'] = $user->getEmail();
            $template_parameters['usr_id'] = $user->getId();
            $ava_path = realpath($this->container->getParameter('kernel.root_dir').'/../web/users/').'/'.$user->getId().'/avatar.jpg';
            if(file_exists($ava_path)){
                $template_parameters['avatar'] = $ava_path;
            }

            return $this->render('CabinetBundle:Teacher:user_information.html.twig', $template_parameters);
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function tabelAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $info = DBBoss::getInstance()->getUserInfo($user);

            $parameters = [
                'month' => $request->get('month'),
                'year' => $request->get('year'),
                'school_id' => $info[0]['SCL_ID'],
                'class_id' => $request->get('class_id')
            ];

            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $parameters['month'], $parameters['year']);

            $pupils = DBBoss::getInstance()->getAllPupil($parameters);

            $parameters['date_to'] = $parameters['year'] . '-' . ($parameters['month']+1) . '-01';
            $parameters['date_from'] = $parameters['year'] . '-' . $parameters['month'] . '-01';

            foreach($pupils as $pupil){
                $user_total = 0;
                $food_user_date = [];
                $parameters['pupil_id'] = $pupil['usr_id'];
                $food_date_info = DBBoss::getInstance()->getFoodCountForBjtPupil($parameters);
                foreach($food_date_info as $key => $date){
                    $food_user_date[$date['date']] = $date['cnt'];
                }

                for($day = 1; $day <= $days_in_month; $day++){
                    $date = $parameters['year'] . '-' . $parameters['month'] . '-' . ($day<10 ? '0'.$day : $day);
                    if(array_key_exists($date, $food_user_date)){
                        $result[$pupil['usr_id']][$day] = $food_user_date[$date];
                        $user_total += $food_user_date[$date];
                    } else {
                        $result[$pupil['usr_id']][$day] = 0;
                    }
                }
                $result[$pupil['usr_id']][$day] = $user_total;
            }

            $itog_total = 0;
            $parameters['date_to'] = $parameters['year'] . '-' . ($parameters['month']+1) . '-01';
            $parameters['date_from'] = $parameters['year'] . '-' . $parameters['month'] . '-01';
            $itog_result = DBBoss::getInstance()->getFoodBjtCountItog($parameters);
            $itog_result_date = [];
            foreach($itog_result as $key => $date){
                $itog_result_date[$date['date']] = $date['cnt'];
            }
            for($day = 1; $day <= $days_in_month; $day++){
                $date = $parameters['year'] . '-' . $parameters['month'] . '-' . ($day<10 ? '0'.$day : $day);
                if(array_key_exists($date, $itog_result_date)){
                    $itog[$day] = $itog_result_date[$date];
                    $itog_total += $itog[$day];
                } else {
                    $itog[$day] = 0;
                }
            }
            $itog[$day] = $itog_total;

            return $this->render('CabinetBundle:Boss/rep_tabel_cat:rep_tabel_cat_report.html.twig', [
                'result' => isset($result) ? $result : [],
                'pupils' => $pupils,
                'days' => $days_in_month,
                'total' => isset($itog) ? $itog : []
            ]);

        } catch (Exception $e){

        }
    }
}
