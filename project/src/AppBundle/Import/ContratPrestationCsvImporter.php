<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Import;

/**
 * Description of EtablissementCsvImport
 *
 * @author mathurin
 */
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\ContratManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Behat\Transliterator\Transliterator;
use AppBundle\Import\CsvFile;
use Symfony\Component\Console\Helper\ProgressBar;

class ContratPrestationCsvImporter {

    protected $dm;
    protected $cm;

    const CSV_OLD_ID_CONTRAT = 2;
    const CSV_PRESTATION = 8;

    public function __construct(DocumentManager $dm, ContratManager $cm) {
        $this->dm = $dm;
        $this->cm = $cm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $progress = new ProgressBar($output, 100);
        $progress->start();

        $csv = $csvFile->getCsv();
        $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
        $prestationsArray = $configuration->getPrestationsArray();


        $i = 0;
        $cptTotal = 0;
        foreach ($csv as $data) {

            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_OLD_ID_CONTRAT]);

            if (!$contrat) {
                $i++;
                $cptTotal++;
                continue;
            }
            $prestationNom = strtoupper(Transliterator::urlize(str_replace('#', '', $data[self::CSV_PRESTATION])));
            if (!array_key_exists($prestationNom, $prestationsArray)) {
                $i++;
                $cptTotal++;
                $output->writeln(sprintf("<comment>La prestation '%s' du contrat %s n'existe pas en base!</comment>", $prestationNom, $contrat->getId()));
                continue;
            }

            $prestationConf = $prestationsArray[$prestationNom];
            $prestation = clone $prestationConf;
            $prestation->setNbPassages(0);
            $contrat->addPrestation($prestation);
            
            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }
            if ($i >= 1000) {
                $this->dm->flush();
                $this->dm->clear();
                gc_collect_cycles();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();
    }

}
