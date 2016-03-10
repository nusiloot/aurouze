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
use AppBundle\Document\Coordinates;
use AppBundle\Manager\EtablissementManager as EtablissementManager;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;

class EtablissementCsvImporter extends CsvFile {

    protected $dm;
    protected $em;

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
    const CSV_COORD_LAT = 38;
    const CSV_COORD_LON = 39;

    public function __construct(DocumentManager $dm, EtablissementManager $em) {
        $this->dm = $dm;
        $this->em = $em;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        foreach ($csv as $data) {
            $etablissement = $this->createFromImport($data);
            $this->dm->persist($etablissement);
            if ($cpt > 1000) {
                $this->dm->flush();
                $cpt = 0;
            }
            $cpt++;
        }
        $this->dm->flush();
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
       
        $adresse->setCoordinates(new Coordinates());
        if ($ligne[self::CSV_COORD_LAT] && $ligne[self::CSV_COORD_LON]) {
            $lat = $ligne[self::CSV_COORD_LAT];
            $lon = $ligne[self::CSV_COORD_LON];
            $adresse->getCoordinates()->setLat($lat);
            $adresse->getCoordinates()->setLon($lon);
             echo "lat=$lat lon=$lon déjà enregistré \n";
        } else {
            $msg = $this->em->getOSMAdresse()->calculCoordonnees($adresse);
            sleep(0.5);
            if ($msg && is_string($msg)) {
                echo $msg . "\n";
            }
        }
        $etablissement->setAdresse($adresse);

        if ($ligne[self::CSV_TYPE_ETABLISSEMENT] == "") {
            $etablissement->setTypeEtablissement(EtablissementManager::TYPE_ETB_NON_SPECIFIE);
        } else {

            $types_etablissements = EtablissementManager::$type_etablissements_libelles;
            $types_etb_keys = array_keys($types_etablissements);

            $etablissement->setTypeEtablissement($types_etb_keys[intval($ligne[self::CSV_TYPE_ETABLISSEMENT]) - 1]);
        }

        return $etablissement;
    }

}
