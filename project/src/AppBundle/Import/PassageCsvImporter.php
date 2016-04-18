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
use AppBundle\Document\Passage;
use AppBundle\Document\Contrat;
use AppBundle\Document\ContratPassages;
use AppBundle\Document\Prestation;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\ContratManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Import\CsvFile;
use Behat\Transliterator\Transliterator;
use Symfony\Component\Console\Helper\ProgressBar;

class PassageCsvImporter {

    protected $dm;
    protected $pm;
    protected $em;
    protected $um;
    protected $cm;

    const CSV_DATE_CREATION = 0;
    const CSV_ETABLISSEMENT_ID = 1;
    const CSV_DATE_DEBUT = 2;
    const CSV_DATE_FIN = 3;
    const CSV_DUREE = 4;
    const CSV_TECHNICIEN = 5;
    const CSV_LIBELLE = 6;
    const CSV_DESCRIPTION = 7;
    const CSV_CONTRAT_ID = 8;
    const CSV_PRESTATIONS = 9;
    const CSV_PRODUITS = 10;

    public function __construct(DocumentManager $dm, PassageManager $pm, EtablissementManager $em, UserManager $um, ContratManager $cm) {
        $this->dm = $dm;
        $this->pm = $pm;
        $this->em = $em;
        $this->um = $um;
        $this->cm = $cm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();

        $i = 0;
        $cptTotal = 0;

        $progress = new ProgressBar($output, 100);
        $progress->start();
        
        $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
        $produitsArray = $configuration->getProduitsArray();
        
        $prestationsType = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestationsArray();

        foreach ($csv as $data) {
          
            if ($data[self::CSV_ETABLISSEMENT_ID] == "000000") {
                continue;
            }
            if(!preg_match('/^[0-9]+$/', $data[self::CSV_ETABLISSEMENT_ID])){
                $output->writeln(sprintf("<error>établissement dont le numéro %s n'est pas correct</error>", $data[self::CSV_ETABLISSEMENT_ID]));
                continue;
            }
            $etablissement = $this->em->getRepository()->findOneByIdentifiantReprise($data[self::CSV_ETABLISSEMENT_ID]);

            if (!$etablissement) {
                $output->writeln(sprintf("<error>L'établissement %s n'existe pas</error>", $data[self::CSV_ETABLISSEMENT_ID]));
                continue;
            }
            $passage = new Passage();
            $passage->setEtablissement($etablissement);
            $passage->setDatePrevision(new \DateTime($data[self::CSV_DATE_CREATION]));
            $passage->setNumeroPassageIdentifiant("001");
            $passage->generateId();

            if ($data[self::CSV_DATE_DEBUT]) {
                $passage->setDateDebut(new \DateTime($data[self::CSV_DATE_DEBUT]));
            } else {
                $passage->setDateDebut(clone $passage->getDatePrevision());
            }

            if (!$data[self::CSV_DUREE]) {
                $output->writeln(sprintf("<error>La durée du passage n'a pas été renseigné : %s</error>", $passage->getId()));
                continue;
            }
            if ($data[self::CSV_DATE_DEBUT]) {
                $passage->setDateFin(clone $passage->getDateDebut());
                $passage->getDateFin()->modify(sprintf("+%s minutes", $data[self::CSV_DUREE]));
            }
            $passage->setLibelle($data[self::CSV_LIBELLE]);
            $passage->setDescription(str_replace('\n', "\n", $data[self::CSV_DESCRIPTION]));
            if(!preg_match('/^[0-9]+$/', $data[self::CSV_CONTRAT_ID])){
                $output->writeln(sprintf("<error>Passage dont le numéro %s n'est pas correct</error>", $data[self::CSV_CONTRAT_ID]));
                continue;
            }
            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_CONTRAT_ID]);
            if (!$contrat) {
                $output->writeln(sprintf("<error>Le contrat %s n'existe pas</error>", $data[self::CSV_CONTRAT_ID]));
                continue;
            }
            if ($data[self::CSV_PRESTATIONS]) {
                $prestations = explode('#', $data[self::CSV_PRESTATIONS]);
                foreach ($prestations as $prestationNom) {
                    if (trim($prestationNom) != "") {
                        $prestationIdentifiant = strtoupper(Transliterator::urlize($prestationNom));
                        if (!array_key_exists($prestationIdentifiant, $prestationsType)) {
                            $output->writeln(sprintf("<error>La prestation : %s n'existe pas dans la configuration </error>", $prestationIdentifiant));
                        }
                        $prestation = clone $prestationsType[$prestationIdentifiant];
                        $passage->addPrestation($prestation);
                    }
                }
            }

            $prenomNomTechnicien = trim($data[self::CSV_TECHNICIEN]);

            $nomTechnicien = substr(strrchr($prenomNomTechnicien, " "), 1);
            $prenomTechnicien = trim(str_replace($nomTechnicien, '', $prenomNomTechnicien));
            $identifiantTechnicien = strtoupper(Transliterator::urlize($prenomTechnicien . ' ' . $nomTechnicien));

            $user = $this->um->getRepository()->findOneByIdentifiant($identifiantTechnicien);
            
            $passage->addTechnicien($user);


            $passage->setContrat($contrat);
            $passage->setNumeroContratArchive($contrat->getNumeroArchive());
            
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
                        $produitToAdd->setNbUtilisePassage(0);
                        $produitToAdd->setNbTotalContrat(null);
                        $produitToAdd->setNbUtilisePassage($produitQte);                        
                        $passage->addProduit($produitToAdd);
                    }
                }
            }
            
            $contrat->addPassage($etablissement, $passage);

            $this->dm->persist($contrat);
            $this->dm->persist($passage);

            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 2000) {
                $this->dm->flush();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();
        $this->updateContrats($output);
    }

    public function updateContrats($output) {
        echo "\nMis à jour des contrats...\n";
        $allContrat = $this->cm->getRepository()->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContrat as $contrat) {

            $prestationsArr = array();
            foreach ($contrat->getContratPassages() as $contratPassages) {
                foreach ($contratPassages->getPassages() as $passage) {
                    foreach ($passage->getPrestations() as $prestation) {
                        if (array_key_exists($prestation->getIdentifiant(), $prestationsArr)) {
                            $prestationsArr[$prestation->getIdentifiant()]->setNbPassages($prestationsArr[$prestation->getIdentifiant()]->getNbPassages());
                        } else {
                            $prestation->setNbPassages(1);
                            $prestationsArr[$prestation->getIdentifiant()] = $prestation;
                        }
                    }
                }
                break;
            }
            foreach ($prestationsArr as $prestation) {
                $contrat->addPrestation($prestation);
            }

            $this->dm->persist($contrat);
            $cptTotal++;
            if ($cptTotal % (count($allContrat) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 2000) {
                $this->dm->flush();
                $i = 0;
            }
            $i++;
        }
        $progress->finish();
    }

}
