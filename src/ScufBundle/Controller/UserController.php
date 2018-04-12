<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use FOS\RestBundle\Controller\Annotations\Get;

class UserController extends Controller
{

    /**
     * @Get("/user")
     */
    public function listUserAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $userList = $em->getRepository('ScufBundle:User')->findAll();
        $response = [];
        foreach ($userList as $list) {
            $response[] = [
                'id' => $list->getId(),
                'username' => $list->getUsername(),
                'firstname' => $list->getFirstname(),
                'lastname' => $list->getLastname(),
                'role' => $list->getRole(),
                'superior' => $list->getSuperior(),
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @Get("/user/{id}")
     */
    public function oneUserAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('ScufBundle:User')->find($id);

        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $response = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'role' => $user->getRole(),
            'superior' => $user->getSuperior(),
            'access' => $user->getAccess(),
            'hoursTodo' => $user->getHoursTodo(),
            'hoursDone' => $user->getHoursDone(),
            'hoursPlanified' => $user->getHoursPlanified(),
            'hoursPlanifiedByMe' => $user->getHoursPlanifiedByMe(),
            'overtime' => $user->getOvertime(),
            'action' => $user->getAction(),
        ];
        return new JsonResponse($response);
    }

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
