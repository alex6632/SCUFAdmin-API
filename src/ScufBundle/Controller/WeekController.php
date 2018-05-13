<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Week;
use ScufBundle\Form\WeekType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $weeksTypeList = $em->getRepository('ScufBundle:Setting')->findByGroup('week');
        $types = [];
        foreach($weeksTypeList as $type) {
            $types[] = [
                'id' => $type->getId(),
                'title' => $type->getTitle(),
                'value' => $type->getValue(),
                'slug' => $type->getSlug(),
            ];
        }
        return [
            'success' => true,
            'list' => $weeks,
            'types' => $types,
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
            $setting = $week->getSetting();
            $user = $week->getUser();
            if($to < $from) {
                return new \Exception('Error : the end value can\'t be inferior to the start value !');
            }
            //$repeat = ($to - $from) + 1;
            if($to == 0) $to = $from;
            for($i=$from; $i<=$to; $i++) {
                dump($i);
                $repeaterWeek = new Week();
                $repeaterWeek->setNumber($i);
                $repeaterWeek->setSetting($setting);
                $repeaterWeek->setUser($user);
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
     * @Rest\DELETE("/section/delete/{id}")
     */
    public function deleteSectionAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $section = $em->getRepository('ScufBundle:Section')->find($id);
        if (empty($section)) {
            return $this->SectionNotFound();
        }
        $em->remove($section);
        $em->flush();

        $message = array(
            'type' => 'success',
            'message'  => 'La section a bien été supprimée.',
            'id' => $id
        );
        return $message;
    }

    /**
     * @Rest\View()
     * @Rest\Put("/section/update/{id}")
     */
    public function editSectionAction(Request $request)
    {
        return $this->editSection($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/section/update/{id}")
     */
    public function patchSectionAction(Request $request)
    {
        return $this->editSection($request, false);
    }


    private function editSection(Request $request,  $clearMissing)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $section = $em->getRepository('ScufBundle:Section')->find($request->get('id'));
        if (empty($section)) {
            return $this->SectionNotFound();
        }
        $form = $this->createForm(SectionType::class, $section);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em->persist($section);
            $em->flush();
            $message = array(
                'type'       => 'success',
                'message'    => 'La section '.$section->getName().' a bien été éditée.',
                'section'    => $section
            );
            return $message;
        } else {
            return $form;
        }
    }

    private function SectionNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('La section n\'a pas pu être trouvée.');
    }
}