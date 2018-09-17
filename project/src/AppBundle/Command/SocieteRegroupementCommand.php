<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SocieteRegroupementCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('societe:regroupement')
                ->setDescription("Regroupement de touts les documents liés à une société dans une autre")
                ->addArgument(
                    'societe', InputArgument::REQUIRED, "Société qui va accueillir les docs"
                )->addArgument(
                    'societe_a_migrer', InputArgument::REQUIRED, 'Société pour laquelle tous les docs sont à migrer'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $sm = $this->getContainer()->get('societe.manager');
        $cm = $this->getContainer()->get('contrat.manager');
        $fm = $this->getContainer()->get('facture.manager');

        $societe = $sm->getRepository()->findOneBy(array('id' => $input->getArgument('societe')));

        if(!$societe) {

            throw new \Exception("Société non trouvé");
        }
        $societeAMigrer = $sm->getRepository()->findOneBy(array('id' => $input->getArgument('societe_a_migrer')));

        if(!$societeAMigrer) {

            throw new \Exception("Société a migré non trouvé");
        }

        foreach($societeAMigrer->getEtablissements() as $etablissement) {
            $etablissement->setSociete($societe);
            echo $etablissement->getId()." migré\n";
        }

        foreach($societeAMigrer->getComptes() as $compte) {
            $compte->setSociete($societe);
            echo $compte->getId()." migré\n";
        }

        $contrats = $cm->getRepository()->findBy(array("societe" => $societeAMigrer->getId()));

        foreach($contrats as $contrat) {
            $contrat->setSociete($societe);
            foreach($contrat->getMouvements() as $mouvement) {
                $mouvement->setSociete($societe);
            }
            echo $contrat->getId()." migré\n";
        }

        $factures = $fm->getRepository()->findBy(array("societe" => $societeAMigrer->getId()));

        foreach($factures as $facture) {
            $facture->setSociete($societe, false);
            echo $facture->getId()." migré\n";
        }

        $dm->flush();
    }
}
