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
use AppBundle\Document\User;
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

    public function __construct(DocumentManager $dm, PassageManager $pm, EtablissementManager $em, UserManager $um, ContratManager $cm) {
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
        $userInconnu = $this->um->getRepository()->findOneByIdentifiant(User::USER_INCONNU);

        foreach ($csv as $data) {
            
            if ($data[self::CSV_ETABLISSEMENT_ID] == "000000") {
                continue;
            }
            if (!preg_match('/^[0-9]+$/', $data[self::CSV_ETABLISSEMENT_ID])) {
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
            if (!$data[self::CSV_DATE_PREVISION]) {
                $output->writeln(sprintf("<error>Le passage %s ne possède aucune date de prévision!</error>", $data[self::CSV_OLD_ID]));
                continue;
            }

            $passage->setDatePrevision(new \DateTime($data[self::CSV_DATE_PREVISION]));

            $passage->setNumeroPassageIdentifiant("001");
            $passage->generateId();
            $passage->setIdentifiantReprise($data[self::CSV_OLD_ID]);

            $doublonPassage = $this->pm->getRepository()->findOneById($passage->getId());
            if ($doublonPassage) {
                $output->writeln(sprintf("<error>Le passage d'id %s existe déjà en base (%s)!</error>", $passage->getId(), $data[self::CSV_OLD_ID]));
                continue;
            }
            $resultStatut = $this->generateStatut($data, $passage,$output);
            if (!$resultStatut) {
                $output->writeln(sprintf("<error>Aucun statut déterminable pour le passage d'id %s (%s)!</error>", $passage->getId(), $data[self::CSV_OLD_ID]));
                continue;
            }

            $passage->setLibelle($data[self::CSV_LIBELLE]);
            $passage->setDescription(str_replace('\n', "\n", $data[self::CSV_DESCRIPTION]));
            if (!preg_match('/^[0-9]+$/', $data[self::CSV_CONTRAT_ID])) {
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
                        $prestation = $prestationsType[$prestationIdentifiant];                        
                        $passage->addPrestation($prestation);
                        $this->dm->persist($passage);
                    }
                }
            }

            $prenomNomTechnicien = trim($data[self::CSV_TECHNICIEN]);
            if ($prenomNomTechnicien) 
                {
                $nomTechnicien = substr(strrchr($prenomNomTechnicien, " "), 1);
                $prenomTechnicien = trim(str_replace($nomTechnicien, '', $prenomNomTechnicien));
                $identifiantTechnicien = strtoupper(Transliterator::urlize($prenomTechnicien . ' ' . $nomTechnicien));

                $user = $this->um->getRepository()->findOneByIdentifiant($identifiantTechnicien);
                if ($user) {
                    $passage->addTechnicien($user);
                    $this->dm->persist($passage);
                } else {
                    $passage->addTechnicien($userInconnu);
                    $this->dm->persist($passage);
                }
            }
            else {
                $passage->addTechnicien($userInconnu);
                $this->dm->persist($passage);
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
            $this->dm->persist($contrat);
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
        $this->updatePassagesAttentes($output);
    }

    public function updateContrats($output) {
        echo "\nMis à jour des contrats...\n";
        $allContrat = $this->cm->getRepository()->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContrat as $contrat) {
            $hasTechnicien = count($contrat->getTechnicien());
            $prestationsArr = array();
            $technicienArr = array();
            $technicienForContrat = null;
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
                    if (count($passage->getTechniciens())) {
                        foreach ($passage->getTechniciens() as $technicien) {
                            if (array_key_exists($technicien->getIdentifiant(), $technicienArr)) {
                                $technicienArr[$technicien->getIdentifiant()] ++;
                                if (max($technicienArr) == $technicienArr[$technicien->getIdentifiant()]) {
                                    $technicienForContrat = $technicien;
                                }
                            } else {
                                $technicienArr[$technicien->getIdentifiant()] = 0;
                            }
                        }
                    }
                }
                break;
            }
            $contratFini = true;
            foreach ($contrat->getContratPassages() as $contratPassages) {
                foreach ($contratPassages->getPassages() as $passage) {
                    if (!$passage->isRealise()) {
                        $contratFini = false;
                        break;
                    }
                }
            }
            if ($contratFini && count($contrat->getContratPassages())) {
                $contrat->setStatut(ContratManager::STATUT_FINI);
            } else {
                $contrat->setStatut(ContratManager::STATUT_VALIDE);
            }

            foreach ($prestationsArr as $prestation) {
                $contrat->addPrestation($prestation);
                $this->dm->persist($contrat);
            }
            $this->dm->persist($contrat);
            if (!$hasTechnicien && $technicienForContrat) {
                $contrat->setTechnicien($technicienForContrat);
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
        $this->dm->flush();
        $progress->finish();
    }

    public function updatePassagesAttentes($output) {
        echo "\nMis à jour des passages en attente...\n";
        $allPassagesAttente = $this->pm->getRepository()->findByStatut(PassageManager::STATUT_EN_ATTENTE);
       
        
        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allPassagesAttente as $passage) {
            if ($this->pm->isFirstPassageNonRealise($passage)) {
                $passage->setDateDebut($passage->getDatePrevision());
            }

            $this->dm->persist($passage);
            $cptTotal++;
            if ($cptTotal % (count($allPassagesAttente) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 2000) {
                $this->dm->flush();
                $i = 0;
            }
            $i++;
        }
        $this->dm->flush();
        $progress->finish();
    }

    public function generateStatut($data, &$passage,$output) {

        switch ($data[self::CSV_STATUT]) {
            case PassageManager::STATUT_REALISE: {
                    return $this->updateStatutRealise($data, $passage,$output);
                    break;
                }
            case PassageManager::STATUT_PLANIFIE: {
                    return $this->updateStatutPlanifie($data, $passage,$output);
                    break;
                }
            case PassageManager::STATUT_EN_ATTENTE: {
                    return $this->updateStatutEnAttente($data, $passage,$output);
                    break;
                }
            default:
                return false;
                break;
        }
    }

    public function updateStatutPlanifie($data, &$passage,$output) {
        $passage = $this->updateDateDebutDateFin($data, $passage,$output);
        return $passage;
    }

    public function updateStatutEnAttente($data, &$passage,$output) {
        // DO NOTHING;
        return $passage;
    }

    public function updateStatutRealise($data, &$passage,$output) {
        $passage = $this->updateDateDebutDateFin($data, $passage,$output);
        $passage->setDateRealise($passage->getDateDebut());
        return $passage;
    }

    public function updateDateDebutDateFin($data, &$passage,$output) {
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
            $output->writeln(sprintf("<error>Le passage d'id %s n'a pas de date de début et est %s (%s)!</error>", $passage->getId(), $data[self::CSV_STATUT], $data[self::CSV_OLD_ID]));
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
