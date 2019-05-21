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
                ->setName('societe:correction-comptes')
                ->setDescription("Tache liée à la mis en conformité de l'import")
                ->addArgument(
                    'societe', InputArgument::REQUIRED, "Société à corriger"
                )->addArgument(
                    'file', InputArgument::REQUIRED, 'fichier source des Comptes'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $sm = $this->getContainer()->get('societe.manager');
        $em = $this->getContainer()->get('etablissement.manager');
        $cm = $this->getContainer()->get('contrat.manager');
        $fm = $this->getContainer()->get('facture.manager');

        $csvFile = new CsvFile($input->getArgument('file'));

        $societe = $sm->getRepository()->findOneBy(array('id' => $input->getArgument('societe')));

        if(!$societe) {

            throw new \Exception("Société non trouvé");
        }

        foreach($societe->getComptes() as $compte) {
          $r = $compte->getIdentifiantReprise();
          $log = $compte->getId()." ".$compte->getNom()." (r=".$r.") déplacement | ";
          $oldSocId = $compte->getSociete()->getId();
           foreach ($csvFile->getCsv() as $data) {
             if($r == $data[1]){
               $idetb = $data[0];
               $etbG = $em->getRepository()->findOneByIdentifiantReprise($idetb);
               if($etbG){
                 $nom = $etbG->getNom();
                 echo $log." vers etablissement de reprise $idetb | $nom \n";
                 $compte->setSociete($etbG->getSociete());
               }else{
                 $socG = $sm->getRepository()->findOneByIdentifiantReprise($data[22]);
                 if($socG){
                   $rs = $socG->getRaisonSociale();
                   $compte->setSociete($socG);
                   echo $log." pas etb | vers $rs \n";
                 }else{
                   echo $log." pas soc ! \n";
                 }
               }
               echo $oldSocId." => ".$compte->getSociete()->getId()."\n";
               $dm->flush();
             }
           }
        }
    }
}
