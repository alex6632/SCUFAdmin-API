<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\Setting;
use ScufBundle\Form\SettingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Get("/settings")
     */
    public function listSettingAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $settingList = $em->getRepository('ScufBundle:Setting')->findAll();
        return $settingList;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/setting/{slug}")
     */
    public function oneSettingAction($slug)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $setting = $em->getRepository('ScufBundle:Setting')->findOneBySlug($slug);

        if (empty($setting)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Le réglage n\'a pas pu être trouvé');
        }
        return $setting;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/setting/create")
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
                    'message'    => 'Le champs "titre" est obligatoire, veuillez le renseigner.'
                );
            }
            if(empty($form['value'])) {
                $msg = array(
                    'type'   => 'error',
                    'debug'  => '[Error field is missing] [create|setting|value] See SettingController/createSettingAction',
                    'message'    => 'Le champs "valeur" est obligatoire, veuillez le renseigner.'
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
                    'message'        => 'Le réglage "'.$form['title'].'" a bien été ajouté.',
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
            'message'  => 'Erreur lors de la création du réglage. Veuillez réssayer.'
        );
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\DELETE("/setting/delete/{id}")
     */
    public function deleteSettingAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $setting = $em->getRepository('ScufBundle:Setting')->find($id);
        $em->remove($setting);
        $em->flush();

        $msg = array(
            'type' => 'success',
            'message'  => 'Le réglage a bien été supprimé.',
            'id' => $id
        );
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\Put("/setting/update/{id}")
     */
    public function editSettingAction(Request $request)
    {
        return $this->editSetting($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/setting/update/{id}")
     */
    public function patchSettingAction(Request $request)
    {
        return $this->editSetting($request, false);
    }


    private function editSetting(Request $request,  $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $setting = $em->getRepository('ScufBundle:Setting')->find($request->get('id'));

        if (empty($setting)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Le réglage n\'a pas pu être trouvé');
        }

        $editSettingForm = $this->createForm(SettingType::class, $setting);
        $editSettingForm->submit($request->request->all(), $clearMissing);

        if ($editSettingForm->isValid()) {
            $em->persist($setting);
            $em->flush();
            $msg = array(
                'type'       => 'success',
                'message'        => 'Le réglage '.$setting->getTitle().' a bien été édité.',
                'title'      => $setting->getTitle(),
                'value'      => $setting->getValue(),
                'id'         => $setting->getId()
            );
            return new JsonResponse($msg);
        } else {
            return $editSettingForm;
        }
    }
}
