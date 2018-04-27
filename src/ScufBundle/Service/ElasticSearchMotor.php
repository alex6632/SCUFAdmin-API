<?php

namespace AppBundle\Service;

use Elastica\Query\Match;
use FOS\ElasticaBundle\Finder\FinderInterface;

/**
 * Service pour exécuter des requêtes sur Elastic Search.
 */
class MoteurRecherche
{
    const MIN_CHAR_MDR_CATEGORIE = 3;
    const LIMIT_MDR_CATEGORIE = 10;

    private $finderCategorie;

    public function __construct(FinderInterface $finderCategorie)
    {
        $this->finderCategorie = $finderCategorie;
    }

    /**
     * Exécute la recherche sur Elasticsearch pour le moteur de recherche des catégories.
     *
     * @param string $recherche Valeur recherchée
     *
     * @return Categorie[]
     */
    public function rechercheCategories($recherche)
    {
        $query = new Match();
        $query->setFieldQuery('libelle', $recherche);
        $query->setFieldOperator('libelle', 'AND');

        return $this->finderCategorie->find($query, self::LIMIT_MDR_CATEGORIE);
    }
}