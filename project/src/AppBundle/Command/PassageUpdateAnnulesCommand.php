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

class PassageUpdateAnnulesCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:passages-update-annules')
                ->setDescription('Passages update annules');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $this->updatePassagesAnnules($output);
    }

    public function updatePassagesAnnules($output) {
        echo "\nMis à jour des passages a annuler ...\n";

        $allPassagesAPlanifier = $this->dm->getRepository('AppBundle:Passage')->findToPlan();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allPassagesAPlanifier as $passage) {
            $datePrevision = $passage->getDatePrevision()->format('Ymd');
            if($datePrevision < (new \DateTime('2015-01-01'))->format('Ymd')){
                $passage->setStatut(PassageManager::STATUT_ANNULE);
            }elseif($datePrevision < (new \DateTime('2016-01-01'))->format('Ymd')){
                if($passage->getContrat()->getTypeContrat() == ContratManager::TYPE_CONTRAT_ANNULE){
                    $passage->setStatut(PassageManager::STATUT_ANNULE);
                }
            }

            $cptTotal++;
            if ($cptTotal % (count($allPassagesAPlanifier) / 10) == 0) {
                $progress->advance();
            }
            if ($i >= 15) {
                $this->dm->flush();
                $i = 0;
            }
            $i++;
        }
        $this->dm->flush();
        $progress->finish();
    }

}
