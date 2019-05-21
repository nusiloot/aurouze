<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Import\CsvFile;

class SocieteCorrectionEtablissementCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('societe:correction-factures')
                ->setDescription("Tache liée à la mis en conformité de l'import")
                ->addArgument(
                    'societe', InputArgument::REQUIRED, "Société à corriger"
                )->addArgument(
                    'file', InputArgument::REQUIRED, 'fichier source des Facture'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $sm = $this->getContainer()->get('societe.manager');
        $em = $this->getContainer()->get('etablissement.manager');
        $cm = $this->getContainer()->get('contrat.manager');
        $fm = $this->getContainer()->get('facture.manager');

        $csvFile = new CsvFile($input->getArgument('file'));


        $factures = $fm->getRepository()->findBy(array('societe' => $input->getArgument('societe')));



        foreach($factures as $facture) {
           $r = $facture->getIdentifiantReprise();
           $log = $facture->getId()." (r=".$r.") déplacement | ";
          // $oldSocId = $compte->getSociete()->getId();
            foreach ($csvFile->getCsv() as $data) {
              if($r == $data[0]){
                $socG = $sm->getRepository()->findOneByIdentifiantReprise($data[6]);
          //      $idetb = $data[0];
          //      $etbG = $em->getRepository()->findOneByIdentifiantReprise($idetb);
               if($socG){
                  $rs = $socG->getRaisonSociale();
                  echo $log." vers société  $rs \n";
                  $socG->setActif(true);
                  foreach ($socG->getEtablissements() as $key => $etb) {
                    $etb->setActif(true);
                  }
                  $facture->setSociete($socG);
               }else{
          //        $socG = $sm->getRepository()->findOneByIdentifiantReprise($data[22]);
          //        if($socG){
          //          $rs = $socG->getRaisonSociale();
          //          $compte->setSociete($socG);
          //          echo $log." pas etb | vers $rs \n";
          //        }else{
                    echo $log." pas soc ! \n";
          //        }
               }
          //      echo $oldSocId." => ".$compte->getSociete()->getId()."\n";
                $dm->flush();
              }
          }
        }
    }
}
