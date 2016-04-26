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
use AppBundle\Document\Contrat;
use AppBundle\Document\CompteInfos;
use AppBundle\Document\Produit;
use AppBundle\Document\Compte;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\ContratManager;
use AppBundle\Manager\PassageManager;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Manager\CompteManager;
use AppBundle\Manager\SocieteManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Behat\Transliterator\Transliterator;
use AppBundle\Import\CsvFile;
use Symfony\Component\Console\Helper\ProgressBar;

class ContratCsvImporter {

    protected $dm;
    protected $cm;
    protected $pm;
    protected $em;
    protected $sm;
    protected $um;

    const CSV_ID_CONTRAT = 0;
    const CSV_ID_SOCIETE = 1;
    const CSV_ID_COMMERCIAL = 2;
    const CSV_ID_TECHNICIEN = 3;
    const CSV_TYPE_CONTRAT = 4;
    const CSV_TYPE_PRESTATION = 5;
    const CSV_NOMENCLATURE = 6;
    const CSV_DATE_CREATION = 7;
    const CSV_DATE_DEBUT = 8;
    const CSV_DUREE = 9;
    const CSV_GARANTIE = 10;
    const CSV_PRIXHT = 11;
    const CSV_ARCHIVAGE = 12;
    const CSV_TVA_REDUITE = 13;
    const CSV_DATE_RESILIATION = 14;
    const CSV_PRODUITS = 15;
    const CSV_NOM_COMMERCIAL = 16;
    const CSV_NOM_TECHNICIEN = 17;

    public function __construct(DocumentManager $dm, ContratManager $cm, PassageManager $pm, EtablissementManager $em, SocieteManager $sm, CompteManager $um) {
        $this->dm = $dm;
        $this->cm = $cm;
        $this->pm = $pm;
        $this->em = $em;
        $this->sm = $sm;
        $this->um = $um;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $progress = new ProgressBar($output, 100);
        $progress->start();

        $csv = $csvFile->getCsv();
        $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
        $produitsArray = $configuration->getProduitsArray();

        $comptesArray = $this->um->getRepository()->findAllInArray();

        $i = 0;
        $cptTotal = 0;
        foreach ($csv as $data) {
            $societe = $this->sm->getRepository()->findOneBy(array('identifiantReprise' => $data[self::CSV_ID_SOCIETE]));

            if (!$societe) {
                $output->writeln(sprintf("<error>La societe %s n'existe pas</error>", $data[self::CSV_ID_SOCIETE]));
                continue;
            }

            $contrat = $this->cm->getRepository()->findOneBy(array('identifiantReprise', $data[self::CSV_ID_CONTRAT]));

            if (!$contrat) {
                $contrat = new Contrat();
            } else {
                $output->writeln(sprintf("<error>Le contrat : %s existe déjà en base?</error>", $data[self::CSV_ID_CONTRAT]));
            }

            $contrat->setDateCreation(new \DateTime($data[self::CSV_DATE_CREATION]));
            $contrat->setSociete($societe);
            $contrat->setTvaReduite(boolval($data[self::CSV_TVA_REDUITE]));
            $type_contrat = ContratManager::$types_contrat_import_index[$data[self::CSV_TYPE_CONTRAT]];
            $contrat->setTypeContrat($type_contrat);
            
            if ($data[self::CSV_DATE_DEBUT]) {
                $contrat->setDateDebut(new \DateTime($data[self::CSV_DATE_DEBUT]));
            }

            if (!preg_match("/^[0-9+]+$/", $data[self::CSV_DUREE])) {
                $output->writeln(sprintf("<error>La durée du contrat %s n'est pas correct : %s</error>", $data[self::CSV_ID_CONTRAT], $data[self::CSV_DUREE]));
                continue;
            }

            $contrat->setDuree($data[self::CSV_DUREE]);
            $contrat->setDureeGarantie($data[self::CSV_GARANTIE]);
            $contrat->setDateDebut(new \DateTime($data[self::CSV_DATE_DEBUT]));
            $dateFin = clone $contrat->getDateDebut();
            $dateFin->modify("+ " . $contrat->getDuree() . " month");
            $contrat->setDateFin($dateFin);
            $contrat->setNomenclature(str_replace('#', "\n", $data[self::CSV_NOMENCLATURE]));
            $contrat->setPrixHt($data[self::CSV_PRIXHT]);
            $contrat->setIdentifiantReprise($data[self::CSV_ID_CONTRAT]);
            $contrat->setNumeroArchive($data[self::CSV_ARCHIVAGE]);

            if ($data[self::CSV_NOM_COMMERCIAL]) {
                $identifiantCommercial = strtoupper(Transliterator::urlize($data[self::CSV_NOM_COMMERCIAL]));
                if (array_key_exists($identifiantCommercial, $comptesArray)) {
                    $commercial = $comptesArray[$identifiantCommercial];
                    $contrat->setCommercial($commercial);
                }
            }

            if ($data[self::CSV_NOM_TECHNICIEN]) {
                $identifiantTechnicien = strtoupper(Transliterator::urlize($data[self::CSV_NOM_TECHNICIEN]));
                if (array_key_exists($identifiantTechnicien, $comptesArray)) {
                    $technicien = $comptesArray[$identifiantTechnicien];
                    $contrat->setTechnicien($technicien);
                }
            }

            if ($data[self::CSV_DATE_RESILIATION]) {
                $contrat->setDateResiliation(new \DateTime($data[self::CSV_DATE_RESILIATION]));
                $contrat->setStatut(ContratManager::STATUT_RESILIE);
            }

            $produits = explode('#', $data[self::CSV_PRODUITS]);
            foreach ($produits as $produitStr) {
                if ($produitStr) {
                    $produitdetail = explode('~', $produitStr);
                    $produitQte = 0;
                    $produitLib = $produitdetail[0];
                    if (count($produitdetail) > 1) {
                        $produitQte = $produitdetail[1];
                    }
                    if ($produitLib) {
                        $produitToAdd = clone $produitsArray[strtoupper(Transliterator::urlize($produitLib))];
                        $produitToAdd->setNbTotalContrat(0);
                        $produitToAdd->setNbTotalContrat($produitQte);
                        $contrat->addProduit($produitToAdd);
                    }
                }
            }
            $this->dm->persist($contrat);
            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }

            if ($i >= 10000) {
                $this->dm->flush();
                $this->dm->clear();
                gc_collect_cycles();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();
    }

}
