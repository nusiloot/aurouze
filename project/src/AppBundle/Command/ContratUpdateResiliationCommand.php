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

class ContratUpdateResiliationCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:contrat-update-resiliation')
                ->setDescription('Contrat update resiliation');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        echo "\nMis à jour des contrats résilié...\n";
        $this->updateContratsEnResiliation($output);
    }

    public function updateContratsEnResiliation($output) {

        $allContratsByNumero = $this->dm->getRepository('AppBundle:Contrat')->findAllSortedByNumeroArchive();

        $contratsByNumero = array();
        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContratsByNumero as $contrat) {
            $numArchive = $contrat->getNumeroArchive();
            if (!array_key_exists($numArchive, $contratsByNumero)) {
                $contratsByNumero[$numArchive] = new \stdClass();
                $contratsByNumero[$numArchive]->contrats = array();
                $contratsByNumero[$numArchive]->resiliation = false;
            }
            $dDebut = ($contrat->getDateDebut())? $contrat->getDateDebut()->format('Ymd') : $contrat->getDateCreation()->format('Ymd');
            $contratsByNumero[$numArchive]->contrats[$dDebut] = $contrat;
            if ($contrat->isResilie()) {
                $contratsByNumero[$contrat->getNumeroArchive()]->resiliation = true;
            }
        }
        foreach ($contratsByNumero as $numeroArchive => $contratsStruct) {

            ksort($contratsStruct->contrats);
            $nbContrats = count($contratsStruct->contrats);
            $numContrat = 1;
            foreach ($contratsStruct->contrats as $contrat) {
                $hasAnnules = false;
                foreach ($contrat->getContratPassages() as $contratPassages) {
                    foreach ($contratPassages->getPassagesSorted() as $idP => $passage) {
                        if ($passage->isAnnule()) {
                            $hasAnnules = true;
                        }
                    }
                }
                if ($numContrat == $nbContrats) {
                    if ($contratsStruct->resiliation) {
                        if (!$hasAnnules) {
                            $output->writeln(sprintf("\n<comment>CONTRAT : %s ne possède pas de passages Annulé et est résilié</comment>", $contrat->getId()));
                        }
                        $output->writeln(sprintf("\n<comment>%s => fini et annulé</comment>", $contrat->getId()));
                        $contrat->setTypeContrat(ContratManager::TYPE_CONTRAT_ANNULE);
                        $contrat->setStatut(ContratManager::STATUT_FINI);
                    }
                    $contrat->setReconduit(false);
                } elseif ($numContrat < $nbContrats) {
                    if ($contratsStruct->resiliation) {
                        if ($hasAnnules) {
                            $output->writeln(sprintf("\n<comment>CONTRAT : %s a de passages Annulés et semble FINI?</comment>", $contrat->getId()));
                        }
                        $output->writeln(sprintf("\n<comment>%s => fini</comment>", $contrat->getId()));
                        $contrat->setStatut(ContratManager::STATUT_FINI);
                    }
                    $contrat->setReconduit(true);
                }
                $numContrat++;
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



}
