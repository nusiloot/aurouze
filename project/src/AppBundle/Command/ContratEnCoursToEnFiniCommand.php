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

class ContratEnCoursToEnFiniCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this->setName('update:update-enCours-to-Fini')
                ->setDescription('Contrat en-cours vers fermé');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $allContratsEnCours = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_EN_COURS);
        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        $nb =  count($allContratsEnCours);


        foreach ($allContratsEnCours as $contrat) {
                $contrat->verifyAndClose();
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
        $this->dm->flush();
        $progress->finish();
    }

}
