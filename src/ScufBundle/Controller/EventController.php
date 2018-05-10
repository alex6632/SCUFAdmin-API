<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Event;
use ScufBundle\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class EventController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"event"})
     * @Rest\Get("/events/{userID}")
     */
    public function listEventsAction($userID)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $events = $em->getRepository('ScufBundle:Event')->findByUser($userID);
        //$userList = $em->getRepository('ScufBundle:Event')->findAll();
        return $events;
    }

    /**
     * @Rest\View(serializerGroups={"event"}, statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/event/create/{userID}")
     */
    public function createEventAction(Request $request, $userID)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $em->getRepository('ScufBundle:User')->find($userID);
            $event->setUser($user);
            $em->persist($event);
            $em->flush();
            $message = array(
                'type' => 'success',
                'message' => 'L\'événement a bien été enregistré',
                'event' => $event,
            );
            return $message;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\DELETE("/action/delete/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $action = $em->getRepository('ScufBundle:Action')->find($id);

        if (empty($action)) {
            return $this->ActionNotFound();
        }

        $em->remove($action);
        $em->flush();

        $msg = array(
            'message'  => 'L\'action a bien été supprimée.',
            'id' => $id,
            'user' => $action->getUser()->getId()
        );
        return new JsonResponse($msg);
    }


    /**
     * @Rest\View()
     * @Rest\Put("/action/update/{type}/{id}")
     */
    public function putAction(Request $request)
    {
        return $this->editAction($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("action/update/{type}/{id}")
     */
    public function patchAction(Request $request)
    {
        return $this->editAction($request, false);
    }


    private function editAction(Request $request,  $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ScufBundle:User')->find($request->get('id'));

        if (empty($user)) {
            return $this->ActionNotFound();
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

    private function ActionNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Aucune action trouvée.');
    }


}
