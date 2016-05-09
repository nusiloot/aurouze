<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * EtablissementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EtablissementRepository extends DocumentRepository {

    public function findAllOrderedByIdentifiantSociete($societe) {

        return $this->findBy(array('societe' => $societe->getId()));
    }
    public function findAllOrderedByIdentifiantSocieteArray($societe) {

        $etbs =  $this->findBy(array('societe' => $societe->getId()));
        $result = array();
        foreach ($etbs as $etb) {
            $result[$etb->getId()] = $etb;
        }
        return $result;
    }

    public function findByTerms($queryString) {
        $terms = explode(" ", trim(preg_replace("/[ ]+/", " ", $queryString)));
        $results = null;
        foreach($terms as $term) {
            if(strlen($term) < 3) {
                continue;
            }
            $q = $this->createQueryBuilder();
            $etablissements = $q
                  ->addOr($q->expr()->field('identifiant')->equals(new \MongoRegex('/.*'.$term.'.*/i')))
                  ->addOr($q->expr()->field('nom')->equals(new \MongoRegex('/.*'.$term.'.*/i')))
                  ->addOr($q->expr()->field('adresse.adresse')->equals(new \MongoRegex('/.*'.$term.'.*/i')))
                  ->addOr($q->expr()->field('adresse.codePostal')->equals(new \MongoRegex('/.*'.$term.'.*/i')))
                  ->addOr($q->expr()->field('adresse.commune')->equals(new \MongoRegex('/.*'.$term.'.*/i')))
                  ->field('actif')->equals(true)
                  ->limit(1000)
                  ->getQuery()->execute();

            $currentResults = array();
            foreach($etablissements as $etablissement) {
                $currentResults[$etablissement->getId()] = $etablissement->getIntitule();
            }

            if(!is_null($results)) {
                $results = array_intersect_assoc($results, $currentResults);
            } else {
                $results = $currentResults;
            }
        }

        return is_null($results) ? array() : $results;
    }

    public function findAllPostfixByIdentifiantSociete($societe) {
        $etablissements = $this->findAllOrderedByIdentifiantSociete($societe);
        $allPostfixByIdentifiantSociete = array();
        if (count($etablissements)) {
            foreach ($etablissements as $etablissement) {
                $postfix = substr($etablissement->getIdentifiant(), 6);
                $allPostfixByIdentifiantSociete[$postfix] = $postfix;
            }
        }

        return $allPostfixByIdentifiantSociete;
    }

}
