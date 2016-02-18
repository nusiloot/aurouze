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

    public function findAllOrderedByIdentifiantSociete($societeIdentifiant) {
        return $this->findBy(array('identifiant_societe' => $societeIdentifiant));
    }

    public function findByTerm($term,$criteria) {
        $request = $this->createQueryBuilder()
                ->find()
                ->field($criteria)->equals(new \MongoRegex('/.*'.$term.'.*/i'))
                ->getQuery()
                ->execute();
        return $request;
    }

    public function findAllPostfixByIdentifiantSociete($societeIdentifiant) {
        $etablissements = $this->findAllOrderedByIdentifiantSociete($societeIdentifiant);
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
