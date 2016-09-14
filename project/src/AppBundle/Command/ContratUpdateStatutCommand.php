<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportEtablissement
 *
 * @author mathurin
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use Symfony\Component\Console\Helper\ProgressBar;
use AppBundle\Manager\ContratManager;

class ContratUpdateStatutCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:contrat-update-statut')
                ->setDescription('Contrat update statut');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        echo "\nMis à jour des contrats non acceptés...\n";
        $this->updateContratsNonAcceptes($output);

        echo "\nMis à jour des contrats résiliés...\n";
        $this->updateContratsResilies($output);

        echo "\nMis à jour des passages en attente...\n";
        $this->updatePassagesAPlanifier($output);

        echo "\nMis à jour des contrats finis...\n";
        $this->updateContratsFinis($output);
    }

    public function updateContratsNonAcceptes($output) {
        $allContratsNonAcceptes = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContratsNonAcceptes as $contrat) {
            foreach ($contrat->getContratPassages() as $contratPassages) {
                foreach ($contratPassages->getPassages() as $passage) {
                    $this->dm->getRepository('AppBundle:Passage')->createQueryBuilder('Passage')
                            ->remove()
                            ->field('id')
                            ->equals($passage->getId())
                            ->getQuery()
                            ->execute();
                }
                $contrat->removeContratPassage($contratPassages);
            }
            $cptTotal++;
            if ($cptTotal % (count($allContratsNonAcceptes) / 100) == 0) {
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

    public function updateContratsResilies($output) {
        $allContratsResilies = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_RESILIE);

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContratsResilies as $contrat) {
            foreach ($contrat->getContratPassages() as $contratPassages) {
                foreach ($contratPassages->getPassages() as $passage) {
                    if (!$this->dm->getRepository('AppBundle:Passage')->findById($passage->getId())) {
                        $output->writeln(sprintf("<comment>Le passage d'id %s semble Introuvable dans la base pourtant référencé par le contrat  %s !</comment>", $passage->getId(), $contrat->getId()));
                        continue;
                    }
                    if ($passage->getDatePrevision()->format('YmdHi') > $contrat->getDateResiliation()->format('YmdHi')) {
                        if ($passage->isEnAttente()) {
                            $passage->setStatut(PassageManager::STATUT_ANNULE);
                        } else {
                            $output->writeln(sprintf("<comment>ANNULATION DE PASSAGE : %s ne semble pas 'en attente' (statut='%s') => contrat  %s !</comment>", $passage->getId(),$passage->getStatut(), $contrat->getId()));
                        }
                    }
                }
            }
            $cptTotal++;
            if ($cptTotal % (count($allContratsResilies) / 100) == 0) {
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

    public function updatePassagesAPlanifier($output) {

        $allContratsByNumero = $this->dm->getRepository('AppBundle:Contrat')->findAllSortedByNumeroArchive();

        $contratsByNumero = array();
        foreach ($allContratsByNumero as $contrat) {
            if (!array_key_exists($contrat->getNumeroArchive(), $contratsByNumero)) {
                $contratsByNumero[$contrat->getNumeroArchive()] = array();
            }
            $contratsByNumero[$contrat->getNumeroArchive()][] = $contrat;
        }


        $passagesByNumero = array();
        foreach ($contratsByNumero as $numeroArchive => $contrats) {
            foreach ($contrats as $contrat) {
                if (!array_key_exists($numeroArchive, $passagesByNumero)) {
                    $passagesByNumero[$numeroArchive] = array();
                }
                foreach ($contrat->getContratPassages() as $contratPassages) {
                    $idEtb = $contratPassages->getEtablissement()->getId();
                    if (!array_key_exists($idEtb, $passagesByNumero[$numeroArchive])) {
                        $passagesByNumero[$contrat->getNumeroArchive()][$idEtb] = array();
                    }
                    foreach ($contratPassages->getPassages() as $passage) {
                        $passagesByNumero[$numeroArchive][$idEtb][$passage->getId()] = $passage;
                    }
                    ksort($passagesByNumero[$numeroArchive][$idEtb]);
                }
            }
        }
        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();

        foreach ($passagesByNumero as $numeroArchive => $passagesEtb) {
            foreach ($passagesEtb as $idEtb => $passages) {
                $founded = false;
                $lastPassage = null;
                $current_statut = null;
                foreach ($passages as $idP => $passage) {
                    if ($founded && !$passage->isRealise() && !$passage->isPlanifie()) {
                        break;
                    } else {
                        $founded = false;
                        if ($lastPassage) {
                            $lastPassage->setDateRealise($lastPassage->getDateDebut());
                        }
                    }

                    if ($passage->isEnAttente() &&
                            (is_null($current_statut) || $current_statut == PassageManager::STATUT_PLANIFIE || $current_statut == PassageManager::STATUT_REALISE)) {
                        $passage->setDateDebut($passage->getDatePrevision());

                        $this->dm->persist($passage);
                        $lastPassage = $passage;
                        $founded = true;
                    }
                    $lastPassage = $passage;
                    $current_statut = $passage->getStatut();
                }
            }
            $cptTotal++;
            if ($cptTotal % (count($contratsByNumero) / 100) == 0) {
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


    public function updateContratsFinis($output) {
        $allContratsEnCours = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_EN_COURS);

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContratsEnCours as $contrat) {

            $contrat->verifyAndClose();

            $cptTotal++;
            if ($cptTotal % (count($allContratsEnCours) / 100) == 0) {
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

}
