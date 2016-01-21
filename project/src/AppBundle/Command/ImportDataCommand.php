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

use AppBundle\Import as Import;

class ImportDataCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('import:data')
                ->setDescription('Import des données')
                ->addArgument(
                        'name_doc', InputArgument::REQUIRED, 'nom entité'
                )->addArgument(
                'path', InputArgument::REQUIRED, 'path fichier'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $name_doc = $input->getArgument('name_doc');
        $path = $input->getArgument('path');
        
        $className = 'AppBundle\\Import\\'.ucfirst($name_doc)."CsvImport";
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $csv = new $className($path);
        $csv->setManager($dm);
        $csv->import();
    }

}
