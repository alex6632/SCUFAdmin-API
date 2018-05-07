<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Action;
use ScufBundle\Form\Action\LeaveType;
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
            return $this->ActionNotFound();
        }

        $formattedActions = [];
        foreach ($actions as $action) {
            $updated = !is_null($action['updated']) ? $action['created']->format('d-m-Y') : "/";
            $formattedActions[] = [
                'id' => $action['id'],
                'user' => $action['user'],
                'created' => $action['created']->format('d-m-Y à H:i:s'),
                'updated' => $updated,
                'start' => $action['start']->format('d-m-Y'),
                'end' => $action['end']->format('d-m-Y'),
                'justification' => $action['justification'],
                'status' => $action['status'],
            ];
        }
        return $formattedActions;
    }

    /**
     * @Rest\View(serializerGroups={"action"}, statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/action/create/{type}/{userID}")
     */
    public function createAction(Request $request, $type, $userID)
    {
        $action = new Action();
        $form = $this->createForm(LeaveType::class, $action);
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
