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
                ->setName('societe:correction-etablissements')
                ->setDescription("Tache liée à la mis en conformité de l'import")
                ->addArgument(
                    'societe', InputArgument::REQUIRED, "Société à corriger"
                )->addArgument(
                    'file', InputArgument::REQUIRED, 'fichier source des Etablissements'
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

        foreach($societe->getEtablissements() as $etablissement) {
          $r = $etablissement->getIdentifiantReprise();
          echo $etablissement->getId()." (r=".$r.") déplacement | ";
          $socG = null;
          foreach ($csvFile->getCsv() as $data) {
            if($r == $data[0]){
              $rs = $data[1];
              echo " vers société de reprise $rs | ";
              $socG = $sm->getRepository()->findOneByIdentifiantReprise($rs);
              $etbIdentite = str_replace('ASSOCIATION DES CITES DU SECOURS CATHOLIQUES CITE "ROSIER ROUGE" - ',"",$etablissement->getNom());
              echo $socG->getId()." ".$socG->getRaisonSociale()." | $etbIdentite \n";
            }
          }
          if($socG){
            $etablissement->setSociete($socG);
          }
          $dm->flush();
        }
    }
}
