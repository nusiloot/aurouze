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

class ImporterCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('importer:csv')
                ->setDescription('Import des données')
                ->addArgument(
                    'service', InputArgument::REQUIRED, "Nom du service à utiliser pour l'import"
                )->addArgument(
                    'path', InputArgument::REQUIRED, 'fichier'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $importer = $this->getContainer()->get($input->getArgument('service'));
        $importer->import($input->getArgument('path'), $output);
    }

}
