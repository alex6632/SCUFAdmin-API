<?php

namespace ScufBundle\Service;

use Elastica\Query\Match;
use FOS\ElasticaBundle\Finder\FinderInterface;

class ElasticSearchMotor
{
    const MIN_CHAR_USER = 3;
    const LIMIT_USER = 10;

    private $finderUser;

    public function __construct(FinderInterface $finderUser)
    {
        $this->finderUser = $finderUser;
    }

    /**
     * Exécute la recherche sur Elasticsearch pour le moteur de recherche des utilisateurs.
     */
    public function searchUsers($search)
    {
        $query = new Match();
        $query->setFieldQuery('firstname', $search);
        $query->setFieldOperator('firstname', 'AND');

        return $this->finderUser->find($query, self::LIMIT_USER);
    }
}