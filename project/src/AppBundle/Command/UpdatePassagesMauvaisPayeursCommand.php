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
use AppBundle\Manager\EtablissementManager;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdatePassagesMauvaisPayeursCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('update:passages-mauvais-payeurs')
                ->setDescription('Permet de mettre à jour dans les passages à planifier les sociétés qui sont mauvais payeurs');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {


      $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
      $pm = $this->getContainer()->get('passage.manager');
      $fm = $this->getContainer()->get('facture.manager');

      $dateDebut = new \DateTime();
      $dateDebut->modify("-3 months");

      $dateFin = new \DateTime();
      $dateFin->modify("+4 months");

      $secteurs = array_keys(EtablissementManager::$secteurs);

      foreach ($secteurs as $secteur) {
        $passages = $pm->getRepository()->findToPlan($secteur, $dateDebut, $dateFin)->toArray();
        $cpt = 0;
        foreach ($passages as $passage) {
          $societe = $passage->getSociete();
          $mauvaisPayeur = boolval(count($fm->getRetardDePaiementBySociete($societe)) > 0);
          $passage->getEtablissementInfos()->setMauvaisPayeur($mauvaisPayeur);
          echo $passage->getId().";".$societe->getId().";";
          echo ($mauvaisPayeur)? "Mauvais Payeur" : "Bon Payeur";
          echo "\n";
          $cpt++;
        }
        if($cpt > 200){
          $dm->flush();
          $cpt = 0;
        }
      }
      $dm->flush();
    }
}
