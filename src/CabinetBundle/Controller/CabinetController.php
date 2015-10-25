<?php

namespace CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CabinetBundle\Repositories\Teacher\DBTeacher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Exception;

class CabinetController extends Controller
{
    public function indexAction()
    {
        if($user = $this->getUser()){
            $roles = $user->getRoles();
            if(in_array('ROLE_PUPIL', $roles)) return $this->redirectToRoute('cabinet_pupil');
            if(in_array('ROLE_TEACHER', $roles)) return $this->redirectToRoute('cabinet_teacher');
            if(in_array('ROLE_BOSS', $roles)) return $this->redirectToRoute('cabinet_boss');
            if(in_array('ROLE_FOOD', $roles)) return $this->redirectToRoute('cabinet_food');
            if(in_array('ROLE_CLIENT', $roles)) return $this->redirectToRoute('cabinet_client');
        }
        return $this->redirectToRoute('main_homepage');
    }

    public function updateImageAction(Request $request)
    {
        $context = [
            'time' => date('Y-m-d H:i:s'),
            'function' => __METHOD__
        ];

        try{
            $user = $this->getUser();
            if(!$user) throw new AuthenticationException('User was not founded');

            $context['user_id'] = $this->getUser()->getId();

            foreach($request->files as $uploadedFile) {
                if($uploadedFile instanceof UploadedFile){
                    $path = realpath($this->container->getParameter('kernel.root_dir').'/../web/users/').'/'.$user->getId();
                    $uploadedFile->move($path, 'avatar.jpg');
                }
            }

            return new RedirectResponse($this->generateUrl('cabinet_detecting'));
        } catch (AuthenticationException $e) {
            $this->get('session')->getFlashBag()->add('notice', 'Необходимо авторизоваться заново');
            return new RedirectResponse($this->generateUrl('main_homepage'));
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            $this->get('session')->getFlashBag()->add('notice', 'Ошибка загрузки файла. Файл должен быть не больше ' . ini_get('upload_max_filesize'));
            return new RedirectResponse($this->generateUrl('cabinet_detecting'));
        }
    }

    public function updateInfoAction(Request $request)
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
                'user_id' => $user->getId(),
                'name' => $request->get('name'),
                'phone' => $request->get('phone'),
                'email' => $request->get('email')
            ];

            DBTeacher::getInstance()->updateInfo($parameters);

            return new Response(1);
        } catch (Exception $e) {
            $this->get('logger')->error($e->getMessage(), $context);
            return false;
        }
    }
}
