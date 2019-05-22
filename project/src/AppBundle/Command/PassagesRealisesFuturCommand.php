<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Import\CsvFile;
use AppBundle\Manager\PassageManager;

class PassagesRealisesFuturCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('passages:realises-futur')
                ->setDescription("Tache liée à la mis en conformité de l'import");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $pm = $this->getContainer()->get('passage.manager');
        $em = $this->getContainer()->get('etablissement.manager');
        $cm = $this->getContainer()->get('contrat.manager');
        $fm = $this->getContainer()->get('facture.manager');

        $pss = $pm->getRepository()->findByStatut('REALISE');
            foreach ($pss as $ps) {
                if($ps->getDateRealise() >= new \DateTime('now')){
                  echo $ps->getId()." ". $ps->getDateRealise()->format("Y-m-d")."\n";
                  $ps->setDateRealise(null);
                  $ps->setDateFin(null);
                  $ps->setStatut(PassageManager::STATUT_A_PLANIFIER);
                }
          //      $idetb = $data[0];
          //      $etbG = $em->getRepository()->findOneByIdentifiantReprise($idetb);

          //      echo $oldSocId." => ".$compte->getSociete()->getId()."\n";
        }
        $dm->flush();
    }
}
