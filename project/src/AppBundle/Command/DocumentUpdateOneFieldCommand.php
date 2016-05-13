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
use AppBundle\Import\CsvFile;
use Symfony\Component\Console\Helper\ProgressBar;

class DocumentUpdateOneFieldCommand extends ContainerAwareCommand {

    protected $dm;

    const CSV_ID_REPRISE = 0;
    const CSV_FIELD_TO_CHANGE = 1;

    protected function configure() {
        $this
                ->setName('update:contrat-update-one-field')
                ->setDescription('Contrat update one field')
                ->addArgument('document', InputArgument::REQUIRED, "Nom du document")
                ->addArgument('field', InputArgument::REQUIRED, "Nom du field à update")
                ->addArgument('path', InputArgument::REQUIRED, 'fichier');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $documentName = $input->getArgument('document');
        $fieldName = $input->getArgument('field');
        $pathFile = $input->getArgument('path');
        $repo = 'AppBundle:' . $documentName;
        $allDocs = $this->dm->getRepository($repo)->findAll();

        $allDocsArray = array();
        foreach ($allDocs as $doc) {
            $allDocsArray[$doc->getIdentifiantReprise()] = $doc;
        }
        $cptTotal = 0;
        $i = 0;

        $csvFile = new CsvFile($pathFile);

        $csv = $csvFile->getCsv();
        $progress = new ProgressBar($output, 100);
        $progress->start();


        foreach ($csv as $ligne) {

            $setFonctionName = "set" . ucfirst($fieldName);
            $idReprise = "" . $ligne[self::CSV_ID_REPRISE];
            if (!array_key_exists($idReprise, $allDocsArray)) {
                continue;
            }
            $docToChange = $allDocsArray[$idReprise];
            if ($ligne[self::CSV_FIELD_TO_CHANGE]) {
                $docToChange->$setFonctionName($ligne[self::CSV_FIELD_TO_CHANGE]);
                $output->writeln(sprintf("\n<comment>Mis à jour du contrat %s : '%s' va être assigné à %s</comment>",$docToChange->getId(), $fieldName, $ligne[self::CSV_FIELD_TO_CHANGE]));                    
            }
            $cptTotal++;
            if ($cptTotal % (count($allDocsArray) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 100) {
                $this->dm->flush();
                $i = 0;
            }
            $i++;
        }
        $this->dm->flush();
        $progress->finish();
    }

}
