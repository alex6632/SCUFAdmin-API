<?php

namespace ScufBundle\Repository;

/**
 * EventRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByUserAndDay($userID, $now)
    {
        $queryBuilder = $this->_em->createQueryBuilder('e')
            ->select('e.id, e.title, e.location, e.validation, e.start, e.end, (e.user) AS user, e.confirm')
            ->from('ScufBundle:Event', 'e')
            ->where('e.user = :id AND e.start LIKE :start')
            ->setParameter('id', $userID)
            ->setParameter('start', $now . '%')
            ->orderBy('e.start', 'ASC');
        $query = $queryBuilder->getQuery();
        $events = $query->getResult();
        return $events;
    }

    public function findDaysInProgress($userID, $date)
    {
        $queryBuilder = $this->_em->createQueryBuilder('e')
            ->select('DISTINCT CAST(e.start AS DATE) AS day')
            ->from('ScufBundle:Event', 'e')
            ->where('e.user = :id AND e.confirm = 0 AND CAST(e.start AS DATE) <= :date')
            ->setParameter('id', $userID)
            ->setParameter('date', $date)
            ->orderBy('e.start', 'ASC');
        $query = $queryBuilder->getQuery();
        $events = $query->getResult();
        return $events;
    }


    public function reorder($events)
    {
        //$count = 0;

        foreach ($events as $event) {
            $queryBuilder = $this->_em->createQueryBuilder();

            // 1. Update Event validation
            $queryBuilder->update('ScufBundle:Event', 'e')
                ->set('e.validation', ':validation')
                ->where('e.id = :id')
                ->setParameter('validation', $event['validation'])
                ->setParameter('id', $event['id'])
                 ->getQuery()
                ->execute();

            // 2. Update User hours
            $queryBuilder->update('ScufBundle:User', 'u ')
                ->set('e.validation', ':validation')
                ->where('e.id = :id')
                ->setParameter('validation', $event['validation'])
                ->setParameter('id', $event['id'])
                ->getQuery()
                ->execute();
        }

        //return $count;
    }

}
