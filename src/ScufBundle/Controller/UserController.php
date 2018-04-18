<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\User;
use ScufBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Get("/users")
     */
    public function listUsersAction()
    {
        //$em = $this->getDoctrine()->getEntityManager();
        $em = $this->get('doctrine.orm.entity_manager');
        $userList = $em->getRepository('ScufBundle:User')->findAll();
        return $userList;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/user/{id}")
     */
    public function oneUserAction($id)
    {
        //$em = $this->getDoctrine()->getEntityManager();
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ScufBundle:User')->find($id);

        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return $user;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/user/create")
     */
    public function createUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/user/login")
     */
    public function loginAction()
    {
        return new JsonResponse('login');
    }

    /**
     * @Rest\View()
     * @Rest\Get("/user/logout")
     */
    public function logoutAction()
    {
        return new JsonResponse('logout');
    }
}
