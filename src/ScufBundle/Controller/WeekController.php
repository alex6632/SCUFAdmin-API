<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Week;
use ScufBundle\Form\WeekType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class WeekController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"week"})
     * @Rest\Get("/weeks/{userID}")
     */
    public function listWeeksByUserAction($userID)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $weeks = $em->getRepository('ScufBundle:Week')->findByUser($userID);
        if (empty($weeks)) {
            return [
                'success' => false,
                'message' => 'Aucune semaine n\'a été crée pour ce salarié !'
            ];
        }
        return [
            'success' => true,
            'list' => $weeks,
        ];
    }

    /**
     * @Rest\View(serializerGroups={"week"}, statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/week/create")
     */
    public function createWeekAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $week = new Week();
        $form = $this->createForm(WeekType::class, $week);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $from = $request->request->get('from');
            $to = $request->request->get('to');
            $nbHours = $week->getHours();
            $userID = $week->getUser();
            $user = $em->getRepository('ScufBundle:User')->find($userID);
            if($to < $from) {
                return new \Exception('Error : the end value can\'t be inferior to the start value !');
            }
            if($to == 0) $to = $from;
            for($i=$from; $i<=$to; $i++) {
                $repeaterWeek = new Week();
                $repeaterWeek->setNumber($i);
                $repeaterWeek->setHours($nbHours);
                $repeaterWeek->setUser($userID);
                $repeaterWeek->setHoursDone(0);
                $user->setHoursTodo($user->getHoursTodo() + $nbHours);
                $em->persist($repeaterWeek);
            }
            $em->flush();
            $message = $from == $to ? 'La semaine '.$from.' a bien été crée' : 'Les semaines ' .$from.' à '.$to.' ont bien été crée';
            $message = array(
                'type' => 'success',
                'message' => $message
            );
            return $message;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\DELETE("/week/delete/{id}")
     */
    public function deleteWeekAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $week = $em->getRepository('ScufBundle:Week')->find($id);
        if (empty($week)) {
            return $this->WeekNotFound();
        }
        $em->remove($week);

        // Update hoursTodo in User entity
        $user = $em->getRepository('ScufBundle:User')->find($week->getUser());
        $user->setHoursTodo($user->getHoursTodo() - $week->getHours());
        $em->flush();

        $message = array(
            'type' => 'success',
            'message'  => 'La semaine a bien été supprimée.',
            'id' => $id
        );
        return $message;
    }

    /**
     * @Rest\View(serializerGroups={"week"})
     * @Rest\Put("/week/update/{id}")
     */
    public function editWeekAction(Request $request)
    {
        return $this->editWeek($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"week"})
     * @Rest\Patch("/week/update/{id}")
     */
    public function patchWeekAction(Request $request)
    {
        return $this->editWeek($request, false);
    }


    private function editWeek(Request $request,  $clearMissing)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $week = $em->getRepository('ScufBundle:Week')->find($request->get('id'));
        $nbHoursBeforeEdit = $week->getHours();

        if (empty($week)) {
            return $this->WeekNotFound();
        }
        $form = $this->createForm(WeekType::class, $week);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {

            // Update hoursTodo in User entity
            $user = $em->getRepository('ScufBundle:User')->find($week->getUser());
            $nbHoursAfterEdit = $request->request->get('hours');
            if ($nbHoursBeforeEdit > $nbHoursAfterEdit) {
                $user->setHoursTodo($user->getHoursTodo() - ($nbHoursBeforeEdit - $nbHoursAfterEdit));
            } else {
                $user->setHoursTodo($user->getHoursTodo() + ($nbHoursAfterEdit - $nbHoursBeforeEdit));
            }

            $em->persist($week);
            $em->flush();
            $message = array(
                'type'       => 'success',
                'message'    => 'La semaine numéro '.$week->getNumber().' a bien été éditée.',
                'week'       => $week
            );
            return $message;
        } else {
            return $form;
        }
    }

    private function WeekNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('La semaine n\'a pas pu être trouvée.');
    }
}