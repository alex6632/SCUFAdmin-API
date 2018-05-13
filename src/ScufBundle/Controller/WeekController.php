<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Section;
use ScufBundle\Form\SectionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

class SectionController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Get("/sections")
     */
    public function listSectionsAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $sections = $em->getRepository('ScufBundle:Section')->findAll();

        if (empty($sections)) {
            return [
                'success' => false,
                'message' => 'Aucune section n\'a été crée pour le moment !'
            ];
        }
        return [
            'success' => true,
            'list' => $sections
        ];
    }

    /**
     * @Rest\View()
     * @Rest\Get("/section/{id}")
     */
    public function oneSectionAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $section = $em->getRepository('ScufBundle:Setting')->findOneById($id);
        if (empty($section)) {
            return $this->SectionNotFound();
        }
        return $section;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/section/create")
     */
    public function createSectionAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em->persist($section);
            $em->flush();
            $message = array(
                'message' => 'La section a bien été crée',
                'section' => $section,
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