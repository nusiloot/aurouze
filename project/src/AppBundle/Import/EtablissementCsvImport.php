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
use AppBundle\Manager\EtablissementManager as EtablissementManager;

class EtablissementCsvImport extends CsvFile {

    protected $dm;

    const CSV_ID = 0;
    const CSV_ID_SOCIETE = 1;
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

    public function setManager($dm) {
        $this->dm = $dm;
    }

    public function import() {
        $this->errors = array();
        $csv = $this->getCsv();
        $etablissementManager = new EtablissementManager($this->dm);


        foreach ($csv as $data) {
            $etablissement = $etablissementManager->createFromImport($data);
            $this->dm->persist($etablissement);
            $this->dm->flush();
        }
    }

    public function createFromImport($ligne) {

        $etablissement = new Etablissement();
        $etablissement->setIdentifiant(sprintf("%06d", $ligne[self::CSV_ID]));
        $etablissement->setId();

        $etablissement->setNom($ligne[EtablissementCSVImport::CSV_NOM_ETB]);
        $adresse = $ligne[EtablissementCSVImport::CSV_ADRESS_1];
        if ($ligne[EtablissementCSVImport::CSV_ADRESS_2]) {
            $adresse .= ', ' . $ligne[EtablissementCSVImport::CSV_ADRESS_2];
        }
        $etablissement->setAdresse($adresse);
        $etablissement->setCodePostal($ligne[EtablissementCSVImport::CSV_CP]);
        $etablissement->setCommune($ligne[EtablissementCSVImport::CSV_VILLE]);
        $etablissement->setTelephoneFixe($ligne[EtablissementCSVImport::CSV_TEL_FIXE]);
        $etablissement->setTelephonePortable($ligne[EtablissementCSVImport::CSV_TEL_MOBILE]);
        $etablissement->setFax($ligne[EtablissementCSVImport::CSV_FAX]);
        $etablissement->setNomContact($ligne[EtablissementCSVImport::CSV_CMT]);

//        if ($ligne[EtablissementCSVImport::CSV_TYPE_ETABLISSEMENT] == "") {
//            $etablissement->setTypeEtablissement(self::TYPE_ETB_NON_SPECIFIE);
//        } else {
//
//            $types_etablissements = self::$type_etablissements_libelles;
//            $types_etablissements_values = array_values($types_etablissements);
//
//            $type_etb_libelle = $types_etablissements_values[$ligne[EtablissementCSVImport::CSV_TYPE_ETABLISSEMENT]];
//            $types_etb_key = array_keys($types_etablissements, $type_etb_libelle);
//
//            $etablissement->setTypeEtablissement($types_etb_key[0]);
//        }

        return $etablissement;
    }

}
