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
use AppBundle\Manager\EtablissementManager;

class EtablissementsUpdateTypeCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('etablissements:etablissements-update-type')
                ->setDescription('Les etablissements dont le nom est Immeuble deviennent de type NOM...')
                ->addArgument('nom', InputArgument::REQUIRED, "nom pour lesquels les etablissements vont devenir un type");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $nom = $input->getArgument("nom");
        $i=0;
        foreach ($this->dm->getRepository('AppBundle:Etablissement')->findAll() as $etablissement) {
            if(strtoupper(trim($nom)) == strtoupper(trim($etablissement->getNom(false))) || strtoupper(trim($nom)."s") == strtoupper(trim($etablissement->getNom(false)))) {
              $n = $etablissement->getNom(false);
              $nc = $etablissement->getNom();
              $etablissement->setNom($etablissement->getAdresse()->getCodePostal()." ".$etablissement->getAdresse()->getCommune());
              $etablissement->setType(EtablissementManager::TYPE_ETB_IMMEUBLE);
              echo $etablissement->getId()." => ".$n." (affiché : ".$nc.")   devient =>  ".$etablissement->getNom(false)." (affiché : ".$etablissement->getNom().") \n";
            }
              if($i > 500) {
                $this->dm->flush();
                $i=0;
              }
              $i++;
        }

        $this->dm->flush();
    }
}
