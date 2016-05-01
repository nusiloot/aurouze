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
use AppBundle\Document\FactureLigne;

class FactureCsvImporter {

    protected $dm;
    protected $fm;
    protected $cm;

    const CSV_FACTURE_ID = 0;
    const CSV_FACTURE_LIGNE_ID = 1;
    const CSV_FACTURE_LIGNE_PASSAGE = 2;
    const CSV_FACTURE_LIGNE_LIBELLE = 4;
    const CSV_FACTURE_LIGNE_PUHT = 5;
    const CSV_FACTURE_LIGNE_QTE = 6;
    const CSV_FACTURE_LIGNE_TVA = 7;
    const CSV_CONTRAT_ID = 8;
    const CSV_NUMERO_FACTURE = 9;
    const CSV_IS_AVOIR = 10;
    const CSV_REF_AVOIR = 11;
    const CSV_DATE_D = 15;
    const CSV_DATE_LIMITE_REGLEMENT = 16;
    const CSV_REGLEMENT_TYPE = 17;
    const CSV_FACTURE_CMT = 19;
    const CSV_TVA_REDUITE = 27;
    const CSV_DATE_CREATION = 28;
    const CSV_REF_PASSAGE = 33;

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

            $facture = $this->fm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_FACTURE_ID]);
            if ($facture) {
                $output->writeln(sprintf("<comment>La facture %s existe déjà avec l'id %s </comment>", $data[self::CSV_FACTURE_ID], $facture->getId()));
            } else {
                $facture = new Facture();
                $facture->setDateEmission(new \DateTime($data[self::CSV_DATE_CREATION]));
                $facture->setSociete($contrat->getSociete());
            }
            $this->dm->persist($facture);

            $facture->setIdentifiantReprise($data[self::CSV_FACTURE_ID]);
            $facture->setNumeroFacture($data[self::CSV_NUMERO_FACTURE]);
            
            $fl = new FactureLigne();
            $fl->setLibelle($data[self::CSV_FACTURE_LIGNE_LIBELLE]);
            $fl->setMontantHT($data[self::CSV_FACTURE_LIGNE_PUHT]);
            $fl->setPrixUnitaire($data[self::CSV_FACTURE_LIGNE_PUHT]);
            $fl->setQuantite($data[self::CSV_FACTURE_LIGNE_QTE]);
            $fl->setTauxTaxe(0.2);
            if ($data[self::CSV_TVA_REDUITE]) {
                $fl->setTauxTaxe(0.1);
            }            
            $fl->setOrigineDocument($contrat);
            $this->dm->persist($fl);
            $facture->addLigne($fl);
            $facture->update();
            
            $mouvement = new Mouvement();
            $mouvement->setPrix($data[self::CSV_FACTURE_LIGNE_PUHT]);
            $mouvement->setFacturable(true);
            $mouvement->setFacture(true);
            $mouvement->setOrigineDocument($facture);           
            $mouvement->setLibelle($data[self::CSV_FACTURE_LIGNE_LIBELLE]);
            /*
             * ????
             */
            
            
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
