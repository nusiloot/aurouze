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
    const CSV_ID_ADRESSE = 0;

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
    const CSV_SITE_WEB = 12;
    const CSV_EMAIL = 13;

    const CSV_ACTIF = 16;
    const CSV_RAISON_SOCIALE = 23;
    const CSV_TYPE_ETABLISSEMENT = 27;


    const CSV_CMT = 37;
    const CSV_COORD_LAT = 39;
    const CSV_COORD_LON = 40;
    const CSV_ID_ANCIENNE_ADRESSE_SOCIETE = 41;

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
        $societe = null;
        $etablissementPlaceSurEntite = false;

        if(!isset($ligne[self::CSV_ID_ANCIENNE_ADRESSE_SOCIETE])){
          $output->writeln(sprintf("\n<comment>La ligne %s possède un établissement dont la société n'a pas d'adresse de facturation.  </comment>", implode(";", $ligne)));
        }else{
            $societe = $this->sm->getRepository()->findOneBy(array('identifiantAdresseReprise' => $ligne[self::CSV_ID_ANCIENNE_ADRESSE_SOCIETE]));

        }
        if (!$societe) {
            $societe = $this->sm->getRepository()->findOneBy(array('identifiantReprise' => $ligne[self::CSV_ID_SOCIETE]));
            $etablissementPlaceSurEntite = true;
            if (!$societe) {
              $output->writeln(sprintf("<error>La société %s n'existe pas</error>", $ligne[self::CSV_ID_SOCIETE]));
              return;
            }
        }

        $etablissement = $this->em->getRepository()->findOneBy(array('identifiantReprise', $ligne[self::CSV_ID_ADRESSE]));
        if (!$etablissement) {
            $etablissement = new Etablissement();
        }else{
          $output->writeln(sprintf("<error>L'établissement existe déjà ! %s </error>", $ligne[self::CSV_ID_ADRESSE]));
              return;
        }
        $etablissement->setSociete($societe);
        $etablissement->setIdentifiant($societe->getIdentifiant());
        $etablissement->setIdentifiantReprise($ligne[self::CSV_ID_ADRESSE]);

        $etablissement->setNom($ligne[self::CSV_NOM_ETB]);
        $adresse_str = $ligne[self::CSV_ADRESS_1];
        if ($ligne[self::CSV_ADRESS_2]) {
            $adresse_str .= ', ' . $ligne[self::CSV_ADRESS_2];
        }
        if(isset($ligne[self::CSV_CMT]) && trim($ligne[self::CSV_CMT])){
          $etablissement->setCommentaire(str_replace('#', "\n", $ligne[self::CSV_CMT]));
        }
        if($etablissementPlaceSurEntite){
          $com = $etablissement->getCommentaire();
          if($com){ $com.="\n\n";}
          $etablissement->setCommentaire($com."Etablissement placé automatiquement dans cette société, non reliable directement à une adresse de facturation. ");
        }
        //$etablissement->setNom($ligne[self::CSV_RAISON_SOCIALE]);

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
            //echo "lat=$lat lon=$lon enregistré \n";
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
        if(!$societe->getAdresse()->getAdresse() && !$societe->getAdresse()->getCommune() && !$societe->getAdresse()->getCodePostal()){
          $societe->setAdresse($adresse);
        }

        return $etablissement;
    }

}
