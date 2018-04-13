<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Access;
use ScufBundle\Form\AccessType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

class AccessController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Get("/access")
     */
    public function listAccessAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $accessList = $em->getRepository('ScufBundle:Access')->findAll();
        return $accessList;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/access/create")
     */
    public function createAccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $access = new Access();
        $createAccessForm = $this->createForm(AccessType::class, $access, array(
            'method' => 'post'
        ));

        $msg = array();

        if ($request->isMethod('POST')) {
            $createAccessForm->submit($request->request->all());
            if ($createAccessForm->isValid()) {
                $form = $request->request->all();

                if (empty($form['title'])) {
                    $msg = array(
                        'type' => 'error',
                        'debug' => '[Error field is missing] [create|access|title] See AccessController/createAccessAction',
                        'msg' => 'Le champs "titre" est obligatoire, veuillez le renseigner.'
                    );
                } else {
                    $em->persist($access);
                    $em->flush();
                    $msg = array(
                        'type' => 'success',
                        'msg' => 'Le droit ' . $form['title'] . ' a bien été ajouté.',
                        'title' => $form['title'],
                        'id' => $access->getId(),
                    );
                }
            } else {
                $msg = array(
                    'type' => 'error',
                    'debug' => '[Error] [create|access] See AccessController/createAccessAction',
                    'msg' => 'Erreur lors de la création du droit. Veuillez réssayer.'
                );
            }
        }
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/access/delete/{id}")
     */
    public function deleteAccessAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $access = $em->getRepository('ScufBundle:Access')->find($id);
        $em->remove($access);
        $em->flush();

        $msg = array(
            'type' => 'success',
            'msg'  => 'Le droit a bien été supprimé.',
            'id' => $id,
        );
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\Post("/access/update/{id}")
     */
    public function editAccessAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $access = $em->getRepository('ScufBundle:Access')->find($id);

        $editAccessForm = $this->createForm(AccessType::class, $access);
        $editAccessForm->handleRequest($request);

        if($request->isXmlHttpRequest()) {
            $form = $request->request->all();

            if(empty($form['title'])) {
                $msg = array(
                    'type'   => 'error',
                    'debug'  => '[Error field is missing] [edit|access|title] See AccessController/editAccessAction',
                    'msg'    => 'Le champs "titre" est obligatoire, veuillez le renseigner.'
                );
            } else {
                $em->persist($access);
                $em->flush();
                $msg = array(
                    'type'       => 'success',
                    'msg'        => 'Le droit '.$form['title'].' a bien été édité.',
                    'title'      => $form['title'],
                    'id'         => $access->getId(),
                );
            }
            return new JsonResponse($msg);
        }

        $msg = array(
            'type' => 'error',
            'debug'  => '[Error] [edit|access] See AccessController/editAccessAction',
            'msg'  => "Erreur lors de l'édition du droit. Veuillez réssayer."
        );
        return new JsonResponse($msg);
    }
}
