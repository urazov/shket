<?php

namespace CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CabinetBundle\Repositories\Boss\DBBoss;
use CabinetBundle\Repositories\Pupil\DBPupil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

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
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function enterPupilAction(Request $request)
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
                'school_id' => $info[0]['SCL_ID'],
                'class_id' => $request->get('class_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = DBBoss::getInstance()->getPupilEntersInfo($parameters);

            return $this->render('CabinetBundle:Boss/ent_pupil:ent_pupil_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function enterStaffAction(Request $request)
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
                'school_id' => $info[0]['SCL_ID'],
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = DBBoss::getInstance()->getStaffEntersInfo($parameters);

            return $this->render('CabinetBundle:Boss/ent_staff:ent_staff_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function enterTeacherAction(Request $request)
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
                'school_id' => $info[0]['SCL_ID'],
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = DBBoss::getInstance()->getTeacherEntersInfo($parameters);

            return $this->render('CabinetBundle:Boss/ent_teacher:ent_teacher_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function enterPupilDetailAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $parameters = [
                'user_id' => $request->get('user_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to')
            ];

            $result = DBPupil::getInstance()->getPupilEnter($parameters);

            return $this->render('CabinetBundle:Pupil/enter:enter_report.html.twig', [
                'result' => $result
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function listPupilsAction(Request $request)
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

            $parameters['class_id'] = $request->get('class_id');
            $parameters['school_id'] = $info[0]['SCL_ID'];

            $pupils = DBBoss::getInstance()->getAllPupil($parameters);

            return $this->render('CabinetBundle:Boss/list_class:list_class_report.html.twig', [
                'pupils' => $pupils
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function pupilsBalanceAction(Request $request)
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

            $parameters['class_id'] = $request->get('class_id');
            $parameters['school_id'] = $info[0]['SCL_ID'];

            $pupils = DBBoss::getInstance()->getPupilsBalance($parameters);

            return $this->render('CabinetBundle:Boss/balance:balance_accounts_report.html.twig', [
                'pupils' => $pupils,
                'date_from' => date("d-m-Y", time() - 14 * 24 * 60 * 60),
                'date_to' => date("d-m-Y")
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function balanceDetailsAction($user_id, $date_from, $date_to, $acr_id)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__,
            'user_detail' => $user_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'acr_id' => $acr_id,
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');
            $context['user_id'] = $this->getUser()->getId();

            $parameters = [
                'user_id' => $user_id,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'acr_id' => $acr_id,
            ];

            $types = DBPupil::getInstance()->getAllType(['user_id' => $user_id]);
            $pupil = DBPupil::getInstance()->getUserInfoById($user_id);
            $info = DBBoss::getInstance()->getBalanceDetail($parameters);

            $cls_name = $pupil[0]['cls_name'];
            $full_name = $pupil[0]['user_name'];

            return $this->render('CabinetBundle:Boss/balance:balance_detail_report.html.twig', [
                'info' => $info,
                'money_types' => $types,
                'date_from' => $parameters['date_from'],
                'date_to' => $parameters['date_to'],
                'curr_acr_id' => $parameters['acr_id'],
                'full_name' => $full_name,
                'cls_name' => $cls_name,
                'user_id' => $user_id
            ]);

        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return new Response('Ошибка. Обратитесь к администратору');
        }
    }

    public function getLinkAction(Request $request)
    {
        $url = $this->generateUrl('boss_balance_detail', array(
            'user_id' => $request->get('user_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'acr_id' => $request->get('acr_id')
        ));

        return new Response($url);
    }

}
