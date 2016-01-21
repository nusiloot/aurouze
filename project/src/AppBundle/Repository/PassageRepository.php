<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * EtablissementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PassageRepository extends DocumentRepository {

    public function findByEtablissementAndPrestation($etablissementIdentifiant, $prestationIdentifiant) {
        return $this->findBy(
                                array('etablissementIdentifiant' => $etablissementIdentifiant, 'prestationIdentifiant' => $prestationIdentifiant));
    }

    public function findPassagesForEtablissementsAndPrestationIdentifiants($etablissementIdentifiant, $prestationIdentifiant) {
        $passagesByEtablissementAndPrestation = $this->findByEtablissementAndPrestation($etablissementIdentifiant, $prestationIdentifiant);
        
        $allPassagesNumeros = array();
        if (count($passagesByEtablissementAndPrestation)) {
            foreach ($passagesByEtablissementAndPrestation as $passageByEtablissementAndPrestation) {
                $allPassagesNumeros[$passageByEtablissementAndPrestation->getNumeroPassageIdentifiant()] = $passageByEtablissementAndPrestation->getNumeroPassageIdentifiant();
            }
        }
        return $allPassagesNumeros;
    }

}
