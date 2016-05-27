<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * FactureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FactureRepository extends DocumentRepository {

    public function findAllByContrat($contrat) {

        return $this->createQueryBuilder()
                        ->field('lignes.origineDocument.$id')->equals($contrat->getId())
                        ->getQuery()
                        ->execute();
    }

    public function findByTerms($queryString) {
        $terms = explode(" ", trim(preg_replace("/[ ]+/", " ", $queryString)));
        $results = null;
        foreach ($terms as $term) {
            if (strlen($term) < 2) {
                continue;
            }
            $q = $this->createQueryBuilder();

            $q->field('cloture')->equals(false);
            if (preg_match('/^[0-9]+\.[0-9]+$/', $term)) {
                $nbInf = $term - 0.0001;
                $nbSup = $term + 0.0001;
                $q->addOr($q->expr()->field('montantTTC')->lt($nbSup)->gt($nbInf))
                  ->addOr($q->expr()->field('montantAPayer')->lt($nbSup)->gt($nbInf));
            } else {
                $q->addOr($q->expr()->field('destinataire.nom')->equals(new \MongoRegex('/.*' . $term . '.*/i')))
                        ->addOr($q->expr()->field('numeroFacture')->equals(new \MongoRegex('/.*' . $term . '.*/i')));
            }
            $factures = $q->limit(1000)->getQuery()->execute();

            $currentResults = array();
            foreach ($factures as $facture) {
                $currentResults[$facture->getId()] = $facture->__toString();
            }

            if (!is_null($results)) {
                $results = array_intersect_assoc($results, $currentResults);
            } else {
                $results = $currentResults;
            }
        }

        return is_null($results) ? array() : $results;
    }

}
