<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Import\CsvFile;

class EtablissementCorrectionCommentaireCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('etablissement:correction-commentaire')
                ->setDescription("Tache liée à la mis en conformité de l'import")
                ->addArgument(
                    'file', InputArgument::REQUIRED, 'fichier source des etablissement'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $sm = $this->getContainer()->get('societe.manager');
        $em = $this->getContainer()->get('etablissement.manager');
        $cm = $this->getContainer()->get('contrat.manager');
        $fm = $this->getContainer()->get('facture.manager');

        $csvFile = new CsvFile($input->getArgument('file'));


          $i=0;
            foreach ($csvFile->getCsv() as $data) {
                $etbs = $em->getRepository()->findByIdentifiantReprise($data[0]);
          //      $idetb = $data[0];
          //      $etbG = $em->getRepository()->findOneByIdentifiantReprise($idetb);
          foreach ($etbs as $etb) {
              $i++;
               if($etb){
                 $id= $etb->getId();
                  $com = $etb->getCommentaire();
                  $comBon = $data[14];
                  echo "$id => \"$com\" devient \"$comBon\"\n";
                  $etb->setCommentaire($comBon);
               }else{
                    echo $data[0]." pas etb ! \n";
               }
             }
             if($i > 10) {
                 $dm->flush();
                 $i=0;
             }

        }
        $dm->flush();
    }
}
