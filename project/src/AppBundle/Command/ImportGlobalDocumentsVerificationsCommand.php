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
use AppBundle\Import\CsvFile;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportGlobalDocumentsVerificationsCommand extends ContainerAwareCommand {

    protected $dm;

    protected function configure() {
        $this
                ->setName('import:global-documents-verifications')
                ->setDescription('Import Global Documents Verifications')
                ->addArgument('type', InputArgument::REQUIRED, "Type du document")
                ->addArgument('path', InputArgument::REQUIRED, 'fichier');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $typeDocument = $input->getArgument('type');
        $pathFile = $input->getArgument('path');
        $repo = 'AppBundle:' . $typeDocument;

        $csvFile = new CsvFile($pathFile);

        $csv = $csvFile->getCsv();

        if($typeDocument == "Societe"){
          $this->verifySocietes($csv,$repo,$output);
        }
        if($typeDocument == "Etablissement"){
          $this->verifyEtablissements($csv,$repo,$output);
        }
    }

    private function verifySocietes($csv,$repo,$output){
      foreach ($csv as $ligne) {
          $idRepriseEntite = "" . $ligne[0];
          $soc = null;
          if(trim($ligne[1]) && !is_null($ligne[1])){
            $socs = $this->dm->getRepository($repo)->findByIdentifiantAdresseReprise($ligne[1]);
          }else{
            $socs = $this->dm->getRepository($repo)->findByIdentifiantReprise($ligne[0]);
          }
          if(count($socs) != 1 ){
            $output->writeln(sprintf("<error>SOCIETE / Problème d'import avec la ligne  %s </error>", implode(",",$ligne)));
          }else{
            //  $output->writeln(sprintf("Societe importée : %s", $socs[0]->getId()));
          }
      }

      foreach ($this->dm->getRepository($repo)->findAll() as $s) {
        $found = false;
        $idRepriseEntite = $s->getIdentifiantReprise();
        $idRepriseAdresse = $s->getIdentifiantAdresseReprise();
        if($idRepriseAdresse){
          foreach ($csv as $ligne) {
            if($ligne[1] == $idRepriseAdresse){
              $found = $ligne;
              break;
            }
          }
        //  $output->writeln(sprintf("Societe bien reprise : %s", implode(",",$found)));
        }else{
          foreach ($csv as $ligne) {
            if($ligne[0] == $idRepriseEntite){
              $found = $ligne;
              break;
            }
          }
        //  $output->writeln(sprintf("Societe bien reprise : %s", implode(",",$found)));
        }
      }
      if(!$found){
        $output->writeln(sprintf("<error>SOCIETE %s ne se trouvant pas dans le fichier?</error>", $s->getId()));
      }
    }

    private function verifyEtablissements($csv,$repo,$output){
      foreach ($csv as $ligne) {
          $etb = null;
          $etbs = $this->dm->getRepository($repo)->findByIdentifiantReprise($ligne[0]);

          if(count($etbs) != 1 ){
            $output->writeln(sprintf("<error>ETABLISSEMENT / Problème d'import avec la ligne  %s </error>", implode(",",$ligne)));
          }else{
            //  $output->writeln(sprintf("Societe importée : %s", $socs[0]->getId()));
          }
      }

      foreach ($this->dm->getRepository($repo)->findAll() as $e) {
        $found = false;
        $idReprise = $e->getIdentifiantReprise();
          foreach ($csv as $ligne) {
            if($ligne[0] == $idReprise){
              $found = $ligne;
              break;
            }
        //  $output->writeln(sprintf("Societe bien reprise : %s", implode(",",$found)));
        }
      }
      if(!$found){
        $output->writeln(sprintf("<error>Etablissement %s ne se trouvant pas dans le fichier?</error>", $e->getId()));
      }
    }

}
