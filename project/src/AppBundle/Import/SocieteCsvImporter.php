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

class SocieteCsvImporter extends CsvFile {

    protected $dm;

    const CSV_ID_SOCIETE = 0;
    const CSV_ADRESSE_SOCIETE_1 = 4;
    const CSV_ADRESSE_SOCIETE_2 = 5;
    const CSV_CP = 6;
    const CSV_VILLE = 7;
    const CSV_PAYS = 8;
    const CSV_TEL_FIXE = 9;
    const CSV_TEL_MOBILE = 10;
    const CSV_FAX = 11;
    const CSV_SITEWEB = 12;
    const CSV_EMAIL = 13;
    const CSV_ADRESSE_COMMENTAIRE = 14;
    const CSV_SOUS_TRAITANT = 21;
    const CSV_RAISON_SOCIALE = 22;
    const CSV_COMMENTAIRE = 25;
    const CSV_TYPE_SOCIETE = 26;
    const CSV_CODE_COMPTABLE = 30;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        foreach ($csv as $data) {
            $societe = $this->createFromImport($data);
            $this->dm->persist($societe);
            if ($cpt > 1000) {
                $this->dm->flush();
                $cpt = 0;
            }
            $cpt++;
        } 
        $this->dm->flush();
    }

    public function createFromImport($ligne) {

        $societe = new Societe();

        $societe->setIdentifiant(sprintf("%06d", $ligne[self::CSV_ID_SOCIETE]));

        $societe->setId();

        $societe->setRaisonSociale($ligne[self::CSV_RAISON_SOCIALE]);
        $societe->setCodeComptable($ligne[self::CSV_CODE_COMPTABLE]);
        $societe->setCommentaire($ligne[self::CSV_COMMENTAIRE]);
        $societe->setSousTraitant(!($ligne[self::CSV_SOUS_TRAITANT]));

        $adresse = new Adresse();

        $adresseStr = $ligne[self::CSV_ADRESSE_SOCIETE_1];
        if ($ligne[self::CSV_ADRESSE_SOCIETE_2]) {
            $adresseStr .=", " . $ligne[self::CSV_ADRESSE_SOCIETE_2];
        }
        $adresse->setAdresse($adresseStr);
        $adresse->setCodePostal($ligne[self::CSV_CP]);
        $adresse->setCommune($ligne[self::CSV_VILLE]);
        $adresse->setFax($ligne[self::CSV_FAX]);
        $adresse->setTelephoneFixe($ligne[self::CSV_TEL_FIXE]);
        $adresse->setTelephonePortable($ligne[self::CSV_TEL_MOBILE]);

        $societe->setAdresse($adresse);
        if ($ligne[self::CSV_TYPE_SOCIETE] == "") {
            $societe->setTypeSociete(EtablissementManager::TYPE_ETB_NON_SPECIFIE);
        } else {

            $types_etablissements = EtablissementManager::$type_etablissements_libelles;
            $types_etb_keys = array_keys($types_etablissements);
        
            $societe->setTypeSociete($types_etb_keys[intval($ligne[self::CSV_TYPE_SOCIETE])-1]);
        }
        return $societe;
    }

}
