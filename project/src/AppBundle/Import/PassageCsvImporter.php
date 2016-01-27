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
use AppBundle\Document\Passage;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\PassageManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Import\CsvFile;

class PassageCsvImporter {

    protected $dm;
    protected $pm;

    const CSV_ID = 0;
    const CSV_LIBELLE = 2;
    const CSV_TYPE_ETABLISSEMENT = 6;

    public function __construct(DocumentManager $dm, PassageManager $pm) {
        $this->dm = $dm;
        $this->pm = $pm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $csv = $csvFile->getCsv();

        foreach ($csv as $data) {
            $passage = new Passage();

            //$this->dm->persist($passage);
            //$this->dm->flush();
        }
    }

}
