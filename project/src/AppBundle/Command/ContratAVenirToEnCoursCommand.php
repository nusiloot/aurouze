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
use Doctrine\Common\Collections\ArrayCollection;

class ContratAVenirToEnCoursCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:cron-avenir-to-encours')
                ->setDescription('Contrat cron avenir to en cours');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $allContratsAVenir = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_A_VENIR);
        $allContratsEnCours = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_EN_COURS);
        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        $nb = count($allContratsAVenir) + count($allContratsEnCours);

        foreach ($allContratsAVenir as $contrat) {
            if ($contrat->getDateDebut()->format('Ymd') <= (new \DateTime())->format('Ymd')) {
                $contrat->setStatut(ContratManager::STATUT_EN_COURS);
                $cptTotal++;
                if ($cptTotal % ($nb / 100) == 0) {
                    $progress->advance();
                }
                if ($i >= 1000) {
                    $this->dm->flush();
                    $i = 0;
                }
                $i++;
            }
        }
        foreach ($allContratsEnCours as $contrat) {
            if ($contrat->getDateFin()->format('Ymd') < (new \DateTime())->format('Ymd')) {
                $contrat->setStatut(ContratManager::STATUT_FINI);
                $cptTotal++;
                if ($cptTotal % ($nb / 100) == 0) {
                    $progress->advance();
                }
                if ($i >= 1000) {
                    $this->dm->flush();
                    $i = 0;
                }
                $i++;
            }
        }
        $this->dm->flush();
        $progress->finish();
    }

}
