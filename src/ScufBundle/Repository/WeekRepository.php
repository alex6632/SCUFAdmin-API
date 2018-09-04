<?php

namespace ScufBundle\Repository;

/**
 * WeekRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WeekRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByUser($userID)
    {
        $queryBuilder = $this->_em->createQueryBuilder('w')
            ->select('w.id, w.number, (w.user) AS user, w.hours')
            ->from('ScufBundle:Week', 'w')
            ->where('w.user = :id')
            ->setParameter('id', $userID)
            ->orderBy('w.number', 'ASC');
        $query = $queryBuilder->getQuery();
        $weeks = $query->getResult();
        return $weeks;
    }
}
