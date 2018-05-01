<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\AuthToken;
use ScufBundle\Entity\Credentials;
use ScufBundle\Form\CredentialsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenController extends Controller
{

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"auth-token"})
     * @Rest\Post("/auth-tokens")
     */
    public function postAuthTokensAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit($request->request->all());

        if(!$form->isValid()) {
            // return msg...
            return $form;
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('ScufBundle:User')->findOneByUsername($credentials->getLogin());

        if(!$user) {
            return $this->invalidCredentials();
        }

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());

        if(!$isPasswordValid) {
            return $this->invalidCredentials();
        }

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreated(new \DateTime('now'));
        $authToken->setUser($user);

        $em->persist($authToken);
        $em->flush();

        // return msg...
        return $authToken;
    }

    private function invalidCredentials()
    {
        throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('L\'identifiant et/ou le mot de passe indiqué(s) semble(nt) incorrect(s)');
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/auth-tokens/{id}")
     */
    public function removeAuthTokenAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $authToken = $em->getRepository('ScufBundle:AuthToken')->find($request->get('id'));

        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();

        if($authToken && $authToken->getUser()->getId() === $connectedUser->getId()) {
            $em->remove($authToken);
            $em->flush();
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('L\'identifiant indiqué semble incorrect');
        }
    }
}