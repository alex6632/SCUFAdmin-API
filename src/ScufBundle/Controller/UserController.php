<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\User;
use ScufBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"user"})
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
     * @Rest\View(serializerGroups={"user"})
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
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * @Rest\Post("/user/create")
     */
    public function createUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $user->setHoursTodo(0);
            $user->setHoursDone(0);
            $user->setHoursPlanifiedByMe(0);
            $user->setOvertime(0);
            $em->persist($user);
            $em->flush();
            $msg = array(
                'type' => 'success',
                'msg' => 'Le user a bien été ajouté.',
                'user' => $user,
            );
            return new JsonResponse($msg);
        } else {
            //return $form;
            $msg = array(
                'type' => 'error',
                'debug' => '[Error] [create|user] See UserController/createUserAction',
                'msg' => 'Erreur lors de la création de l\'utilisateur. Veuillez réssayer.',
                'user' => $user,
                'form' => $form
            );
            return new JsonResponse($msg);
        }
    }

    /**
     * @Rest\View()
     * @Rest\DELETE("/user/delete/{id}")
     */
    public function deleteUserAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $setting = $em->getRepository('ScufBundle:User')->find($id);
        $em->remove($setting);
        $em->flush();

        $msg = array(
            'type' => 'success',
            'msg'  => 'L\'utilisateur a bien été supprimé.',
            'id' => $id
        );
        return new JsonResponse($msg);
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
