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
use AppBundle\Document\Societe as Societe;
use AppBundle\Document\Adresse as Adresse;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\EtablissementManager as EtablissementManager;
use AppBundle\Document\ContactCoordonnee;

class SocieteCsvImporter extends CsvFile {

    protected $dm;

    const CSV_ID_SOCIETE = 0;
    const CSV_TYPE_ADRESSE = 2;
    const CSV_ADRESSE_SOCIETE_1 = 4;
    const CSV_ADRESSE_SOCIETE_2 = 5;
    const CSV_CP = 6;
    const CSV_VILLE = 7;
    const CSV_PAYS = 8;
    const CSV_TEL_FIXE = 9;
    const CSV_TEL_MOBILE = 10;
    const CSV_FAX = 11;
    const CSV_SITE_WEB = 12;
    const CSV_EMAIL = 13;
    const CSV_ADRESSE_COMMENTAIRE = 14;
    const CSV_TYPE_SOCIETE = 27;
    const CSV_RAISON_SOCIALE = 23;

    const CSV_SOUS_TRAITANT = 22;

    const CSV_COMMENTAIRE = 26;

    const CSV_CODE_COMPTABLE = 31;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        foreach ($csv as $data) {

            $societe = $this->createFromImport($data);
            if(!$societe) {

                continue;
            }
            $this->dm->persist($societe);
            if ($cpt > 1000) {
                $this->dm->flush();
                $this->dm->clear();
                gc_collect_cycles();
                $cpt = 0;
            }
            $cpt++;
        }
        $this->dm->flush();
    }

    public function createFromImport($ligne) {

        if(!is_numeric($ligne[self::CSV_ID_SOCIETE])) {

            return;
        }

        $societe = new Societe();

        $societe->setIdentifiantReprise($ligne[self::CSV_ID_SOCIETE]);

        $societe->setRaisonSociale($ligne[self::CSV_RAISON_SOCIALE]);
        $societe->setCodeComptable($ligne[self::CSV_CODE_COMPTABLE]);
        $societe->setCommentaire(null);
        $ligne[self::CSV_COMMENTAIRE] = str_replace('"', "", $ligne[self::CSV_COMMENTAIRE]);
        $ligne[self::CSV_ADRESSE_COMMENTAIRE] = str_replace('"', "", $ligne[self::CSV_COMMENTAIRE]);
        if($ligne[self::CSV_TYPE_ADRESSE] != "1") {
            $ligne[self::CSV_ADRESSE_COMMENTAIRE] = null;
        }
        if(trim($ligne[self::CSV_COMMENTAIRE])) {
            $societe->setCommentaire($ligne[self::CSV_COMMENTAIRE]."\n");
        }
        if(trim($ligne[self::CSV_ADRESSE_COMMENTAIRE]) && $ligne[self::CSV_COMMENTAIRE] != $ligne[self::CSV_ADRESSE_COMMENTAIRE]) {
            $societe->setCommentaire($societe->getCommentaire()."\n".$ligne[self::CSV_ADRESSE_COMMENTAIRE]);
        }
        $societe->setSousTraitant(!($ligne[self::CSV_SOUS_TRAITANT]));

        $adresse = new Adresse();

        $adresseStr = $ligne[self::CSV_ADRESSE_SOCIETE_1];
        if ($ligne[self::CSV_ADRESSE_SOCIETE_2]) {
            $adresseStr .=", " . $ligne[self::CSV_ADRESSE_SOCIETE_2];
        }
        $adresse->setAdresse($adresseStr);
        $adresse->setCodePostal($ligne[self::CSV_CP]);
        $adresse->setCommune($ligne[self::CSV_VILLE]);

        $societe->setAdresse($adresse);

         $contactCoordonnee = new ContactCoordonnee();
        $contactCoordonnee->setTelephoneFixe($ligne[self::CSV_TEL_FIXE]);
        $contactCoordonnee->setTelephoneMobile($ligne[self::CSV_TEL_MOBILE]);
        $contactCoordonnee->setFax($ligne[self::CSV_FAX]);
        $contactCoordonnee->setSiteInternet($ligne[self::CSV_SITE_WEB]);
        $contactCoordonnee->setEmail($ligne[self::CSV_EMAIL]);


         $societe->setContactCoordonnee($contactCoordonnee);
        if ($ligne[self::CSV_TYPE_SOCIETE] == "") {
            $societe->setType(EtablissementManager::TYPE_ETB_NON_SPECIFIE);
        } else {

            $types_etablissements = EtablissementManager::$type_libelles;
            $types_etb_keys = array_keys($types_etablissements);

            if((intval($ligne[self::CSV_TYPE_SOCIETE])-1) >= 0) {
                $societe->setType($types_etb_keys[intval($ligne[self::CSV_TYPE_SOCIETE])-1]);
            }
        }
        return $societe;
    }

}
