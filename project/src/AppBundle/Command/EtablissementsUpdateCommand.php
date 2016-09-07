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
use Symfony\Component\Console\Helper\ProgressBar;
use AppBundle\Manager\ContratManager;

class EtablissementsUpdateCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('etablissements:update')
                ->setDescription('Mise à jour des établissements');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $i=0;
        foreach ($this->dm->getRepository('AppBundle:Etablissement')->findAll() as $etablissement) {
            if(!$etablissement->getActif()) {
                continue;
            }
            echo $etablissement->getId()."\n";

            $this->getContainer()->get('etablissement.manager')->getOSMAdresse()->calculCoordonnees($etablissement->getAdresse());
            $etablissement->updatePassages();
            if($i > 500) {
                $this->dm->flush();
                $i=0;
            }
            $i++;
        }

        $this->dm->flush();
    }
}
