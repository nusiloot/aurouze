<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MongoDate as MongoDate;

class ContratRepository extends DocumentRepository {

    public function findByEtablissement($etablissement) {
        return $this->findBy(
                        array('etablissement.id' => $etablissement->getId()),
                        array('dateFin' => 'DESC'));
    }

    public function findBySocieteAndDateCreation($societe, $dateCreation) {
        $contratsBySociete = $this->findBy(array("societe.id" => $societe->getId()));
        $results = array();
        foreach ($contratsBySociete as $contrat) {
            if($contrat->getDateCreation()->format('Ymd') == $dateCreation->format('Ymd'))
            $results[] = $contrat;
        }
        return $results;
    }

    public function findNextNumero($societe, $dateCreation) {
        $contrats = $this->findBySocieteAndDateCreation($societe, $dateCreation);
        $identifiants = array();
        foreach ($contrats as $contrat) {
            $identifiants[$contrat->getIdentifiant()] = $contrat->getIdentifiant();
        }
        return (count($identifiants) > 0) ? (substr(max($identifiants), -3) + 1) : 1;
    }

    public function findContratMouvements($etablissement, $isFacturable, $isFacture) {

        return $this->createQueryBuilder()
             ->select('mouvements')
             ->field('etablissement.id')->equals($etablissement->getId())
             ->field('mouvements.facturable')->equals($isFacturable)
             ->field('mouvements.facture')->equals($isFacture)
             ->getQuery()
             ->execute();
    }

}
