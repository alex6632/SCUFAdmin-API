<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Action;
use ScufBundle\Form\ActionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class ActionController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/actions/{type}/{userID}")
     */
    public function listByUserAction($type, $userID)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $actions = $em->getRepository('ScufBundle:Action')->findActionByTypeAndUser($type, $userID);

        if (empty($actions)) {
            return [
                'success' => false,
                'message' => 'Aucune demande n\'a été soumise pour le moment !'
            ];
        }

        $formattedActions = [];
        foreach ($actions as $action) {
            $updated = !is_null($action['updated']) ? $action['updated']->format('d-m-Y à H:i') : "/";
            $start = $action['start']->format('d-m-Y à H:i');
            $startDate = $action['start']->format('d-m-Y');
            $end = $action['end']->format('d-m-Y à H:i');
            $endDate = $action['end']->format('d-m-Y');
            $startHours = $action['start']->format('H:i');
            $endHours = $action['end']->format('H:i');
            $recipient = $em->getRepository('ScufBundle:User')->findOneById($action['recipient']);
            $recipientFirstName = $recipient->getFirstname();
            $recipientLastName = $recipient->getLastname();

            $formattedActions[] = [
                'success' => true,
                'id' => $action['id'],
                'user' => $action['user'],
                'recipientFirstName' => $recipientFirstName,
                'recipientLastName' => $recipientLastName,
                'created' => $action['created']->format('d-m-Y à H:i'),
                'updated' => $updated,
                'start' => $start,
                'startDate' => $startDate,
                'end' => $end,
                'endDate' => $endDate,
                'startHours' => $startHours,
                'endHours' => $endHours,
                'justification' => $action['justification'],
                'status' => $action['status'],
            ];
        }
        return [
            'success' => true,
            'list' => $formattedActions,
        ];
    }

    /**
     * @Rest\View()
     * @Rest\Get("/notifications/{userID}")
     */
    public function notificationAction($userID)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $notifications = $em->getRepository('ScufBundle:Action')->findNotificationByUser($userID);
        if (empty($notifications)) {
            return $this->ActionNotFound();
        }
        $count = count($notifications);
        $formattedActions = [];
        foreach ($notifications as $notification) {
            $updated = !is_null($notification['updated']) ? $notification['created']->format('d/m/Y à H:i') : "";
            $startDate = $notification['start']->format('d/m/Y');
            $endDate = $notification['end']->format('d/m/Y');
            $startHours = $notification['start']->format('H:i');
            $endHours = $notification['end']->format('H:i');
            $startUnformatted = $notification['start']->format('Y-m-d H:i:s');
            $endUnformatted = $notification['end']->format('Y-m-d H:i:s');
            $recipient = $em->getRepository('ScufBundle:User')->findOneById($notification['recipient']);
            $recipientFirstName = $recipient->getFirstname();
            $recipientLastName = $recipient->getLastname();
            $user = $em->getRepository('ScufBundle:User')->findOneById($notification['user']);
            $userFirstName = $user->getFirstname();
            $userLastName = $user->getLastname();

            $formattedActions[] = [
                'id'                 => $notification['id'],
                'userID'             => $notification['user'],
                'userFirstName'      => $userFirstName,
                'userLastName'       => $userLastName,
                'recipientFirstName' => $recipientFirstName,
                'recipientLastName'  => $recipientLastName,
                'created'            => $notification['created']->format('d/m/Y à H:i'),
                'updated'            => $updated,
                'startDate'          => $startDate,
                'endDate'            => $endDate,
                'startHours'         => $startHours,
                'endHours'           => $endHours,
                'startUnformatted'   => $startUnformatted,
                'endUnformatted'     => $endUnformatted,
                'justification'      => $notification['justification'],
                'location'           => $notification['location'],
                'status'             => $notification['status'],
                'view'               => $notification['view'],
                'type'               => $notification['type'],
            ];
        }
        return [
            'count'                  => $count,
            'list'                   => $formattedActions,
        ];
    }

    /**
     * @Rest\View()
     * @Rest\Get("/notifications/count/{userID}")
     */
    public function refreshNotificationAction($userID)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $countNotifications = $em->getRepository('ScufBundle:Action')->countNotificationByUser($userID);
        return $countNotifications;
    }

    /**
     * @Rest\View(serializerGroups={"action"}, statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/action/create/{type}/{userID}")
     */
    public function createAction(Request $request, $type, $userID)
    {
        $action = new Action();
        $form = $this->createForm(ActionType::class, $action);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $user = $em->getRepository('ScufBundle:User')->find($userID);

            if($type == "leave" || $type == "rest") {
                $superiorID = $em->getRepository('ScufBundle:User')->getSuperior($userID);
                $recipient = $em->getRepository('ScufBundle:User')->find($superiorID[0][1]);
                $action->setRecipient($recipient);
            }
            if($type == "rest") {
                // Get start & end date of form
                $startHours = substr($request->request->get('start'), 11, 2);
                $startMinutes = substr($request->request->get('start'), 14, 2);
                $endHours = substr($request->request->get('end'), 11, 2);
                $endMinutes = substr($request->request->get('end'), 14, 2);

                if($startMinutes == 00 || $startMinutes == 15 || $startMinutes == 30 || $startMinutes == 45) {
                    if($endMinutes == 00 || $endMinutes == 15 || $endMinutes == 30 || $endMinutes == 45) {
                        $start = ($startHours * 60) + $startMinutes;
                        $end = ($endHours * 60) + $endMinutes;
                    } else {
                        throw new \Exception('L\'heure de fin est incorrecte !');
                    }
                } else {
                    throw new \Exception('L\'heure de début est incorrecte !');
                }

                // Get rest value from database
                $overtime = $em->getRepository('ScufBundle:User')->findOneById($userID)->getOvertime();
                $coefficient = $em->getRepository('ScufBundle:Setting')->findOneBySlug('coeff')->getValue();
                $restOwned = number_format($overtime * $coefficient, 2);

                if($start < $end) {
                    $restWanted = $end - $start;
                    if($restWanted <= $restOwned) {
                        $newOvertime = number_format(($restOwned - $restWanted) / $coefficient, 2);
                        $user = $em->getRepository('ScufBundle:User')->findOneById($userID)->setOvertime($newOvertime);
                    } else {
                        throw new \Exception('Vous n\'avez pas cumulé assez d\'heures pour cette demande !');
                    }
                } else {
                    throw new \Exception('La date de fin doit être ultérieure à la date de début !');
                }
            }
            $now = new \DateTime('now');
            $now->setTimezone(new \DateTimeZone('Europe/Paris'));
            $action->setCreated($now);
            $action->setStatus(2); //By default, status is "In progress"
            $action->setView(0);
            $action->setUser($user);

            $em->persist($action);
            $em->flush();
            $message = array(
                'message' => 'Votre demande a bien été enregistrée. Vous recevrez une notification lorsque son statut évoluera.',
                'action' => $action,
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

        $message = array(
            'message'  => 'L\'action a bien été supprimée.',
            'id' => $id,
            'user' => $action->getUser()->getId()
        );
        return $message;
    }


    /**
     * @Rest\View(serializerGroups={"action"})
     * @Rest\Put("/action/update/{id}")
     */
    public function putAction(Request $request)
    {
        return $this->editAction($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"action"})
     * @Rest\Patch("action/update/{id}")
     */
    public function patchAction(Request $request)
    {
        return $this->editAction($request, false);
    }

    private function editAction(Request $request,  $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $action = $em->getRepository('ScufBundle:Action')->find($request->get('id'));

        if (empty($action)) {
            return $this->ActionNotFound();
        }

        $form = $this->createForm(ActionType::class, $action);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            // ********************
            // ** TEMPORARY *******
            // ********************
            $now = new \DateTime('now');
            $now->setTimezone(new \DateTimeZone('Europe/Paris'));
            $action->setUpdated($now);
            $action->setView(1);
            $action->setStatus(0); // Accepted status
            // ********************
            $em->persist($action);
            $em->flush();
            $message = array(
                'message'  => 'La notification a bien été mise à jour.',
                'action' => $action
            );
            return $message;
        } else {
            return $form;
        }
    }

    private function ActionNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Aucune action trouvée.');
    }


}
