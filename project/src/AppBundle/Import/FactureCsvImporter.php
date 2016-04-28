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
use AppBundle\Document\Mouvement;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Manager\ContratManager;
use AppBundle\Manager\FactureManager;
use AppBundle\Manager\SocieteManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Behat\Transliterator\Transliterator;
use AppBundle\Import\CsvFile;
use Symfony\Component\Console\Helper\ProgressBar;
use AppBundle\Document\Facture;

class FactureCsvImporter {

    protected $dm;
    protected $fm;
    protected $cm;

    const CSV_CONTRAT_ID = 0;
    const CSV_FACTURE_ID = 1;
    const CSV_NUMERO_FACTURE = 2;
    const CSV_IS_AVOIR = 3;
    
    const CSV_DATE_CREATION = 21;
    const CSV_PASSAGE = 26;
    

    public function __construct(DocumentManager $dm, FactureManager $fm, ContratManager $cm) {
        $this->dm = $dm;
        $this->fm = $fm;
        $this->cm = $cm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $progress = new ProgressBar($output, 100);
        $progress->start();

        $csv = $csvFile->getCsv();

        $i = 0;
        $cptTotal = 0;

        foreach ($csv as $data) {
            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_CONTRAT_ID]);

            if (!$contrat) {
                $output->writeln(sprintf("<error>Le contrat %s n'existe pas</error>", $data[self::CSV_CONTRAT_ID]));
                continue;
            }
            
            $facture = new Facture();
            $facture->setDateEmission(new \DateTime($data[self::CSV_DATE_CREATION]));
            $facture->setSociete($contrat->getSociete());
            $facture->setNumeroFacture($data[self::CSV_NUMERO_FACTURE]);
            $facture->setIdentifiantReprise($data[self::CSV_FACTURE_ID]);
            $this->dm->persist($facture);
            
            $mouvement = new Mouvement();
           // $mouvement->setPrix($data[self::CSV_PRIX_UNITAIRE]);
            $mouvement->setFacturable(true);
            $mouvement->setFacture(false);            

            $contrat->addMouvement($mouvement);

            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }

            if ($i >= 1000) {
                $this->dm->flush();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();
    }

}
