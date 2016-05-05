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

class ContratUpdatePrestationCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:contrat-update-prestation')
                ->setDescription('Contrat update prestation');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        echo "\nMis à jour des passages n'ayant aucune prestations...\n";
        $this->updatePassagesPrestations($output);

        echo "\nMis à jour du nombre de prestation des contrats...\n";
        $this->updateContratsPrestationsNombre($output);
    }

    public function updatePassagesPrestations($output) {

        $allPassages = $this->dm->getRepository('AppBundle:Passage')->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();

        foreach ($allPassages as $passage) {
            if (!count($passage->getPrestations())) {
                $contratPassages = $passage->getContrat()->getPassagesEtablissementNode($passage->getEtablissement())->getPassagesSorted(true);
                $founded = false;
                $previousPassage = null;
                foreach ($contratPassages as $passageId => $cPassage) {
                    if ($founded) {
                        $previousPassage = $cPassage;
                        break;
                    }
                    if ($passageId == $cPassage->getId()) {
                        $founded = true;
                    }
                }
                if ($previousPassage && count($previousPassage->getPrestations())) {
                    foreach ($previousPassage->getPrestations() as $previousPresta) {
                        $prestation = clone $previousPresta;
                        $passage->addPrestation($prestation);
                    }
                } else {
                    if (count($passage->getContrat()->getPrestations())) {
                        foreach ($passage->getContrat()->getPrestations() as $contratPresta) {
                            $prestation = clone $contratPresta;
                            $passage->addPrestation($prestation);
                        }
                    }
                }
            }
            $cptTotal++;
            if ($cptTotal % (count($allPassages) / 100) == 0) {
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

    public function updateContratsPrestationsNombre($output) {
        $allContrats = $this->dm->getRepository('AppBundle:Contrat')->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allContrats as $contrat) {
            $contratPrestationsArr = array();
            $etbReference = null;
            foreach ($contrat->getContratPassages() as $contratPassages) {
                $etbReference = $contratPassages->getEtablissement();
                foreach ($contratPassages->getPassages() as $passage) {
                    foreach ($passage->getPrestations() as $prestation) {
                        if ($passage->isSousContrat()) {
                            if (!array_key_exists($prestation->getIdentifiant(), $contratPrestationsArr)) {
                                $contratPrestationsArr[$prestation->getIdentifiant()] = 0;
                            }
                            $contratPrestationsArr[$prestation->getIdentifiant()] = $contratPrestationsArr[$prestation->getIdentifiant()] + 1;
                        }
                    }
                }
                break;
            }
            if (count($contrat->getContratPassages()) > 1) {
                $output->writeln(sprintf("\n<comment>ATTENTION LE CONTRAT : %s à plusieurs établissement. Ses prestation seront uniquement calculé avec l'etb %s !</comment>", $contrat->getId(), $etbReference->getId()));
            }

            foreach ($contrat->getPrestations() as $prestationContrat) {
                if (!array_key_exists($prestationContrat->getIdentifiant(), $contratPrestationsArr)) {
                    continue;
                }
                $prestationContrat->setNbPassages($contratPrestationsArr[$prestationContrat->getIdentifiant()]);
            }

            $cptTotal++;
            if ($cptTotal % (count($allContrats) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 1000) {
                $this->dm->flush();
                $i = 0;
            }
            $i++;
           }
        $this->dm->flush();
        $progress->finish();
    }

}
