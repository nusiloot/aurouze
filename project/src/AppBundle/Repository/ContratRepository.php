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

    public function findByEtablissementAndDateCreation($etablissement, $dateCreation) {
        $contratsByEtablissement = $this->findByEtablissement($etablissement);
        $results = array();
        foreach ($contratsByEtablissement as $contrat) {
            if($contrat->getDateCreation()->format('Ymd') == $dateCreation->format('Ymd'))
            $results[] = $contrat;
        }
        return $results;
    }

    public function findNextNumero($etablissement, $dateCreation) {
        $contrats = $this->findByEtablissementAndDateCreation($etablissement, $dateCreation);
        $identifiants = array();
        foreach ($contrats as $contrat) {
            $identifiants[$contrat->getIdentifiant()] = $contrat->getIdentifiant();
        }
        return (count($identifiants) > 0) ? (substr(max($identifiants), -3) + 1) : 1;
    }

}
