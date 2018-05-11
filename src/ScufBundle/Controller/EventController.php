<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Event;
use ScufBundle\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Rest\View(serializerGroups={"event"})
     * @Rest\DELETE("/event/delete/{id}")
     */
    public function deleteEventAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $event = $em->getRepository('ScufBundle:Event')->find($id);

        if (empty($event)) {
            return $this->EventNotFound();
        }

        $em->remove($event);
        $em->flush();

        $message = array(
            'message'  => 'L\'événement a bien été supprimé.',
        );
        return $message;
    }


    /**
     * @Rest\View(serializerGroups={"event"})
     * @Rest\Put("/event/update/{id}")
     */
    public function putEventAction(Request $request)
    {
        return $this->editEventAction($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"event"})
     * @Rest\Patch("event/update/{id}")
     */
    public function patchEventAction(Request $request)
    {
        return $this->editEventAction($request, false);
    }


    private function editEventAction(Request $request,  $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('ScufBundle:Event')->find($request->get('id'));

        if (empty($event)) {
            return $this->EventNotFound();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            // HACK to set correct value to allDay field
            $starTime = substr($request->request->get('start'), 11, 5);
            $allDay = $starTime == '00:00' ? true : false;
            $event->setAllDay($allDay);
            $em->persist($event);
            $em->flush();
            $message = array(
                'type' => 'success',
                'message' => 'L\'événement a bien été mis à jour',
                'event' => $event,
            );
            return $message;
        } else {
            return $form;
        }
    }

    private function EventNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Aucun événement trouvé.');
    }


}
