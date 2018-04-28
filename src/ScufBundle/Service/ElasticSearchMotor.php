<?php

namespace ScufBundle\Service;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
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

    // Exécute la recherche sur Elasticsearch pour le moteur de recherche des utilisateurs.
    public function searchUsers($search)
    {
//        $query = new Match();
//        $query->setFieldQuery('firstname', $search);
//        $query->setFieldOperator('firstname', 'AND');

        $queryBool = new BoolQuery();
        $multiMatch = new MultiMatch();

        $multiMatch->setQuery($search);
        $multiMatch->setFields(['firstname', 'username', 'lastname']);
        $multiMatch->setType('cross_fields');
        $multiMatch->setOperator('and');

        $queryBool->addMust($multiMatch);

        $query = new Query();
        $query->setQuery($queryBool);
        $query->setSize(self::LIMIT_USER);
        //$query->setFrom($offset);
        //$query->setSize($size);

        return $this->finderUser->find($query, self::LIMIT_USER);
    }
}