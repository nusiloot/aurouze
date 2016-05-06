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
    protected $sm;
    protected $fm;
    protected $cm;

    const CSV_FACTURE_ID = 0;
    const CSV_NUMERO_FACTURE = 1;
    const CSV_IS_AVOIR = 2;
    const CSV_REF_AVOIR = 3;
    const CSV_CONTRAT_ID = 7;
    const CSV_SOCIETE_ID = 5;
    const CSV_FACTURE_LIGNE_ID = 27;
    const CSV_FACTURE_LIGNE_PASSAGE = 28;
    const CSV_FACTURE_LIGNE_PRODUIT = 29;
    const CSV_FACTURE_LIGNE_LIBELLE = 30;
    const CSV_FACTURE_LIGNE_PUHT = 31;
    const CSV_FACTURE_LIGNE_QTE = 32;
    const CSV_FACTURE_LIGNE_TVA = 33;
    const CSV_DATE_FACTURATION = 8;
    const CSV_DATE_LIMITE_REGLEMENT = 9;
    const CSV_REGLEMENT_TYPE = 10;
    const CSV_VEROUILLE = 12;
    const CSV_FACTURE_CMT = 13;
    const CSV_TVA_REDUITE = 21;
    const CSV_DATE_CREATION = 22;

    public function __construct(DocumentManager $dm, FactureManager $fm, SocieteManager $sm, ContratManager $cm) {
        $this->dm = $dm;
        $this->fm = $fm;
        $this->sm = $sm;
        $this->cm = $cm;
    }

    public function import($file, OutputInterface $output) {
        $csvFile = new CsvFile($file);

        $progress = new ProgressBar($output, 100);
        $progress->start();

        $csv = $csvFile->getCsv();

        $i = 0;
        $cptTotal = 0;

        $lignes = array();
        $currentIdFacture = null;

        foreach ($csv as $data) {

            if(is_null($currentIdFacture)) {
                $currentIdFacture = $data[self::CSV_FACTURE_ID];
            }

            if($currentIdFacture == $data[self::CSV_FACTURE_ID]) {

                $lignes[] = $data;
                continue;
            }

            $this->importFacture($lignes);
            $lignes[] = $data;



            $facture->update();
            $this->dm->persist($facture);



            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }

            if ($i >= 1000) {
                //$this->dm->flush();
                $i = 0;
            }
        }

        $this->dm->flush();
        $progress->finish();
    }

    public function importFacture($lignes) {
        $ligneFacture = $lignes[0];

        $societe = $this->sm->getRepository()->findOneBy(array('identifiantReprise' => $ligneFacture[self::CSV_SOCIETE_ID]));

        if (!$societe) {
            $output->writeln(sprintf("<error>La societe %s n'existe pas</error>", $ligneFacture[self::CSV_SOCIETE_ID]));
            continue;
        }

        $facture = $this->fm->getRepository()->findOneByIdentifiantReprise($ligneFacture[self::CSV_FACTURE_ID]);
        if ($facture) {
            $output->writeln(sprintf("<comment>La facture %s existe déjà avec l'id %s </comment>", $ligneFacture[self::CSV_FACTURE_ID], $facture->getId()));
            continue;
        }

        $facture = new Facture();
        $facture->setDateEmission(new \DateTime($ligneFacture[self::CSV_DATE_FACTURATION]));
        $facture->setDateFacturation(new \DateTime($ligneFacture[self::CSV_DATE_FACTURATION]));
        $facture->setSociete($societe);

        $facture->setIdentifiantReprise($ligneFacture[self::CSV_FACTURE_ID]);
        $facture->setNumeroFacture($ligneFacture[self::CSV_NUMERO_FACTURE]);

        foreach($lignes as $ligne) {

            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($ligne[self::CSV_CONTRAT_ID]);

            $mouvement = new Mouvement();
            $mouvement->setPrix($ligne[self::CSV_FACTURE_LIGNE_PUHT]);
            $mouvement->setFacturable(true);
            $mouvement->setFacture($mvtIsFacture);

            $mouvement->setLibelle(str_replace('#', "\n", $ligne[self::CSV_FACTURE_LIGNE_LIBELLE]));

            if ($ligne[self::CSV_FACTURE_LIGNE_PASSAGE]) {
                $refPassage = str_replace('#', "", $ligne[self::CSV_FACTURE_LIGNE_PASSAGE]);
                $passage = $this->dm->getRepository('AppBundle:Passage')->findOneByIdentifiantReprise($refPassage);
                if (!$passage) {
                    $output->writeln(sprintf("<comment>Le passage d'identifiant de reprise %s n'est pas trouvé dans la base </comment>", $refPassage));
                } else {
                    $passage->setMouvementDeclenchable(true);
                    $passage->setMouvementDeclenche($mvtIsFacture);
                }
            }

            if($contrat) {
                $mouvement->setOrigineDocument($contrat);
                $contrat->addMouvement($mouvement);
            }

            $fl = new FactureLigne();

            $fl->setLibelle(str_replace('#', "\n", $ligne[self::CSV_FACTURE_LIGNE_LIBELLE]));
            $fl->setMontantHT($ligne[self::CSV_FACTURE_LIGNE_PUHT]);
            $fl->setPrixUnitaire($ligne[self::CSV_FACTURE_LIGNE_PUHT]);
            $fl->setQuantite($ligne[self::CSV_FACTURE_LIGNE_QTE]);
            $fl->setTauxTaxe(0.2);
            if ($ligne[self::CSV_TVA_REDUITE]) {
                $fl->setTauxTaxe(0.1);
            }
            if($contrat) {
                $fl->setOrigineDocument($contrat);
            }
            $facture->addLigne($fl);
        }
    }

}
