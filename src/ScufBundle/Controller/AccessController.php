<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Access;
use ScufBundle\Form\AccessType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

class AccessController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"access"})
     * @Rest\Get("/access")
     */
    public function listAccessAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $accessList = $em->getRepository('ScufBundle:Access')->findAll();
        return $accessList;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"access"})
     * @Rest\Post("/access/create")
     */
    public function createAccessAction(Request $request)
    {
        $access = new Access();
        $createAccessForm = $this->createForm(AccessType::class, $access);
        $createAccessForm->submit($request->request->all());

        if ($createAccessForm->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($access);
            $em->flush();
            $msg = array(
                'type' => 'success',
                'msg' => 'Le droit a bien été ajouté.',
                'access' => $access,
            );
        } else {
            $msg = array(
                'type' => 'error',
                'debug' => '[Error] [create|access] See AccessController/createAccessAction',
                'msg' => 'Erreur lors de la création du droit. Veuillez réssayer.'
            );
        }
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/access/delete/{id}")
     */
    public function deleteAccessAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $access = $em->getRepository('ScufBundle:Access')->find($id);

        if($access) {
            $em->remove($access);
            $em->flush();
        }
        $msg = array(
            'type' => 'success',
            'msg'  => 'Le droit a bien été supprimé.',
            'id' => $id,
        );
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\Put("/access/update/{id}")
     */
    public function editAccessAction(Request $request)
    {
        return $this->editAccess($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/access/update/{id}")
     */
    public function patchAccessAction(Request $request)
    {
        return $this->editAccess($request, false);
    }


    public function editAccess(Request $request, $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $access = $em->getRepository('ScufBundle:Access')->find($request->get('id'));

        if (empty($access)) {
            return \FOS\RestBundle\View\View::create(['msg' => 'L\'accès n\'a pas pu être trouvé'], Response::HTTP_NOT_FOUND);
        }

        $editAccessForm = $this->createForm(AccessType::class, $access);
        $editAccessForm->submit($request->request->all(), $clearMissing);

        if ($editAccessForm->isValid()) {
            $em->persist($access);
            $em->flush();
            $msg = array(
                'type'       => 'success',
                'msg'        => 'Le droit '.$access->getTitle().' a bien été édité.',
                'title'      => $access->getTitle(),
                'id'         => $access->getId(),
            );
            return new JsonResponse($msg);
        } else {
            return $editAccessForm;
//            $msg = array(
//                'type' => 'error',
//                'debug'  => '[Error] [edit|access] See AccessController/editAccessAction',
//                'msg'  => "Erreur lors de l'édition du droit. Veuillez réssayer."
//            );
//            return new JsonResponse($msg);
        }
    }
}
