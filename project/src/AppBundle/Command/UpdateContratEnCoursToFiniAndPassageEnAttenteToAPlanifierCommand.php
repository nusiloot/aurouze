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

class UpdateContratEnCoursToFiniAndPassageEnAttenteToAPlanifierCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this->setName('update:contrat-encours-to-fini-and-passage-enattente-to-aplanifier')
                ->setDescription('Contrat en-cours vers fermé')
                ->addArgument('date', InputArgument::REQUIRED, "Date d'ancienneté à partir de laquelle on va fermer les contrats qui n'ont pas été acceptés (Y-m-d)");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $date = \DateTime::createFromFormat('Y-m-d',$input->getArgument("date"));
        $allContratsEnCours =  $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_EN_COURS);

        $allPassageEnAttente = $this->dm->getRepository('AppBundle:Passage')->findByStatut("EN_ATTENTE");

        $allContratsEnAttenteAcceptation = $this->dm->getRepository('AppBundle:Contrat')->findByStatut(ContratManager::STATUT_EN_ATTENTE_ACCEPTATION);

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        $nb =  count($allContratsEnCours) + count($allPassageEnAttente) + count($allContratsEnAttenteAcceptation);


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

        foreach ($allPassageEnAttente as $passage) {
                $passage->setStatut(PassageManager::STATUT_A_PLANIFIER);
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

        foreach ($allContratsEnAttenteAcceptation as $contrat_en_attente) {

                $dateDebut = $contrat_en_attente->getDateDebut();
                $dateCreation = $contrat_en_attente->getDateCreation();
                $dureeContrat = $contrat_en_attente->getDuree();
                $calcEnd = null;
                if($dateDebut){
                  $calcEnd = clone $dateDebut;
                }elseif($dateCreation){
                  $calcEnd = clone $dateCreation;
                }else{
                  echo "Contrat : ".$contrat_en_attente->getId()." n'a aucune date sur laquelle se baser \n";
                }
                $calcEnd->modify("+".$dureeContrat." month");
                if($calcEnd->format("Ymd") < $date->format('Ymd')){
                  echo "Fermeture automatique du contrat : ".$contrat_en_attente->getId()." > date calculé de fin : ".$calcEnd->format("Y-m-d")." (durée : ".$dureeContrat.") \n";
                  $contrat_en_attente->setTypeContrat(ContratManager::TYPE_CONTRAT_ANNULE);
                  $contrat_en_attente->setDateResiliation($calcEnd);
                }

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
