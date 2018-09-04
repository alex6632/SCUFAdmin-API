<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Event;
use ScufBundle\Entity\EventParent;
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
     * @Rest\View(serializerGroups={"event"})
     * @Rest\Get("/events/in-progress/{userID}")
     */
    public function listDaysInProgressAction($userID)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $nowFormatted = $now->format('Y-m-d');
        $events = $em->getRepository('ScufBundle:Event')->findDaysInProgress($userID, $nowFormatted);
        $eventsFormatted = [];

        foreach($events as $event) {
            $day = substr($event['day'], 8, 2);
            $month = substr($event['day'], 5, 2);
            $year = substr($event['day'], 0, 4);
            $date = $day.'/'.$month.'/'.$year;
            $eventsFormatted[] = [
                'date' => $date,
                'dateEN' => $event['day'],
            ];
        }
        return $eventsFormatted;
    }

    /**
     * @Rest\View(serializerGroups={"event"})
     * @Rest\Get("/user/{userID}/event/{date}", defaults={"date"="now"})
     */
    public function listEventsByDayAction($userID, $date)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $now = ($date == 'now') ? new \DateTime('now', new \DateTimeZone('Europe/Paris')) : new \DateTime($date);
        $nowFormatted = $now->format('Y-m-d');
        $events = $em->getRepository('ScufBundle:Event')->findByUserAndDay($userID, $nowFormatted);
        $today = $now->format('d/m/Y');
        $todayEN = $now->format('Y-m-d');
        $week = $now->format('W');
        $eventsFormatted = [];
        foreach($events as $event) {
            $startHours = $event['start']->format('H:i');
            $endHours = $event['end']->format('H:i');
            $partialStartHours = is_null($event['partial_start']) ? null : $event['partial_start']->format('H:i');
            $partialEndHours = is_null($event['partial_end']) ? null : $event['partial_end']->format('H:i');
            $eventsFormatted[] = [
                'id' => $event['id'],
                'userID' => $event['user'],
                'type' => $event['type'],
                'title' => $event['title'],
                'validation' => $event['validation'],
                'location' => $event['location'],
                'startHours' => $startHours,
                'endHours' => $endHours,
                'partialStart' => $partialStartHours,
                'partialEnd' => $partialEndHours,
                'justification' => $event['justification'],
                'confirm' => $event['confirm'],
            ];
        }
        return [
            'date' => $today,
            'dateEN' => $todayEN,
            'week' => $week,
            'list' => $eventsFormatted,
        ];
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

            // 0. Get all info necessary to save event
            $title = $event->getTitle();
            $all_day = $event->getAllDay();
            $start = $event->getStart();
            $end = $event->getEnd();
            $location = $event->getLocation();
            $bg = $event->getBackgroundColor();
            $border = $event->getBorderColor();
            $partial_start = $event->getPartialStart();
            $partial_end = $event->getPartialEnd();
            $justification = $event->getJustification();
            $type = $request->request->get('type');


            // 1. Create new instance of EventParent
            //$field = $request->request->get('field');
            $start_date = $event->getStart()->format('Y-m-d');
            $weekday = date('N', strtotime($start_date));
            $new_start_date = \DateTime::createFromFormat("Y-m-d", $start_date);
            $start_time = \DateTime::createFromFormat("H:i:s", $event->getStart()->format('H:i:s'));
            $end_time = \DateTime::createFromFormat("H:i:s", $event->getEnd()->format('H:i:s'));

            // Define variable if event is not repeated
            $repeats = 0;
            $repeat_freq = 0;
            $until = 1;

            if ($request->request->get('repeats') == 1) {
                $repeats = $request->request->get('repeats');
                $repeat_freq = $request->request->get('repeat_freq');
                $until = $request->request->get('until') !== 0 ? ($request->request->get('until')*7)/$repeat_freq : 365/$repeat_freq;
            }

            $eventParent = new EventParent();
            $eventParent->setWeekday($weekday);
            $eventParent->setStartDate($new_start_date);
            $eventParent->setStartTime($start_time);
            $eventParent->setEndTime($end_time);
            $eventParent->setRepeats($repeats);
            $eventParent->setRepeatFreq($repeat_freq);
            $em->persist($eventParent);

            $nbHours = $this->GetHoursDone($request->request->get('start'), $request->request->get('end'));

            // 2. Create new instance of Event based on EventParent
            for ($i=0; $i<$until; $i++) {
                $get_date = $start->format('Y-m-d');
                $get_weekday = date('N', strtotime($get_date));

                // We don't create event saturday or sunday
                if ($get_weekday !== '6' && $get_weekday !== '7') {
                    $event_repeater = new Event();
                    /*
                     * basic_me = event created by user himself
                     * basic_ext = event created by superior of user
                     */
                    if($type == "basic_me") {
                        $actualHoursPlanifiedByMe = $user->getHoursPlanifiedByMe();
                        $newHoursPlanifiedByMe = $actualHoursPlanifiedByMe + $nbHours;
                        $user->setHoursPlanifiedByMe($newHoursPlanifiedByMe);
                    } else {
                        $actualHoursPlanified = $user->getHoursPlanified();
                        $newHoursPlanified = $actualHoursPlanified + $nbHours;
                        $user->setHoursPlanified($newHoursPlanified);
                    }
                    $event_repeater->setTitle($title);
                    $event_repeater->setAllDay($all_day);
                    $event_repeater->setStart($start);
                    $event_repeater->setEnd($end);
                    $event_repeater->setLocation($location);
                    $event_repeater->setBackgroundColor($bg);
                    $event_repeater->setBorderColor($border);
                    $event_repeater->setPartialStart($partial_start);
                    $event_repeater->setPartialEnd($partial_end);
                    $event_repeater->setJustification($justification);
                    $event_repeater->setType($type);
                    $event_repeater->setUser($user);
                    $event_repeater->setValidation(0);
                    $event_repeater->setConfirm(0);
                    $event_repeater->setEventParent($eventParent);
                    $em->persist($event_repeater);
                }
                // Update start and end date to repeat
                $start_tmp = date("Y-m-d H:i:s", strtotime($start->format('Y-m-d H:i:s') . '+' . $repeat_freq . 'DAYS'));
                $end_tmp = date("Y-m-d H:i:s", strtotime($end->format('Y-m-d H:i:s') . '+' . $repeat_freq . 'DAYS'));
                $start = \DateTime::createFromFormat("Y-m-d H:i:s", $start_tmp);
                $end = \DateTime::createFromFormat("Y-m-d H:i:s", $end_tmp);
            }
            $em->flush();
            $message_text = $until === 1 ? 'L\'événement a bien été enregistré' : 'Les événements ont bien été enregistrés';
            $message = array(
                'type' => 'success',
                'message' => $message_text
            );
            return $message;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"event"}, statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/event/createFromNotification/{userID}/{actionID}")
     */
    public function createEventAndUpdateAction(Request $request, $userID, $actionID)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');

            // 1. Update Action
            $action = $em->getRepository('ScufBundle:Action')->findOneById($actionID);
            $now = new \DateTime('now');
            $now->setTimezone(new \DateTimeZone('Europe/Paris'));
            $action->setUpdated($now);
            $action->setView(1);
            $action->setStatus(1); // Accepted status

            // 2. Update User overtime if rest is accepted
            $user = $em->getRepository('ScufBundle:User')->find($userID);
            $type = $action->getType();
            if($type == 'rest') {
                $rest = $this->GetHoursDone($event->getStart()->format('Y-m-d H:i:s'), $event->getEnd()->format('Y-m-d H:i:s'));
                $coefficient = $em->getRepository('ScufBundle:Setting')->findOneBySlug('coeff')->getValue();
                $actualRest = number_format($user->getOvertime() * $coefficient, 2);
                $newOvertime = number_format(($actualRest - $rest) / $coefficient, 2);
                $user->setOvertime($newOvertime);
            }

            // 3. Create Event
            switch($type) {
                case 'hours':
                    $event->setType('hours');
                    break;
                case 'rest':
                    $event->setType('rest');
                    break;
                case 'leave':
                    $event->setType('leave');
                    break;
            }
            $event->setUser($user);
            $event->setValidation(0);
            $event->setConfirm(0);
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

        $type = $event->getType();
        if($type == "basic_ext") {
            $user = $em->getRepository('ScufBundle:Event')->findOneById($id)->getUser();
            $nbHours = $this->GetHoursDone($event->getStart()->format('Y-m-d H:i:s'), $event->getEnd()->format('Y-m-d H:i:s'));
            $actualHoursPlanified = $user->getHoursPlanified();
            $newHoursPlanified = $actualHoursPlanified - $nbHours;
            $user->setHoursPlanified($newHoursPlanified);
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
        $nbHoursBeforeEdit = $this->GetHoursDone($event->getStart()->format('Y-m-d H:i:s'), $event->getEnd()->format('Y-m-d H:i:s'));

        if (empty($event)) {
            return $this->EventNotFound();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $type = $request->request->get('type');
            $user = $em->getRepository('ScufBundle:Event')->findOneById($request->get('id'))->getUser();
            $nbHoursAfterEdit = $this->GetHoursDone($request->request->get('start'), $request->request->get('end'));

            if($type == "basic_ext") {
                $actualHoursPlanified = $user->getHoursPlanified();
                $newHoursPlanified = 0;

                if($nbHoursBeforeEdit > $nbHoursAfterEdit) {
                    $delta = $nbHoursBeforeEdit - $nbHoursAfterEdit;
                    $newHoursPlanified = $actualHoursPlanified - $delta;
                } else if($nbHoursBeforeEdit < $nbHoursAfterEdit) {
                    $delta = $nbHoursAfterEdit - $nbHoursBeforeEdit;
                    $newHoursPlanified = $actualHoursPlanified + $delta;
                }
                $user->setHoursPlanified($newHoursPlanified);
            }

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

    /**
     * @Rest\View(serializerGroups={"event"})
     * @Rest\Patch("event/{id}/confirm")
     */
    public function patchMultipleEventsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $event = $em->getRepository('ScufBundle:Event')->find($request->get('id'));

        if (empty($event)) {
            return $this->EventNotFound();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $validation = $request->get('validation');
            if($validation == 1) {
                $hoursDone = $this->GetHoursDone($event->getStart()->format('Y-m-d H:i:s'), $event->getEnd()->format('Y-m-d H:i:s'));
            } else if($validation == 2) {
                $hoursDone = $this->GetHoursDone($request->get('partial_start'), $request->get('partial_end'));
            } else {
                $hoursDone = 0;
            }
            // Update total hoursDone field
            $userID = $request->request->get('user');
            $user = $em->getRepository('ScufBundle:User')->findOneById($userID);
            $actualTotalHoursDone = $user->getHoursDone();
            $user->setHoursDone($actualTotalHoursDone + $hoursDone);

            // Update week hoursDone field
            $date = $event->getStart();
            $weekValue = $date->format('W');
            $weekArray = $em->getRepository('ScufBundle:User')->findTypeByUserAndWeek($userID, $weekValue);
            $week = $em->getRepository('ScufBundle:Week')->findOneById($weekArray[0]['id']);
            $actualWeekHoursDone = $week->getHoursDone();
            $week->setHoursDone($actualWeekHoursDone + $hoursDone);

            // Update User overtime if is hours type
            $type = $request->request->get('type');
            if($type == 'hours') {
                //$rest = $this->GetHoursDone($event->getStart()->format('Y-m-d H:i:s'),$event->getEnd()->format('Y-m-d H:i:s'));
                $actualOvertime = $user->getOvertime();
                $newOvertime = $actualOvertime + $hoursDone;
                $user->setOvertime($newOvertime);
            }

            $em->persist($event);
            $em->flush();
            $message = array(
                'type' => 'success',
                'message' => 'L\'événement a bien été mis à jour',
                'id' => $event->getId(),
                'validation' => $event->getValidation(),
            );
            return $message;
        } else {
            return $form;
        }
    }

    private function GetHoursDone($startDate, $endDate)
    {
        $startHours = substr($startDate, 11, 2);
        $startMinutes = substr($startDate, 14, 2);
        $endHours = substr($endDate, 11, 2);
        $endMinutes = substr($endDate, 14, 2);

        // Convert hours into minutes
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

        // Find delta between start & end date
        if($start < $end) {
            $hoursDone = ($end - $start) / 60;
        } else {
            throw new \Exception('La date de fin doit être ultérieure à la date de début !');
        }
        return $hoursDone;
    }

    private function EventNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Aucun événement trouvé.');
    }


}
