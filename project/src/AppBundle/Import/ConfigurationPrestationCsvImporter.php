<?php

namespace AppBundle\Import;

use AppBundle\Document\Configuration;
use AppBundle\Document\Prestation;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationPrestationCsvImporter extends CsvFile {

    protected $dm;

    const CSV_ID = 0;
    const CSV_LIBELLE1 = 1;
    const CSV_LIBELLE2 = 2;
    const CSV_LIBELLE3 = 3;
    const CSV_LIBELLE4 = 4;
    const CSV_NOM = 5;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();
        $cpt = 0;
        $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
        if (!$configuration) {
            $configuration = new Configuration();
            $configuration->setId(Configuration::PREFIX); 
        }
        foreach ($csv as $data) {
            $prestation = new Prestation();
            $prestation->setNom($data[self::CSV_NOM]);
            $configuration->addPrestation($prestation);
            $this->dm->persist($configuration);
        }
        $this->dm->flush();
    }


}
