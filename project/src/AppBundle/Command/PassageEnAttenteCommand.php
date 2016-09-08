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

class PassageEnAttenteCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('maintenance:passage-en-attente')
                ->setDescription('Passage en attente');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $q = $this->dm->createQueryBuilder('AppBundle:Passage')->getQuery();

        $iterableResult = $q->iterate();

        foreach ($iterableResult as $passage) {
            if($passage->getStatut() != PassageManager::STATUT_EN_ATTENTE) {
                continue;
            }

            if(!$passage->getContrat()->isAVenir() && !$passage->getContrat()->isEnCours()) {
                continue;
            }

            if(!$passage->isSousContrat()) {
                continue;
            }

            if($passage->getDatePrevision()->format('Y') < 2015) {
                continue;
            }

            $previousPassage = $this->getContainer()->get('passage.manager')->passagePrecedentSousContrat($passage);

            if($previousPassage && $previousPassage->isRealise() && $previousPassage->isPlanifie()) {
                continue;
            }

            $passage->setDateDebut($passage->getDatePrevision());
            $passage->setStatut(PassageManager::STATUT_A_PLANIFIER);

            echo "Passage de ".$passage->getDatePrevision()->format('M Y')." pour l'établissment ".$passage->getEtablissementInfos()->getNom()." dans le contrat n°".$passage->getContrat()->getNumeroArchive()." : ".$passage->getContrat()->getId()." ".$passage->getId()."\n";
        }

        $this->dm->flush();
    }
}
