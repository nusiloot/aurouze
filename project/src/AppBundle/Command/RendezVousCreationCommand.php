<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Document\RendezVous;

class RendezVousCreationCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('rdv:create')
                ->setDescription('Créer un rendez vous')
                ->addArgument('titre', InputArgument::REQUIRED, "Titre du rendez vous")
                ->addArgument('dateDebut', InputArgument::REQUIRED, "Date début")
                ->addArgument('dateFin', InputArgument::REQUIRED, "Date fin")
                ->addArgument('compte', InputArgument::REQUIRED, "Compte");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $rm = $this->getContainer()->get('rendezvous.manager');
        $cm = $this->getContainer()->get('compte.manager');

        $rdv = new RendezVous();
        $rdv->setTitre($input->getArgument("titre"));
        $rdv->setDateDebut(new \DateTime($input->getArgument("dateDebut")));
        $rdv->setDateFin(new \DateTime($input->getArgument("dateFin")));

        $compte = $cm->getRepository()->find($input->getArgument("compte"));

        if(!$compte) {
            throw new \Exception(sprintf("Le compte n'existe pas %s", $input->getArgument("compte")));
        }

        $rdv->addParticipant($compte);

        $dm->persist($rdv);
        $dm->flush();

    }

}
