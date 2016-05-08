<?php

namespace AppBundle\Import;

use AppBundle\Document\Configuration;
use AppBundle\Document\Prestation;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Console\Output\OutputInterface;
use Behat\Transliterator\Transliterator;

class ConfigurationPrestationCsvImporter extends CsvFile {

    protected $dm;

    const CSV_ID = 1;
    const CSV_NOM = 2;
    const CSV_NOM_COURT = 3;

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
            $this->dm->persist($configuration);
            $this->dm->flush();
        }
        foreach ($csv as $data) {
            $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
            $founded = false;
            foreach ($configuration->getPrestationsArray() as $prestaConf) {
                if ($prestaConf->getNom() == $data[self::CSV_NOM]) {
                    $founded = true;
                }
            }
            if ($founded) {
                continue;
            }
            if ($data[self::CSV_ID]) {
                $prestation = new Prestation();
                $prestation->setIdentifiant(strtoupper(Transliterator::urlize(trim(preg_replace("/[ ]+/", " ", $data[self::CSV_ID])))));
                $prestation->setNom($data[self::CSV_NOM]);
                $prestation->setNomCourt($data[self::CSV_NOM_COURT]);
                $configuration->addPrestation($prestation);
                $this->dm->flush();
            }
        }
    }

}
