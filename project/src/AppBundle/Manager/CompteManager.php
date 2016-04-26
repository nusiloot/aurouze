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

class CompteManager {
    
    protected $dm;    
    
    const TYPE_TECHNICIEN = "TECHNICIEN";
    const TYPE_COMMERCIAL = "COMMERCIAL";
    const TYPE_ADMINISTRATIF = "ADMINISTRATIF";
    const TYPE_AUTRE = "AUTRE";
    
    public static $tagsCompteLibelles = array(
        self::TYPE_ADMINISTRATIF => 'Administratif',
        self::TYPE_COMMERCIAL => 'Commercial',
        self::TYPE_AUTRE => 'Autre',
        self::TYPE_TECHNICIEN => 'Technicien',
    );


    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Compte');
    }


}
