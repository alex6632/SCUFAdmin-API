<?php

namespace ScufBundle\Repository;

/**
 * ActionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActionRepository extends \Doctrine\ORM\EntityRepository
{
    public function findActionByTypeAndUser($type, $userID)
    {
        $queryBuilder = $this->_em->createQueryBuilder('a')
            ->select('a.id, a.created, a.updated, a.start, a.end, a.status, a.justification, (a.user) AS user, (a.recipient) AS recipient')
            ->from('ScufBundle:Action', 'a')
            ->where('a.type = :type AND a.user = :id')
            ->setParameter('type', $type)
            ->setParameter('id', $userID);
        $query = $queryBuilder->getQuery();
        $actions = $query->getResult();
        return $actions;
    }

    public function findNotificationByUser($userID)
    {
        $queryBuilder = $this->_em->createQueryBuilder('a')
            ->select('a.id, a.created, a.updated, a.start, a.end, a.status, a.justification, (a.user) AS user, (a.recipient) AS recipient, a.type, a.view, a.location')
            ->from('ScufBundle:Action', 'a')
            ->where('a.recipient = :id')
            ->setParameter('id', $userID)
            ->orderBy('a.created', 'ASC');
        $query = $queryBuilder->getQuery();
        $notifications = $query->getResult();
        return $notifications;
    }

    public function findNotificationInProgress($userID)
    {
        $queryBuilder = $this->_em->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->from('ScufBundle:Action', 'a')
            ->where('a.recipient = :id AND a.view = 0')
            ->setParameter('id', $userID);
        $query = $queryBuilder->getQuery();
        $count = $query->getResult();
        return $count;
    }

    public function countNotificationByUser($userID)
    {
        $queryBuilder = $this->_em->createQueryBuilder('a')
            ->select('COUNT(a.id) AS count')
            ->from('ScufBundle:Action', 'a')
            ->where('a.recipient = :id AND a.view != 1')
            ->setParameter('id', $userID);
        $query = $queryBuilder->getQuery();
        $countNotifications = $query->getResult();
        return $countNotifications;
    }
}
