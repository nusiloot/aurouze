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
use AppBundle\Import\EtablissementCsvImport as EtablissementCSVImport;
use AppBundle\Tool\OSMAdresses;

class EtablissementManager {

    protected $dm;
    protected $osmAdresse;

    const TYPE_ETB_BOULANGERIE = "BOULANGERIE";
    const TYPE_ETB_RESTAURANT = "RESTAURANT";
    const TYPE_ETB_ADMINISTRATION = "ADMINISTRATION";
    const TYPE_ETB_MAIRIE = "MAIRIE";
    const TYPE_ETB_ENTREPRISE_PRIVEE = "ENTREPRISE_PRIVEE";
    const TYPE_ETB_PARTICULIER = "PARTICULIER";
    const TYPE_ETB_FERME = "FERME";
    const TYPE_ETB_SYNDIC = "SYNDIC";
    const TYPE_ETB_COMMERCE = "COMMERCE";
    const TYPE_ETB_CAFE_BRASSERIE = "CAFE_BRASSERIE";
    const TYPE_ETB_AUTRE = "AUTRE";
    const TYPE_ETB_HOTEL = "HOTEL";
    const TYPE_ETB_NON_SPECIFIE = "NON_SPECIFIE";

    public static $type_libelles = array(
        self::TYPE_ETB_BOULANGERIE => "Boulangerie",
        self::TYPE_ETB_RESTAURANT => "Restaurant",
        self::TYPE_ETB_ADMINISTRATION => "Administration",
        self::TYPE_ETB_MAIRIE => "Mairie",
        self::TYPE_ETB_ENTREPRISE_PRIVEE => "Entreprise privée",
        self::TYPE_ETB_PARTICULIER => "Particulier",
        self::TYPE_ETB_FERME => "Ferme",
        self::TYPE_ETB_SYNDIC => "Syndic",
        self::TYPE_ETB_COMMERCE => "Commerce",
        self::TYPE_ETB_CAFE_BRASSERIE => "Café brasserie",
        self::TYPE_ETB_AUTRE => "Autre",
        self::TYPE_ETB_HOTEL => "Hôtel",
        self::TYPE_ETB_NON_SPECIFIE => "Non spécifié");
    public static $type_icon = array(
        self::TYPE_ETB_BOULANGERIE => "cake",
        self::TYPE_ETB_RESTAURANT => "local-dining",
        self::TYPE_ETB_ADMINISTRATION => "description",
        self::TYPE_ETB_MAIRIE => "account-balance",
        self::TYPE_ETB_ENTREPRISE_PRIVEE => "store",
        self::TYPE_ETB_PARTICULIER => "face",
        self::TYPE_ETB_FERME => "spa",
        self::TYPE_ETB_SYNDIC => "home",
        self::TYPE_ETB_COMMERCE => "local-grocery-store",
        self::TYPE_ETB_CAFE_BRASSERIE => "local-cafe",
        self::TYPE_ETB_AUTRE => "place",
        self::TYPE_ETB_HOTEL => "local-hotel",
        self::TYPE_ETB_NON_SPECIFIE => "do-not-disturb");

    function __construct(DocumentManager $dm, OSMAdresses $osmAdresse) {
        $this->dm = $dm;
        $this->osmAdresse = $osmAdresse;
    }

    function create() {
        $etablissement = new Etablissement();
        $identifiant = $this->getNextIdentifiant();

        $etablissement->setIdentifiant($identifiant);
        $etablissement->setId();
        $etablissement->setNom("Test " . $etablissement->getIdentifiant());
        return $etablissement;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Etablissement');
    }

    public function getNextIdentifiant() {
        $allEtablissementsIdentifiants = $this->dm->getRepository('AppBundle:Etablissement')->findAllEtablissementsIdentifiants();
    }

    public function getNextNumeroEtablissement($societeIdentifiant) {
        $allEtablissementsIdentifiants = $this->dm->getRepository('AppBundle:Etablissement')->findAllPostfixByIdentifiantSociete($societeIdentifiant);

        if (!count($allEtablissementsIdentifiants)) {
            return sprintf("%02d", 1);
        }
        return sprintf("%02d", max($allEtablissementsIdentifiants) + 1);
    }
    
    public function getOSMAdresse() {
        return $this->osmAdresse;
    }

}
