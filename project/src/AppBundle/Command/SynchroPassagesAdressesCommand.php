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

class SynchroPassagesAdressesCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:synchro-passages-adresses')
                ->setDescription('Mise à jour des coordonnees des passages à partir des adresses d\'établissements');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $i=0;
        $j = 0;
        foreach ($this->dm->getRepository('AppBundle:Passage')->findAll() as $passage) {
            echo $passage->getId()." $j ";

            $etb = $passage->getEtablissement();
            $passage->getEtablissementInfos()->pull($etb);
            $c = $passage->getEtablissementInfos()->getAdresse()->getCoordonnees();
            echo $c->getLat()." x ".$c->getLon()."\n";
            if($i > 500) {
                $this->dm->flush();
                $i=0;
            }
            $i++;
            $j++;
        }

        $this->dm->flush();
    }
}
