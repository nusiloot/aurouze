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
use AppBundle\Document\Etablissement;
use AppBundle\Document\Adresse;
use AppBundle\Document\Coordonnees;
use AppBundle\Manager\SocieteManager;
use AppBundle\Manager\EtablissementManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use AppBundle\Document\ContactCoordonnee;

class EtablissementCsvImporter extends CsvFile {

    protected $dm;
    protected $sm;
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
    const CSV_SITE_WEB = 12;
    const CSV_EMAIL = 13;

    const CSV_ACTIF = 16;
    const CSV_RAISON_SOCIALE = 23;
    const CSV_TYPE_ETABLISSEMENT = 27;


    const CSV_CMT = 38;
    const CSV_COORD_LAT = 39;
    const CSV_COORD_LON = 40;

    public function __construct(DocumentManager $dm, SocieteManager $sm, EtablissementManager $em) {
        $this->dm = $dm;
        $this->sm = $sm;
        $this->em = $em;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $progress = new ProgressBar($output, 100);
        $progress->start();

        $cpt = 0;
        $cptTotal = 0;
        foreach ($csv as $data) {
            $etablissement = $this->createFromImport($data, $output);
            if (!$etablissement) {
                continue;
            }
            $this->dm->persist($etablissement);
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }
            if ($cpt > 10) {
                $this->dm->flush();
                $this->dm->clear();
                gc_collect_cycles();
                $cpt = 0;
            }
            $cpt++;
        }
        $this->dm->flush();
        $progress->finish();
    }

    public function createFromImport($ligne, $output) {
        $societe = $this->sm->getRepository()->findOneBy(array('identifiantReprise' => $ligne[self::CSV_ID_SOCIETE]));

        if (!$societe) {

            $output->writeln(sprintf("<error>La société %s n'existe pas</error>", $ligne[self::CSV_ID_SOCIETE]));
            return;
        }

        $etablissement = $this->em->getRepository()->findOneBy(array('identifiantReprise', $ligne[self::CSV_ID_ADRESSE]));
        if (!$etablissement) {
            $etablissement = new Etablissement();
        }
        $etablissement->setSociete($societe);
        $etablissement->setIdentifiant($societe->getIdentifiant());
        $etablissement->setIdentifiantReprise($ligne[self::CSV_ID_ADRESSE]);

        $etablissement->setNom($ligne[self::CSV_NOM_ETB]);
        $adresse_str = $ligne[self::CSV_ADRESS_1];
        if ($ligne[self::CSV_ADRESS_2]) {
            $adresse_str .= ', ' . $ligne[self::CSV_ADRESS_2];
        }
        $etablissement->setCommentaire(str_replace('#', "\n", $ligne[self::CSV_CMT]));
        $etablissement->setNom($ligne[self::CSV_RAISON_SOCIALE]);

        $adresse = new Adresse();
        $adresse->setAdresse($adresse_str);
        $adresse->setCodePostal($ligne[self::CSV_CP]);
        $adresse->setCommune($ligne[self::CSV_VILLE]);


        $adresse->setCoordonnees(new Coordonnees());
        if (isset($ligne[self::CSV_COORD_LAT]) && isset($ligne[self::CSV_COORD_LON]) && $ligne[self::CSV_COORD_LAT] && $ligne[self::CSV_COORD_LON]) {
            $lat = $ligne[self::CSV_COORD_LAT];
            $lon = $ligne[self::CSV_COORD_LON];
            $adresse->getCoordonnees()->setLat($lat);
            $adresse->getCoordonnees()->setLon($lon);
            echo "lat=$lat lon=$lon déjà enregistré \n";
        }
        else {
            // echo "pas de calcul des coordonnées pour l'instant \n";
                // $msg = $this->em->getOSMAdresse()->calculCoordonnees($adresse);
                // sleep(0.5);
                // if ($msg && is_string($msg)) {
                //     echo $msg . "\n";
                // }
            }
        $etablissement->setAdresse($adresse);


        $contactCoordonnee = new ContactCoordonnee();
        $contactCoordonnee->setTelephoneFixe($ligne[self::CSV_TEL_FIXE]);
        $contactCoordonnee->setTelephoneMobile($ligne[self::CSV_TEL_MOBILE]);
        $contactCoordonnee->setFax($ligne[self::CSV_FAX]);
        $contactCoordonnee->setSiteInternet($ligne[self::CSV_SITE_WEB]);
        $contactCoordonnee->setEmail($ligne[self::CSV_EMAIL]);

         $etablissement->setContactCoordonnee($contactCoordonnee);

        if ($ligne[self::CSV_TYPE_ETABLISSEMENT] == "") {
            $etablissement->setType(EtablissementManager::TYPE_ETB_NON_SPECIFIE);
        } else {

            $types_etablissements = EtablissementManager::$type_libelles;
            $types_etb_keys = array_keys($types_etablissements);

            $etablissement->setType($types_etb_keys[intval($ligne[self::CSV_TYPE_ETABLISSEMENT]) - 1]);
        }

        return $etablissement;
    }

}
