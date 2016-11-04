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

class UpdateContratDatesDePrevisionCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this->setName('update:contrat-dates-de-prevision')
                ->setDescription('Réajustement des dates de prévision des contrats');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $allContratsDatesDePrevision = $this->dm->getRepository('AppBundle:Contrat')->findAllErreurs();


        foreach ($allContratsDatesDePrevision as $contrat) {

          if(count($contrat->datesPrevContrat) != count($contrat->datesPrevLastContrat)){
            echo "Pour le contrat : ".$contrat->contrat->getId()." (".$contrat->contrat->getNumeroArchive().") le nombre de passage est différent\n";
            continue;
          }
          if($contrat->contrat->getDuree() != 12){
            $duree = $contrat->contrat->getDuree();
            echo "Contrat : ".$contrat->contrat->getId()." (".$contrat->contrat->getNumeroArchive().") non traité => prévu sur $duree mois\n";
            continue;
          }

          foreach ($contrat->contrat->getContratPassages() as $contratPassage) {
              $cptPassage = 0;
              foreach ($contratPassage->getPassagesSorted() as $idPassage => $passage) {
                if($passage->isSousContrat()){
                  if(!isset($contrat->datesPrevLastContrat[$cptPassage])){
                    echo "Passage $idPassage du contrat ".$contrat->contrat->getId()." (".$contrat->contrat->getNumeroArchive().") n'a pas d'équivalent l'année précédente\n";
                    continue;
                  }
                  $datePrevisionLastYear = \DateTime::createFromFormat('Y-m-d', ($contrat->datesPrevLastContrat[$cptPassage]));
                  $datePrevision = clone $datePrevisionLastYear;
                  $datePrevision->modify("+1 year");
                  $passage->setDatePrevision($datePrevision);
                  echo "La date du passage $idPassage du contrat ".$contrat->contrat->getId()." (".$contrat->contrat->getNumeroArchive().") est modifié du : ".$contrat->datesPrevContrat[$cptPassage]." au ".$datePrevision->format("Y-m-d")."\n";
                  $cptPassage++;
                }
              }
          }
          echo "#### FIN DE Mis à jour du contrat : ".$contrat->contrat->getId()." (".$contrat->contrat->getNumeroArchive().")\n";

        }

        $this->dm->flush();
    }

}
