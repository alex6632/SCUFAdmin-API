<?php

namespace ScufBundle\Controller;

use ScufBundle\Entity\User;
use ScufBundle\Form\UserType;
use ScufBundle\Service\ElasticSearchMotor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function listUsersAction()
    {
        //$em = $this->getDoctrine()->getEntityManager();
        $em = $this->get('doctrine.orm.entity_manager');
        $userList = $em->getRepository('ScufBundle:User')->findAll();
        return $userList;
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{id}")
     */
    public function listEmployeesFromUserAction($id)
    {
        return $this->recursiveSearchEmployees($id);
    }

    private function recursiveSearchEmployees($id)
    {
        $employeesList = [];
        $em = $this->get('doctrine.orm.entity_manager');
        $employees = $em->getRepository('ScufBundle:User')->findBySuperior($id);
        foreach($employees as $employee) {
            $idSubLevel = $employee->getId();
            $employeesList[] = [
                'id' => $idSubLevel,
                'firstname' => $employee->getFirstname(),
                'lastname' => $employee->getLastname(),
            ];
            $employeesSubLevel = $em->getRepository('ScufBundle:User')->findBySuperior($idSubLevel);
            if(!empty($employeesSubLevel)) $employeesList = array_merge( $employeesList, $this->recursiveSearchEmployees($idSubLevel));
        }
        return $employeesList;
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/user/{id}")
     */
    public function oneUserAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ScufBundle:User')->find($id);

        $superior = $user->getSuperior();
        $superiorName = $superior != null ? $superior->getFirstname()." ".$superior->getLastname() : "Aucun supérieur n'est rattaché.";

        $role = $user->getRole();
        if ($role === 42) {
            return [
                'role' => 'root',
                'user' => $user,
                'superiorName' => $superiorName,
                'weekID' => null,
                'hours_done' => 0,
                'hoursTodoThisWeek' => 0,
            ];
        }

        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $weekValue = $now->format('W');
        $week = $em->getRepository('ScufBundle:User')->findTypeByUserAndWeek($id, $weekValue);
        $hoursTodoThisWeek = empty($week) ? 0 : $week[0]['hours'];
        return [
            'role' => 'other',
            'user' => $user,
            'superiorName' => $superiorName,
            'weekID' => empty($week) ? null : $week[0]['id'],
            'hours_done' => empty($week) ? 0 : $week[0]['hours_done'],
            'hoursTodoThisWeek' => intval($hoursTodoThisWeek),
        ];
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * @Rest\Post("/user/create")
     */
    public function createUserAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['validation_groups'=>['Default', 'New']]);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            if($user->getPlainPassword() == $user->getConfirmPassword()) {
                $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encoded);
            } else {
                throw new \Exception('Les mots de passe doivent être identiques !');
            }

            $em = $this->get('doctrine.orm.entity_manager');
            $user->setHoursPlanified(0);
            $user->setHoursTodo(0);
            $user->setHoursDone(0);
            $user->setHoursPlanifiedByMe(0);
            $user->setOvertime(0);
            $em->persist($user);
            $em->flush();
            $msg = array(
                'type' => 'success',
                'message' => 'Le user a bien été ajouté.',
                'user' => $user,
            );
            return $msg;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\DELETE("/user/delete/{id}")
     */
    public function deleteUserAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $setting = $em->getRepository('ScufBundle:User')->find($id);
        $em->remove($setting);
        $em->flush();

        $msg = array(
            'type' => 'success',
            'message'  => 'L\'utilisateur a bien été supprimé.',
            'id' => $id
        );
        return new JsonResponse($msg);
    }

    /**
     * @Rest\View()
     * @Rest\Put("/user/update/{id}")
     */
    public function editUserAction(Request $request)
    {
        return $this->editUser($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/user/update/{id}")
     */
    public function patchUserAction(Request $request)
    {
        return $this->editUser($request, false);
    }


    private function editUser(Request $request,  $clearMissing)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ScufBundle:User')->find($request->get('id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        if($clearMissing) {
            $options = ['validation_groups'=>['Default', 'FullUpdate']];
        } else {
            $options = [];
        }

        $form = $this->createForm(UserType::class, $user, $options);
        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            if(!empty($user->getPreviousPassword())) {
                $encoder = $this->get('security.password_encoder');
                // Check previous password
                $previousPassword = $user->getPreviousPassword();
                $isPasswordValid = $encoder->isPasswordValid($user, $previousPassword);

                if($isPasswordValid) {
                    if($user->getPlainPassword() == $user->getConfirmPassword()) {
                        $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                        $user->setPassword($encoded);
                    } else {
                        throw new \Exception('Les mots de passe doivent être identiques !');
                    }
                } else {
                    throw new \Exception('Votre mot de passe actuel est incorrect !');
                }
            }
            $em->merge($user);
            $em->flush();
            $msg = array(
                'type'           => 'success',
                'message'            => 'L\'utilisateur "'.$user->getFirstname().' ' .$user->getLastname() .'" a bien été édité.',
                'firstname'      => $user->getFirstname(),
                'lastname'       => $user->getLastname(),
                'username'       => $user->getUsername(),
                'role'           => $user->getRole(),
                'superior'       => $user->getSuperior(),
                'access'         => $user->getAccess(),
                'hoursPlanified' => $user->getHoursPlanified(),
                'id'             => $user->getId()
            );
            return new JsonResponse($msg);
        } else {
            return $form;
        }
    }

    private function userNotFound()
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('L\'utilisateur n\'a pas pu être trouvé');
    }

    /**
     * @Rest\View()
     * @Rest\Get("/search")
     */
    public function searchAction(Request $request)
    {
        $searchMotor = $this->get('app.elastic_search_motor');
        $search = $request->query->get('search', '');

        if (strlen($search) >= ElasticSearchMotor::MIN_CHAR_USER) {
            $searchResults = $searchMotor->searchUsers($search);
            $result = count($searchResults) > 1 ? "utilisateurs trouvés" : "utilisateur trouvé";
            $users[] = [
                'result' => $result,
                'id' => null,
                'total' => count($searchResults),
            ];
            foreach ($searchResults as $user) {
                $users[] = [
                    'result' => $user->getFirstname().' '.$user->getLastname(),
                    'id' => $user->getId(),
                ];
            }
        } else {
            $users = [];
        }
        return new JsonResponse($users);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/user/{userID}/week/{$number}")
     */
    public function getWeekHoursNumber($userID, $number)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $settingID = $em->getRepository('ScufBundle:Week')->findTypeByUserAndWeek($userID, $number);
        $nbHours = $em->getRepository('ScufBundle:Setting')->findOneById($settingID)->getValue();
        return $nbHours;
    }
}
