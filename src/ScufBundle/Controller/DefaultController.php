<?php

namespace ScufBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * @Route("/")
     * @Template
     */
    public function indexAction()
    {}


    /**
     * @Route("/", name="login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        //return $this->render('ScufBundle:Default:index.html.twig');
        //return $this->redirectToRoute('');
        $em = $this->getDoctrine()->getEntityManager();
        //$ville = new Ville();

        if($request->isXmlHttpRequest()) {

            // Creer un service qui check les champs
            //....

            //return new Response(json_encode(array('status'=>'success')));
            return new JsonResponse($msg);
        }

        if($request->isXmlHttpRequest()) {
            // Creer un service qui check les champs
            // ...

            if(empty($form['nom'])) {
                $msg = array(
                    'type' => 'error',
                    'msg'  => ''
                );
            } else {
                $em->persist($ville);
                $em->flush();
                $msg = array(
                    'type'       => 'success',
                    'msg'        => $this->get('translator')->trans('ville.createdSuccess'),
                    'nom'        => $form['nom'],
                    'id'         => $ville->getId(),
                    'deleteLink' => $this->get('translator')->trans('table.action.delete'),
                    'editLink'   => $this->get('translator')->trans('table.action.edit'),
                    'closeText'  => $this->get('translator')->trans('modal.close'),
                    'titleModal' => $this->get('translator')->trans('modal.confirmMessage'),
                    'yes'        => $this->get('translator')->trans('modal.yes'),
                    'no'         => $this->get('translator')->trans('modal.no')
                );
            }
            //return new JsonResponse($msg);
        }
    }
}
