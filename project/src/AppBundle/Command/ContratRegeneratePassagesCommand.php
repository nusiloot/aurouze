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

class ContratRegeneratePassagesCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('contrat:regenerate-passages')
                ->setDescription('')
                ->addArgument(
                    'id_doc', InputArgument::REQUIRED, 'Id MongoDB du contrat'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $cm = $this->getContainer()->get('contrat.manager');
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $contrat = $cm->getRepository()->find($input->getArgument('id_doc'));

        if (!$contrat->getDateAcceptation())
        {
            $output->writeln("Non accepté " . $contrat->getNumeroArchive() . " ".$contrat->getId()." : ".$contrat->getDateDebut()->format('Y-m-d')." / ".$contrat->getDateFin()->format('Y-m-d') ." date de création: ".(($contrat->getDateCreation()) ? $contrat->getDateCreation()->format('Y-m-d'): null));
            return;
        }

        if($contrat->getDateDebut()->format('Y-m-d') != $contrat->getDateFin()->format('Y-m-d')) {
            $output->writeln("Déjà OK  " . $contrat->getNumeroArchive() . " ".$contrat->getId());
            return;
        }

        $dateCreation = clone $contrat->getDateCreation();
        $dateCreation->modify("+ 6 month");

        $dateDebut = clone $contrat->getDateDebut();

        $nbDecalage = 0;
        while($dateDebut->format('Y-m-d') > $dateCreation->format('Y-m-d')) {

            $dateDebut = $dateDebut->modify("-" . $contrat->getDuree() . " month");
            $nbDecalage++;
        }

        $contrat->setDateDebut($dateDebut);
        $dateFin = clone $contrat->getDateDebut();
        $dateFin = $dateFin->modify("+" . $contrat->getDuree() . " month");
        $contrat->setDateFin($dateFin);

        if ($contrat->isModifiable() && $contrat->getDateDebut()) {
            $cm->generateAllPassagesForContrat($contrat);
            $output->writeln("Modification et regénération des passages " . $contrat->getNumeroArchive() . " ".$contrat->getId()." : ".$contrat->getDateDebut()->format('Y-m-d')." / ".$contrat->getDateFin()->format('Y-m-d') ." date de création: ".(($contrat->getDateCreation()) ? $contrat->getDateCreation()->format('Y-m-d'): null). " " . $nbDecalage . ' decalages');
        } else {
            $output->writeln("Modification " . $contrat->getNumeroArchive() . " ".$contrat->getId()." : ".$contrat->getDateDebut()->format('Y-m-d')." / ".$contrat->getDateFin()->format('Y-m-d') ." date de création: ".(($contrat->getDateCreation()) ? $contrat->getDateCreation()->format('Y-m-d'): null). " " . $nbDecalage . ' decalages');
        }

        $dm->flush();

    }
}
