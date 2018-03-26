<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends Controller
{

    /**
     * @Route("/register", name="register")
     */
    public function registerAction()
    {
        return new JsonResponse('register');
    }
//    public function registerAction(UserPasswordEncoderInterface $encoder)
//    {
        // whatever *your* User object is
//        $user = new User();
//        $plainPassword = 'password';
//        $encoded = $encoder->encodePassword($user, $plainPassword);

//        $user->setPassword($encoded);
//    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        return new JsonResponse('login');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return new JsonResponse('logout');
    }
}
