<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtablissementManager
 *
 * @author mathurin
 */

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Etablissement as Etablissement;
use AppBundle\Document\Passage as Passage;

class PassageManager {

    protected $dm;

    function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    function create(Etablissement $etablissement, $prestationIdentifiant = "123567") {
        $passage = new Passage();
        
        $passage->setEtablissementIdentifiant($etablissement->getIdentifiant());       
        $passage->setPrestationIdentifiant($prestationIdentifiant);  
        
        $numeroPassageIdentifiant = $this->getNextNumeroPassage($etablissement->getIdentifiant(),$prestationIdentifiant);
        
        $passage->setNumeroPassageIdentifiant($numeroPassageIdentifiant);       
        $passage->setId();
        $passage->updateEtablissementInfos($etablissement);
        return $passage;
    }

    public function getNextNumeroPassage($etablissementIdentifiant, $prestationIdentifiant) {
        $allPassagesForEtablissementsAndPrestationIdentifiants = $this->dm->getRepository('AppBundle:Passage')->findPassagesForEtablissementsAndPrestationIdentifiants($etablissementIdentifiant,$prestationIdentifiant);
        if(!count($allPassagesForEtablissementsAndPrestationIdentifiants)){
            return sprintf("%03d", 1);
        }
        return sprintf("%03d", max($allPassagesForEtablissementsAndPrestationIdentifiants) + 1);
    }    
    
}
