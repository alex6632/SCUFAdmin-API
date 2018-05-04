<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Action;
use ScufBundle\Form\Action\LeaveType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class ActionController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Get("/actions")
     */
    public function listAction()
    {
        //$em = $this->getDoctrine()->getEntityManager();
        $em = $this->get('doctrine.orm.entity_manager');
        $userList = $em->getRepository('ScufBundle:User')->findAll();
        return $userList;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/action/{id}")
     */
    public function oneAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $action = $em->getRepository('ScufBundle:Action')->find($id);
//        $superior = $user->getSuperior();
//        $superiorName = $superior->getFirstname()." ".$superior->getLastname();

        if (empty($action)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('L\'utilisateur n\'a pas pu être trouvé');
        }
        return $action;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/action/create/{userID}")
     */
    public function createAction(Request $request, $userID)
    {
        $action = new Action();
        $form = $this->createForm(LeaveType::class, $action);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $superior = $em->getRepository('ScufBundle:User')->getSuperior($userID);
            dump($superior);
            die();
            $action->setCreated(new \DateTime('now'));
            $action->setStatus(2); //By default, status is "In progress"
            $action->setView(0);
            $action->setRecipient($superior);
            $em->persist($action);
            $em->flush();
            $message = array(
                'type' => 'success',
                'message' => 'Votre demande a bien été enregistré. Vous recevrez une notification
                 lorsque son statut évoluera.',
                'action' => $action,
            );
            return $message;
        } else {
            return $form;
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
            'message'  => 'L\'utilisateur a bien été supprimé.',
            'id' => $id
        );
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\Put("/user/update/{id}")
     */
    public function editUserAction(Request $request)
    {
        return $this->editUser($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/user/update/{id}")
     */
    public function patchUserAction(Request $request)
    {
        return $this->editUser($request, false);
    }


    private function editUser(Request $request,  $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ScufBundle:User')->find($request->get('id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        if($clearMissing) {
            $options = ['validation_groups'=>['Default', 'FullUpdate']];
        } else {
            $options = [];
        }

        $form = $this->createForm(UserType::class, $user, $options);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            if(!empty($user->getPlainPassword())) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encoded);
            }
            $em->merge($user);
            $em->flush();
            $msg = array(
                'type'           => 'success',
                'message'            => 'L\'utilisateur "'.$user->getFirstname().' ' .$user->getLastname() .'" a bien été édité.',
                'firstname'      => $user->getFirstname(),
                'lastname'       => $user->getLastname(),
                'username'       => $user->getUsername(),
                'role'           => $user->getRole(),
                'superior'       => $user->getSuperior(),
                'access'         => $user->getAccess(),
                'hoursPlanified' => $user->getHoursPlanified(),
                'id'             => $user->getId()
            );
            return new JsonResponse($msg);
        } else {
            return $form;
        }
    }

    private function userNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('L\'utilisateur n\'a pas pu être trouvé');
    }


}
