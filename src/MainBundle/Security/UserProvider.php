<?php

namespace MainBundle\Security;

use MainBundle\Utils\DB;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        $userData = DB::getInstance()->getUserData($username);

        if ($userData) {
            $password = array_key_exists('pass', $userData) ? $userData['pass'] : null;
            $role_id = array_key_exists('role_id', $userData) ? $userData['role_id'] : null;
            switch($role_id){
                case 1 : $role = ['ROLE_PUPIL']; break;
                case 2 : $role = ['ROLE_TEACHER']; break;
                case 3 : $role = ['ROLE_BOSS']; break;
                case 4 : $role = ['ROLE_FOOD']; break;
                case 5 : $role = ['ROLE_CLIENT']; break;
                default: throw new UnsupportedUserException(sprintf('User "%s" has a wrong ROLE_ID: "%s".', $username, $userData['ROLE_ID']));
            }
            return new User(
                $username,
                $password,
                $role,
                $userData['usr_id'],
                $userData['usr_name'],
                $userData['tlph'],
                $userData['email'],
                $userData['role_id'],
                $userData['limit'],
                $userData['prt_id'],
                $userData['prt_name'],
                $userData['blnc']
            );
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'MainBundle\Security\User';
    }
}