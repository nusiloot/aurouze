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
    const TYPE_CALENDRIER = "CALENDRIER";
    
    const CIVILITE_MONSIEUR = "Monsieur";
    const CIVILITE_MADAME = "Madame";
    const CIVILITE_MADEMOISELLE = "Mademoiselle";
    
    const TITRE_MONSIEUR_MAIRE = "Monsieur le Maire";
    const TITRE_MADAME_MAIRE = "Madame le Maire";
    const TITRE_MONSIEUR_PRESIDENT_SYNDICAL = "Monsieur le Président du conseil syndical";
    const TITRE_MADAME_PRESIDENTE_SYNDICAL = "Madame la Présidente du conseil syndical";
    const TITRE_MONSIEUR_DIRECTEUR = "Monsieur le Directeur";
    const TITRE_MADAME_DIRECTEUR = "Madame la Directrice";
    
    
    public static $tagsCompteLibelles = array(
        self::TYPE_ADMINISTRATIF => 'Administratif',
        self::TYPE_COMMERCIAL => 'Commercial',
        self::TYPE_AUTRE => 'Autre',
        self::TYPE_TECHNICIEN => 'Technicien',
        self::TYPE_CALENDRIER => 'Calendrier',
    );


    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Compte');
    }


}
