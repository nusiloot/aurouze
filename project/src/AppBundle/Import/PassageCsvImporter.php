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
use AppBundle\Document\Compte;
use AppBundle\Document\Etablissement;
use AppBundle\Document\CompteTag;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Manager\CompteManager;
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
    protected $dateMin;

    const CSV_DATE_CREATION = 0;
    const CSV_ETABLISSEMENT_ID = 1;
    const CSV_DATE_PREVISION = 2;
    const CSV_DATE_DEBUT = 3;
    const CSV_DATE_FIN = 4;
    const CSV_DUREE = 5;
    const CSV_TECHNICIEN = 6;
    const CSV_LIBELLE = 7;
    const CSV_DESCRIPTION = 8;
    const CSV_CONTRAT_ID = 9;
    const CSV_EFFECTUE = 10;
    const CSV_PRESTATIONS = 11;
    const CSV_PRODUITS = 12;
    const CSV_STATUT = 13;
    const CSV_OLD_ID = 14;
    const CSV_TYPE_PASSAGE = 15;

    public function __construct(DocumentManager $dm, PassageManager $pm, EtablissementManager $em, CompteManager $um, ContratManager $cm) {
        $this->dm = $dm;
        $this->pm = $pm;
        $this->em = $em;
        $this->um = $um;
        $this->cm = $cm;
        $this->dateMin = \DateTime::createFromFormat('Y-m-d', '2014-01-01');
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
            if (!preg_match('/^[0-9]+$/', $data[self::CSV_ETABLISSEMENT_ID])) {
                $output->writeln(sprintf("\n<error>établissement dont le numéro %s n'est pas correct</error>", $data[self::CSV_ETABLISSEMENT_ID]));
                continue;
            }
            $etablissement = $this->em->getRepository()->findOneByIdentifiantReprise($data[self::CSV_ETABLISSEMENT_ID]);

            if (!$etablissement) {
                $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_CONTRAT_ID]);
                if($contrat){
                  $societe = $contrat->getSociete();
                  $idSociete = $societe->getId();
                  $output->writeln(sprintf("\n<comment>L'établissement %s n'existe pas : on va le créer au sein de la société %s </comment>", $data[self::CSV_ETABLISSEMENT_ID], $idSociete));
                  $etablissement = new Etablissement();
                  $etablissement->setSociete($societe);
                  $etablissement->setIdentifiant($societe->getIdentifiant());
                  $etablissement->setIdentifiantReprise($data[self::CSV_ETABLISSEMENT_ID]);

                  $etablissement->setNom($societe->getRaisonSociale());
                  $etablissement->setAdresse($societe->getAdresse());
                  $this->dm->persist($etablissement);
                  $this->dm->flush();
                }else{
                  $output->writeln(sprintf("\n<error>L'établissement %s n'existe pas et n'est pas créable pas de contrat %s </error>", $data[self::CSV_ETABLISSEMENT_ID], $data[self::CSV_CONTRAT_ID]));
                  continue;
                }
            }

            $passage = new Passage();
            $passage->setEtablissement($etablissement);
            if (!$data[self::CSV_DATE_PREVISION]) {
                $output->writeln(sprintf("<error>Le passage %s ne possède aucune date de prévision!</error>", $data[self::CSV_OLD_ID]));
                continue;
            }
            $passage->setDatePrevision(new \DateTime($data[self::CSV_DATE_PREVISION]));

            $passage->setIdentifiantReprise($data[self::CSV_OLD_ID]);
            $passage->setNumeroArchive($data[self::CSV_OLD_ID]);

            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_CONTRAT_ID]);
            if (!$contrat) {
                //$output->writeln(sprintf("<error>Le contrat %s n'existe pas</error>", $data[self::CSV_CONTRAT_ID]));
                continue;
            }

            $this->dm->persist($passage);
            $doublonPassage = $this->pm->getRepository()->findOneById($passage->getId());
            if ($doublonPassage) {

                $output->writeln(sprintf("<comment>Le passage d'id %s existe déjà en base (%s)!</comment>", $passage->getId(), $data[self::CSV_OLD_ID]));
            }
            $resultStatut = $this->generateStatut($data, $passage, $output);
            $passage->updateStatut();
            if (!$resultStatut) {
                $output->writeln(sprintf("<error>Aucun statut déterminable pour le passage d'id %s (%s)!</error>", $passage->getId(), $data[self::CSV_OLD_ID]));
                continue;
            }

            $passage->setLibelle($data[self::CSV_LIBELLE]);
            $passage->setTypePassage($data[self::CSV_TYPE_PASSAGE]);
            $passage->setDescription(str_replace('#', "\n", $data[self::CSV_DESCRIPTION]));
            if (!preg_match('/^[0-9]+$/', $data[self::CSV_CONTRAT_ID])) {
                $output->writeln(sprintf("<error>Passage dont le numéro %s n'est pas correct</error>", $data[self::CSV_CONTRAT_ID]));
                continue;
            }

            if ($data[self::CSV_PRESTATIONS]) {
                $prestations = explode('#', $data[self::CSV_PRESTATIONS]);
                foreach ($prestations as $prestationNom) {
                    if (trim($prestationNom) != "") {
                        $prestationIdentifiant = strtoupper(Transliterator::urlize($prestationNom));
                        if (!array_key_exists($prestationIdentifiant, $prestationsType)) {
                            $output->writeln(sprintf("<comment>La prestation : %s n'existe pas dans la configuration </comment>", $prestationIdentifiant));
                            continue;
                        }
                        $prestation = $prestationsType[$prestationIdentifiant];
                        $passage->addPrestation($prestation);
                        $this->dm->persist($passage);
                    }
                }
            } else {
                $output->writeln(sprintf("<comment>Le passage : %s n'a aucune presta </comment>", $data[self::CSV_OLD_ID]));
            }

            $identifiantRepriseTechnicien = $data[self::CSV_TECHNICIEN];
            if (!is_null($identifiantRepriseTechnicien)) {
                $compte = $this->um->getRepository()->findOneByIdentifiantReprise($identifiantRepriseTechnicien);

                if (!is_null($compte)) {
                    $passage->addTechnicien($compte);
                }
            }

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
                        $this->dm->persist($passage);
                    }
                }
            }
            $contrat->addEtablissement($etablissement);
            $contrat->addPassage($etablissement, $passage);

            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 2000) {
                $this->dm->flush();
                $this->dm->clear();
                gc_collect_cycles();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();

    }

    public function generateStatut($data, &$passage, $output) {
        switch ($data[self::CSV_STATUT]) {
            case PassageManager::STATUT_REALISE: {
                    return $this->updateStatutRealise($data, $passage, $output);
                    break;
                }
            case PassageManager::STATUT_PLANIFIE: {
                    return $this->updateStatutPlanifie($data, $passage, $output);
                    break;
                }
            case PassageManager::STATUT_A_PLANIFIER: {
                    return $this->updateStatutEnAttente($data, $passage, $output);
                    break;
                }
            case "EN_ATTENTE": {
                        return $this->updateStatutEnAttente($data, $passage, $output);
                        break;
            }
            default:
                return false;
                break;
        }
    }

    public function updateStatutPlanifie($data, &$passage, $output) {
        $passage = $this->updateDateDebutDateFin($data, $passage, $output);
        return $passage;
    }

    public function updateStatutEnAttente($data, &$passage, $output) {
// DO NOTHING;
        return $passage;
    }

    public function updateStatutRealise($data, &$passage, $output) {
        $passage = $this->updateDateDebutDateFin($data, $passage, $output);
        $passage->setDateRealise($passage->getDateDebut());

        return $passage;
    }

    public function updateDateDebutDateFin($data, &$passage, $output) {
        if (!$data[self::CSV_DUREE]) {
            $output->writeln(sprintf("<error>La durée du passage n'a pas été renseigné : %s</error>", $passage->getId()));
        }
        if ($data[self::CSV_DATE_DEBUT]) {
            $passage->setDateDebut(new \DateTime($data[self::CSV_DATE_DEBUT]));
            $minutes = $passage->getDateDebut()->format('i');
            $heures = $passage->getDateDebut()->format('H');
            if ($heures . $minutes == "0000") {
                $passage->setDateDebut(\DateTime::createFromFormat('Y-m-d H:i', $passage->getDateDebut()->format('Y-m-d') . ' 12:00'));
            }

            if ($passage->getDateDebut() < $this->dateMin) {
                $dateDebut = $passage->getDatePrevision()->format('Y-m-d');
                $passage->setDateDebut(\DateTime::createFromFormat('Y-m-d H:i', $dateDebut . ' ' . $heures . ':' . $minutes));
            }
        } else {
            $output->writeln(sprintf("<comment>Le passage d'id %s n'a pas de date de début et est %s (%s)!</comment>", $passage->getId(), $data[self::CSV_STATUT], $data[self::CSV_OLD_ID]));
            $passage->setDateDebut($passage->getDatePrevision());
            $minutes = $passage->getDateDebut()->format('i');
            $heures = $passage->getDateDebut()->format('H');
            if ($heures . $minutes == "0000") {
                $passage->setDateDebut(\DateTime::createFromFormat('Y-m-d H:i', $passage->getDateDebut()->format('Y-m-d') . ' 12:00'));
            }
        }
        if ($data[self::CSV_DUREE]) {
            $dateFin = clone $passage->getDateDebut();
            $passage->setDateFin($dateFin);
            $passage->getDateFin()->modify("+ " . $data[self::CSV_DUREE] . " minutes");
        }
        return $passage;
    }


}
