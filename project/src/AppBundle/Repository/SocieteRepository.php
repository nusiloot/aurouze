<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use AppBundle\Document\Societe;
use AppBundle\Tool\RechercheTool;

/**
 * SocieteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SocieteRepository extends DocumentRepository {

    public function findByTerms($queryString, $withNonActif = false) {
        $terms = explode(" ", trim(preg_replace("/[ ]+/", " ", $queryString)));
        $results = null;
        foreach ($terms as $term) {
            if (strlen($term) < 2) {
                continue;
            }
            $q = $this->createQueryBuilder();
            $q
                    ->addOr($q->expr()->field('identifiant')->equals(new \MongoRegex('/.*' . $term . '.*/i')))
                    ->addOr($q->expr()->field('raisonSociale')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')))
                    ->addOr($q->expr()->field('adresse.adresse')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')))
                    ->addOr($q->expr()->field('adresse.codePostal')->equals(new \MongoRegex('/.*' . $term . '.*/i')))
                    ->addOr($q->expr()->field('adresse.commune')->equals(new \MongoRegex('/.*' . RechercheTool::getCorrespondances($term) . '.*/i')));
            if (!$withNonActif) {
                $q->field('actif')->equals(true);
            }
            $societes = $q->limit(1000)->getQuery()->execute();

            $currentResults = array();
            foreach ($societes as $societe) {
                $currentResults[$societe->getId()] = $societe->getIntitule();
            }

            if (!is_null($results)) {
                $results = array_intersect_assoc($results, $currentResults);
            } else {
                $results = $currentResults;
            }
        }

        return is_null($results) ? array() : $results;
    }

    public function findAllTags() {
        $request = $this->createQueryBuilder()
                ->distinct('tags')
                ->hydrate(false)
                ->getQuery()
                ->execute();
        return $request->toArray();
    }

    public function findAllPassages($societe) {
        $societe = $this->findOneById($societe->getId());
        $passagesArray = array();
        foreach ($societe->getEtablissements() as $etb) {
            $passages = $this->dm->getRepository('AppBundle:Passage')->findByEtablissement($etb->getId());
            foreach ($passages as $passage) {
                $passagesArray[$passage->getId()] = $passage;
            }
        }
        return $passagesArray;
    }

}
