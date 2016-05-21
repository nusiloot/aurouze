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
use AppBundle\Manager\FactureManager;
use AppBundle\Manager\PaiementsManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Behat\Transliterator\Transliterator;
use AppBundle\Document\Paiements;
use AppBundle\Document\Paiement;
use AppBundle\Import\CsvFile;
use Symfony\Component\Console\Helper\ProgressBar;

class PaiementsCsvImporter {

    protected $dm;
    protected $pm;
    protected $fm;
    protected $debug = false;

    const CSV_REF_REMISE_CHEQUE = 0;
    const CSV_REGLEMENT_ID = 1;
    const CSV_PAIEMENT_ID = 2;
    const CSV_FACTURE_ID = 3;
    const CSV_PAIEMENT_DATE = 4;
    const CSV_TYPE_REGLEMENT = 5;
    const CSV_MOYEN_PAIEMENT = 6;
    const CSV_MONTANT = 7;
    const CSV_PAIEMENT_DATECREATION = 8;
//    const CSV_REF_REMISE_CHEQUE = 12;
    const CSV_LIBELLE = 13;
    const CSV_MOYEN_PAIEMENT_PIECE = 14; //DEVRAI ETRE == à CSV_MOYEN_PAIEMENT
    const CSV_DATE_PIECE_BANQUE = 15; //DEVRAI ETRE == CSV_PAIEMENT_DATE
    const CSV_MONTANT_PIECE_BANQUE = 16; //DEVRAI ETRE == CSV_MONTANT
    const CSV_DATECREATION_PIECE_BANQUE = 17; //DEVRAI ETRE == CSV_PAIEMENT_DATECREATION
    const CSV_DATE_REMISE_CHEQUE = 22;

    public function __construct(DocumentManager $dm, PaiementsManager $pm, FactureManager $fm) {
        $this->dm = $dm;
        $this->pm = $pm;
        $this->fm = $fm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $progress = new ProgressBar($output, 100);
        $progress->start();

        $csv = $csvFile->getCsv();

        $i = 0;
        $cptTotal = 0;

        foreach ($csv as $data) {

            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }

            if (!$data[self::CSV_FACTURE_ID]) {
                $output->writeln(sprintf("<error>Le paiement %s ne possède aucun numéro de facture</error>", $data[self::CSV_PAIEMENT_ID]));
                continue;
            }

            $facture = $this->fm->getRepository()->findOneByIdentifiantReprise($data[self::CSV_FACTURE_ID]);
            if (!$facture) {
                if ($this->debug) {
                    $output->writeln(sprintf("<comment>La facture %s n'existe pas en base pour le paiement %s</comment>", $data[self::CSV_FACTURE_ID], $data[self::CSV_PAIEMENT_ID]));
                }
                continue;
            }

            if ($data[self::CSV_REGLEMENT_ID]) {
                if ($data[self::CSV_PAIEMENT_DATE] != $data[self::CSV_DATE_PIECE_BANQUE]) {
                    $output->writeln(sprintf("<comment> Facture %s, paiement %s : dates %s (paiement) != %s (banque) Bizar</comment>", $data[self::CSV_FACTURE_ID], $data[self::CSV_PAIEMENT_ID], $data[self::CSV_PAIEMENT_DATE], $data[self::CSV_DATE_PIECE_BANQUE]));
                }
            }
            $idDocPaiements = 'PAIEMENTS-' . (new \DateTime($data[self::CSV_PAIEMENT_DATE]))->format('YmdHi');
            $paiements = $this->pm->getRepository()->findOneById($idDocPaiements);
            if (!$paiements) {
                $paiements = $this->dm->getUnitOfWork()->tryGetById($idDocPaiements, $this->pm->getRepository()->getClassMetadata());
            }
            if (!$paiements) {
                $paiements = $this->pm->createByDateCreation(new \DateTime($data[self::CSV_PAIEMENT_DATE]));
                $this->dm->persist($paiements);
            }

            $paiement = new Paiement();

            $index_moyen_paiement = "";
            $index_type_regl = "";
            $index_type_regl.= $data[self::CSV_TYPE_REGLEMENT];

            $paiement->setMontant($data[self::CSV_MONTANT]);
            if ($data[self::CSV_REGLEMENT_ID]) {
                $paiement->setDatePaiement(new \DateTime($data[self::CSV_DATE_PIECE_BANQUE]));
                $paiement->setLibelle($data[self::CSV_LIBELLE]);
                $index_moyen_paiement.= $data[self::CSV_MOYEN_PAIEMENT_PIECE];
            } else {
                $paiement->setDatePaiement(new \DateTime($data[self::CSV_PAIEMENT_DATE]));
                $index_moyen_paiement.=$data[self::CSV_MOYEN_PAIEMENT];
            }
            $paiement->setFacture($facture);

            if (array_key_exists(self::CSV_DATE_REMISE_CHEQUE, $data) && $data[self::CSV_REF_REMISE_CHEQUE]) {
                if ($data[self::CSV_DATE_REMISE_CHEQUE]) {
                    $output->writeln(sprintf("<comment> %s : Ajout de la date de remise de cheque : %s </comment>", $data[self::CSV_PAIEMENT_ID], $data[self::CSV_DATE_REMISE_CHEQUE]));
                    $paiement->setDatePaiement(new \DateTime($data[self::CSV_DATE_REMISE_CHEQUE]));
                }
            }

            if (!array_key_exists($index_moyen_paiement, PaiementsManager::$moyens_paiement_index)) {
                $output->writeln(sprintf("<comment> Paiement %s : Mode de Paiement inexistant? %s </comment>", $data[self::CSV_PAIEMENT_ID], $index_moyen_paiement));
            } else {
                $paiement->setMoyenPaiement(PaiementsManager::$moyens_paiement_index[$index_moyen_paiement]);
            }

            if (!array_key_exists($index_type_regl, PaiementsManager::$types_reglements_index)) {
                $output->writeln(sprintf("<comment> Paiement %s : Type de reglement inexistant? %s </comment>", $data[self::CSV_PAIEMENT_ID], $index_type_regl));
            } else {
                $paiement->setTypeReglement(PaiementsManager::$types_reglements_index[$index_type_regl]);
            }

            $paiements->addPaiement($paiement);

            $i++;

            if ($i >= 1000) {
                $this->dm->flush();
                $this->dm->clear();
                $i = 0;
            }
        }
        $this->dm->flush();
        $this->dm->clear();
        $progress->finish();
    }

}
