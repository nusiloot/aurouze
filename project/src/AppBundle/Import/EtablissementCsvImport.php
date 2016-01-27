<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Import;

/**
 * Description of EtablissementCsvImport
 *
 * @author mathurin
 */
use AppBundle\Document\Etablissement as Etablissement;
use AppBundle\Document\Adresse as Adresse;
use AppBundle\Manager\EtablissementManager as EtablissementManager;

class EtablissementCsvImport extends CsvFile {

    protected $dm;
    protected $etablissementManager;

    const CSV_ID_SOCIETE = 0;
    const CSV_ID_ADRESSE = 1;
    const CSV_ADRESSE_TYPE = 2;
    const CSV_NOM_ETB = 3;
    const CSV_ADRESS_1 = 4;
    const CSV_ADRESS_2 = 5;
    const CSV_CP = 6;
    const CSV_VILLE = 7;
    const CSV_PAYS = 8;
    const CSV_TEL_FIXE = 9;
    const CSV_TEL_MOBILE = 10;
    const CSV_FAX = 11;
    const CSV_SITEWEB = 12;
    const CSV_EMAIL = 13;
    const CSV_CMT = 14; # A contacter
    const CSV_ACTIF = 15;
    const CSV_RAISON_SOCIALE = 22;
    const CSV_TYPE_ETABLISSEMENT = 26;

    public function setManager($dm) {
        $this->dm = $dm;
    }

    public function import() {
        $this->errors = array();
        $csv = $this->getCsv();
        $this->etablissementManager = new EtablissementManager($this->dm);


        foreach ($csv as $data) {
            $etablissement = $this->createFromImport($data);
            $this->dm->persist($etablissement);
            $this->dm->flush();
        }
    }

    public function createFromImport($ligne) {

        $etablissement = new Etablissement();
        
        $adresseId = sprintf("%06d", $ligne[self::CSV_ID_ADRESSE]);
        
        $etablissement->setIdentifiantSociete(sprintf("%06d", $ligne[self::CSV_ID_SOCIETE]));
    //    $etablissement->setIdentifiant($societeId.$this->etablissementManager->getNextNumeroEtablissement($societeId));
          $etablissement->setIdentifiant($adresseId);
        
        $etablissement->setId();

        $etablissement->setNom($ligne[self::CSV_NOM_ETB]);
        $adresse_str = $ligne[self::CSV_ADRESS_1];
        if ($ligne[self::CSV_ADRESS_2]) {
            $adresse_str .= ', ' . $ligne[self::CSV_ADRESS_2];
        }
        $etablissement->setNomContact($ligne[self::CSV_CMT]);
        $etablissement->setRaisonSociale($ligne[self::CSV_RAISON_SOCIALE]);
        
        $adresse = new Adresse();
        $adresse->setAdresse($adresse_str);        
        $adresse->setCodePostal($ligne[self::CSV_CP]);
        $adresse->setCommune($ligne[self::CSV_VILLE]);
        $adresse->setTelephoneFixe($ligne[self::CSV_TEL_FIXE]);
        $adresse->setTelephonePortable($ligne[self::CSV_TEL_MOBILE]);
        $adresse->setFax($ligne[self::CSV_FAX]);
        
        $etablissement->setAdresse($adresse);

        if ($ligne[self::CSV_TYPE_ETABLISSEMENT] == "") {
            $etablissement->setTypeEtablissement(EtablissementManager::TYPE_ETB_NON_SPECIFIE);
        } else {

            $types_etablissements = EtablissementManager::$type_etablissements_libelles;
            $types_etablissements_values = array_values($types_etablissements);

            $type_etb_libelle = $types_etablissements_values[$ligne[self::CSV_TYPE_ETABLISSEMENT]];
            $types_etb_key = array_keys($types_etablissements, $type_etb_libelle);

            $etablissement->setTypeEtablissement($types_etb_key[0]);
        }

        return $etablissement;
    }

}
