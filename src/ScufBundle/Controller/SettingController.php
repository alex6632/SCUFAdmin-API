<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Setting;
use ScufBundle\Form\SettingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/setting")
 */
class SettingController extends Controller
{

    /**
     * @Route("/create", name="createSetting")
     */
    public function createSettingAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $setting = new Setting();
        $createSettingForm = $this->createForm(SettingType::class, $setting);
        $createSettingForm->handleRequest($request);

        if($request->isXmlHttpRequest()) {
            $form = $request->request->all();
            //$form = $request->get('title');
            $msg['type'] = 'success';

            //var_dump($form['is_int']);
            //die();

            if(empty($form['title'])) {
                $msg = array(
                    'type'   => 'error',
                    'debug'  => '[Error field is missing] [create|setting|title] See SettingController/createSettingAction',
                    'msg'    => 'Le champs "titre" est obligatoire, veuillez le renseigner.'
                );
            }
            if(empty($form['value'])) {
                $msg = array(
                    'type'   => 'error',
                    'debug'  => '[Error field is missing] [create|setting|value] See SettingController/createSettingAction',
                    'msg'    => 'Le champs "valeur" est obligatoire, veuillez le renseigner.'
                );
            }
            if(!empty($form['value']) && $form['is_int'] == 1) {
                $form['value'] = intval($form['value']);
            }

            if($msg['type'] == 'success') {
                $em->persist($setting);
                $em->flush();
                $msg = array(
                    'type'       => 'success',
                    'msg'        => 'Le réglage "'.$form['title'].'" a bien été ajouté.',
                    'title'      => $form['title'],
                    'value'      => $form['value'],
                    'is_int'     => $form['is_int'],
                    'id'         => $setting->getId(),
                );
            }
            return new JsonResponse($msg);
        }

        $msg = array(
            'type' => 'error',
            'debug'  => '[Error] [create|setting] See SettingController/createSettingAction',
            'msg'  => 'Erreur lors de la création du réglage. Veuillez réssayer.'
        );
        return new JsonResponse($msg);
    }

    /**
     * @Route("/delete/{id}", name="deleteSetting")
     */
    public function deleteSettingAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $setting = $em->getRepository('ScufBundle:Setting')->find($id);
        $em->remove($setting);
        $em->flush();

        $msg = array(
            'type' => 'success',
            'msg'  => 'Le réglage a bien été supprimé.'
        );
        return new JsonResponse($msg);
    }

    /**
     * @Route("/edit/{id}", name="editSetting")
     */
    public function editSettingAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $setting = $em->getRepository('ScufBundle:Setting')->find($id);

        $editSettingForm = $this->createForm(SettingType::class, $setting);
        $editSettingForm->handleRequest($request);

        if($request->isXmlHttpRequest()) {
            $form = $request->request->all();
            $msg['type'] = 'success';

            if(empty($form['title'])) {
                $msg = array(
                    'type'   => 'error',
                    'debug'  => '[Error field is missing] [edit|setting|title] See SettingController/editSettingAction',
                    'msg'    => 'Le champs "titre" est obligatoire, veuillez le renseigner.'
                );
            }
            if(empty($form['value'])) {
                $msg = array(
                    'type'   => 'error',
                    'debug'  => '[Error field is missing] [edit|setting|value] See SettingController/editSettingAction',
                    'msg'    => 'Le champs "valeur" est obligatoire, veuillez le renseigner.'
                );
            }
            if(!empty($form['value']) && $form['is_int'] == 1) {
                $form['value'] = intval($form['value']);
            } else {
                $form['value'] = strval($form['value']);
            }

            if($msg['type'] == 'success') {
                $em->persist($setting);
                $em->flush();
                $msg = array(
                    'type'       => 'success',
                    'msg'        => 'Le réglage "'.$form['title'].'" a bien été édité.',
                    'title'      => $form['title'],
                    'value'      => $form['value'],
                    'is_int'     => $form['is_int'],
                    'id'         => $setting->getId(),
                );
            }
            return new JsonResponse($msg);
        }

        $msg = array(
            'type' => 'error',
            'debug'  => '[Error] [edit|setting] See SettingController/editSettingAction',
            'msg'  => "Erreur lors de l'édition du réglage. Veuillez réssayer."
        );
        return new JsonResponse($msg);
    }
}
