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
use AppBundle\Document\Etablissement as Etablissement;
use AppBundle\Manager\EtablissementManager as EtablissementManager;

class EtablissementCsvImport extends CsvFile {

    protected $dm;

    const CSV_ID = 0;
    const CSV_LIBELLE = 2;
    const CSV_TYPE_ETABLISSEMENT = 6;

    public function setManager($dm) {
        $this->dm = $dm;
    }

    public function import() {
        $this->errors = array();
        $csv = $this->getCsv();
        $etablissementManager = new EtablissementManager($this->dm);


        foreach ($csv as $data) {
            $etablissement = $etablissementManager->createFromImport($data);
            $this->dm->persist($etablissement);
            $this->dm->flush();
        }
    }

}
