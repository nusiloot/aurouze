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

class PassageUpdateTechnicienCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:passages-update-technicien')
                ->setDescription('Passages update technicien');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');


        $this->updatePassagesTechniciens($output, PassageManager::STATUT_A_PLANIFIER);
        $this->updatePassagesTechniciens($output, PassageManager::STATUT_PLANIFIE);
        $this->updatePassagesTechniciens($output, PassageManager::STATUT_REALISE);
    }

    public function updatePassagesTechniciens($output, $statut) {
        echo "\nMis à jour des techniciens non renseignés pour les passage " . $statut . "...\n";
        $allPassagesAPlanifier = $this->dm->getRepository('AppBundle:Passage')->findByStatut($statut);

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allPassagesAPlanifier as $passage) {
            if (!count($passage->getTechniciens())) {
                $contrat = $passage->getContrat();
                $etablissement = $passage->getEtablissement();
                $technicienArr = array();
                foreach ($contrat->getContratPassages() as $contratPassages) {
                    if ($contratPassages->getEtablissement()->getId() == $etablissement->getId()) {
                        foreach ($contratPassages->getPassages() as $passageForEtb) {
                            if (count($passageForEtb->getTechniciens())) {
                                foreach ($passageForEtb->getTechniciens() as $technicien) {
                                    if (array_key_exists($technicien->getId(), $technicienArr)) {
                                        $technicienArr[$technicien->getId()] = $technicienArr[$technicien->getId()] + 1;
                                    } else {
                                        $technicienArr[$technicien->getId()] = 1;
                                    }
                                }
                            }
                        }
                    }
                }
                $technicienFav = null;
                $max = 0;
                foreach ($technicienArr as $compteId => $nb) {
                    if ($this->dm->getRepository('AppBundle:Compte')->findOneById($compteId)) {
                        if ($nb > $max) {
                            $technicienFav = $compteId;
                            $max = $nb;
                        }
                    }
                }
                if ($technicienFav) {
                    $tech = $this->dm->getRepository('AppBundle:Compte')->findOneById($technicienFav);
                    if ($tech) {
                        $passage->addTechnicien($tech);
                    } else {
                        $output->writeln(sprintf("\n<comment>ATTENTION LE technicien : %s n'existe pas en base %s !</comment>", $technicienFav));
                    }
                } else {
                    $tech = $passage->getContrat()->getTechnicien();
                    if ($tech) {
                        $passage->addTechnicien($tech);
                    } else {
                        $output->writeln(sprintf("\n<comment>Passage : %s de contrat %s n'aura aucun technicien !</comment>", $passage->getId(),$passage->getContrat()->getId()));
                    }
                }
            }
            $cptTotal++;
            if ($cptTotal % (count($allPassagesAPlanifier) / 100) == 0) {
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
