<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;

class CompteManager {

    protected $dm;

    const TYPE_TECHNICIEN = "TECHNICIEN";
    const TYPE_COMMERCIAL = "COMMERCIAL";
    const TYPE_ADMINISTRATIF = "ADMINISTRATIF";
    const TYPE_AUTRE = "AUTRE";
    const TAG_CALENDRIER = "CALENDRIER";
    const TAG_SOUS_TRAITANT = "SOUS-TRAITANT";

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
        self::TAG_CALENDRIER => 'Calendrier',
        self::TAG_SOUS_TRAITANT => 'Sous traitant',
    );
    public static $civilites = array(self::CIVILITE_MONSIEUR => self::CIVILITE_MONSIEUR, self::CIVILITE_MADAME => self::CIVILITE_MADAME, self::CIVILITE_MADEMOISELLE => self::CIVILITE_MADEMOISELLE
    );

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Compte');
    }

    public function getAllInterlocuteurs($societe, $withActif = true) {
        $etablissements = $this->dm->getRepository('AppBundle:Etablissement')->findBy(array('societe' => $societe->getId(), 'actif' => true));
        $comptes = $this->dm->getRepository('AppBundle:Compte')->findBy(array('societe' => $societe->getId(), 'actif' => true));

        return array_merge(array($societe), $etablissements, $comptes);
    }

}
