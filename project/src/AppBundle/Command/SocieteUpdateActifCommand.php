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
use Symfony\Component\Debug\Exception\UndefinedMethodException;

class SocieteUpdateActifCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('update:societe-update-actif')
                ->setDescription('Societe update actif')
                ->addArgument('date', InputArgument::REQUIRED, "Date d'ancienneté à partir de laquel on va fermer le societes et établissements (Y-m-d)");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $date = \DateTime::createFromFormat('Y-m-d',$input->getArgument("date"));
        echo "\nMis à jour des etablissements actifs...\n";
        $this->updateEtablissementActif($output, $date);

        echo "\nMis à jour des societes actives...\n";
        $this->updateSocieteActif($output, $date);
    }

    public function updateEtablissementActif($output, $date) {

        $allPassages = $this->dm->getRepository('AppBundle:Passage')->findAll();

        $allEtbWithPassage = array();

        $allContrats = $this->dm->getRepository('AppBundle:Contrat')->findAll();

        foreach ($allContrats as $contrat) {
            if(($contrat->getDateFin() && ($contrat->getDateFin()->format("Ymd") > $date->format('Ymd'))) || $contrat->hasPrestation("TRAITEMENT DES BOIS")){
              foreach ($contrat->getContratPassages() as $contratPassage) {
                $allEtbWithPassage[$contratPassage->getEtablissement()->getId()] = $contratPassage->getEtablissement()->getId();
              }
            }
        }

        $allEtablissements = $this->dm->getRepository('AppBundle:Etablissement')->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();

        foreach ($allEtablissements as $etablissement) {
            if (in_array($etablissement->getId(), $allEtbWithPassage)) {
                $etablissement->setActif(true);
            } else {
                $etablissement->setActif(false);
                echo "L'établissement ".$etablissement->getId()." a été suspendu \n";
            }
            $cptTotal++;
            if ($cptTotal % (count($allPassages) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 2000) {
                $this->dm->flush();
                $i = 0;
            }
            $i++;
        }
        $this->dm->flush();
        $progress->finish();
    }

    public function updateSocieteActif($output, $date) {
        $allContrats = $this->dm->getRepository('AppBundle:Contrat')->findAll();

        $allSocWithContrat = array();

        foreach ($allContrats as $contrat) {
            if(($contrat->getDateFin() && ($contrat->getDateFin()->format("Ymd") > $date->format('Ymd'))) || $contrat->hasPrestation("TRAITEMENT DES BOIS")){
              if (!array_key_exists($contrat->getSociete()->getId(), $allSocWithContrat)) {
                  $allSocWithContrat[$contrat->getSociete()->getId()] = $contrat->getSociete()->getId();
              }
            }
        }

        $allSocietes = $this->dm->getRepository('AppBundle:Societe')->findAll();

        $cptTotal = 0;
        $i = 0;
        $progress = new ProgressBar($output, 100);
        $progress->start();
        foreach ($allSocietes as $societe) {
            if (in_array($societe->getId(), $allSocWithContrat)) {
                $societe->setActif(true);
            } else {
                $societe->setActif(false);
                echo "La société ".$societe->getId()." a été suspendue \n";
            }

            $cptTotal++;
            if ($cptTotal % (count($allSocietes) / 100) == 0) {
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
