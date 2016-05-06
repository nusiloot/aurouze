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

    protected $facturesRedressees = array();
    protected $contratsNbFactures = array();

    const CSV_FACTURE_ID = 0;
    const CSV_NUMERO_FACTURE = 1;
    const CSV_IS_AVOIR = 2;
    const CSV_REF_AVOIR = 3;
    const CSV_CONTRAT_ID = 7;
    const CSV_SOCIETE_ID = 5;
    const CSV_TVA_REDUITE = 21;
    const CSV_DESCRIPTION = 13;
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

            $this->importFacture($lignes, $output);

            $i++;
            $cptTotal++;
            if ($cptTotal % (count($csv) / 100) == 0) {
                $progress->advance();
            }

            if ($i >= 1000) {
                $this->dm->flush();
                $this->dm->clear();
                $i = 0;
            }

            $lignes = array();
            $currentIdFacture = $data[self::CSV_FACTURE_ID];
            $lignes[] = $data;
        }

        $this->importFacture($lignes);

        $this->dm->flush();
        $progress->finish();
    }

    public function importFacture($lignes, $output) {
        $ligneFacture = $lignes[0];

        $facture = $this->fm->getRepository()->findOneByIdentifiantReprise($ligneFacture[self::CSV_FACTURE_ID]);
        if ($facture) {
            $output->writeln(sprintf("<comment>La facture %s existe déjà avec l'id %s </comment>", $ligneFacture[self::CSV_FACTURE_ID], $facture->getId()));
            return;
        }

        $societe = $this->sm->getRepository()->findOneBy(array('identifiantReprise' => $ligneFacture[self::CSV_SOCIETE_ID]));

        if (!$societe) {
            $output->writeln(sprintf("<error>La societe %s n'existe pas</error>", $ligneFacture[self::CSV_SOCIETE_ID]));
            return;
        }

        $mouvements = array();
        foreach($lignes as $ligne) {
            $contrat = $this->cm->getRepository()->findOneByIdentifiantReprise($ligne[self::CSV_CONTRAT_ID]);

            if($ligne[self::CSV_CONTRAT_ID] && !$contrat) {
                $output->writeln(sprintf("<error>Le contrat %s n'existe pas</error>", $ligneFacture[self::CSV_CONTRAT_ID]));
                return;
            }

            $coefficient = ($ligneFacture[self::CSV_IS_AVOIR]) ? -1 : 1;

            $mouvement = new Mouvement();
            $mouvement->setFacturable(boolval($ligneFacture[self::CSV_NUMERO_FACTURE]));
            $mouvement->setFacture(false);
            $mouvement->setPrixUnitaire($ligne[self::CSV_FACTURE_LIGNE_PUHT] * $coefficient);
            $mouvement->setQuantite($ligne[self::CSV_FACTURE_LIGNE_QTE]);
            if($ligne[self::CSV_FACTURE_LIGNE_TVA] == 1) {
                $mouvement->setTauxTaxe(0.055);
            } elseif($ligne[self::CSV_FACTURE_LIGNE_TVA] == 2) {
                $mouvement->setTauxTaxe(0.196);
            } elseif($ligne[self::CSV_FACTURE_LIGNE_TVA] == 3) {
                $mouvement->setTauxTaxe(0.07);
            } elseif($ligne[self::CSV_FACTURE_LIGNE_TVA] == 4) {
                $mouvement->setTauxTaxe(0.2);
            } elseif($ligne[self::CSV_FACTURE_LIGNE_TVA] == 5) {
                $mouvement->setTauxTaxe(0.1);
            }
            $mouvement->setIdentifiant($ligne[self::CSV_FACTURE_LIGNE_ID]);
            $mouvement->setSociete($societe->getId());
            $mouvement->setLibelle(preg_replace('/^".*"$/', "", str_replace('#', "\n", $ligne[self::CSV_FACTURE_LIGNE_LIBELLE])));

            /*if ($ligne[self::CSV_FACTURE_LIGNE_PASSAGE]) {
                $refPassage = str_replace('#', "", $ligne[self::CSV_FACTURE_LIGNE_PASSAGE]);
                $passage = $this->dm->getRepository('AppBundle:Passage')->findOneByIdentifiantReprise($refPassage);
                if (!$passage) {
                    $output->writeln(sprintf("<comment>Le passage d'identifiant de reprise %s n'est pas trouvé dans la base </comment>", $refPassage));
                } else {
                    $passage->setMouvementDeclenchable(true);
                    $passage->setMouvementDeclenche($mvtIsFacture);
                }
            }*/

            if($contrat) {
                $mouvement->setDocument($contrat);
            }

            if($contrat && !array_key_exists($contrat->getId(), $this->contratsNbFactures)) {
                $this->contratsNbFactures[$contrat->getId()] = 0;
            }

            if($contrat && !$ligneFacture[self::CSV_IS_AVOIR]) {
                $this->contratsNbFactures[$contrat->getId()] += 1;
            }

            if($contrat && $ligneFacture[self::CSV_IS_AVOIR]) {
                $this->contratsNbFactures[$contrat->getId()] -= 1;
            }

            if($mouvement->isFacturable() && $contrat) {
                $contrat->addMouvement($mouvement);
            }

            $mouvements[] = $mouvement;
        }

        if(!$ligneFacture[self::CSV_NUMERO_FACTURE]) {
            return;
        }

        $facture = $this->fm->create($societe, $mouvements, new \DateTime($ligneFacture[self::CSV_DATE_FACTURATION]));

        $facture->setDateEmission(new \DateTime($ligneFacture[self::CSV_DATE_FACTURATION]));
        $facture->setDateLimitePaiement(new \DateTime($ligneFacture[self::CSV_DATE_LIMITE_REGLEMENT]));

        $facture->setIdentifiantReprise($ligneFacture[self::CSV_FACTURE_ID]);
        $facture->setNumeroFacture($ligneFacture[self::CSV_NUMERO_FACTURE]);
        $facture->setDescription(preg_replace('/^".*"$/', "", str_replace('#', "\n", $ligne[self::CSV_DESCRIPTION])));
        $facture->facturerMouvements();

        $this->dm->persist($facture);

        if($ligneFacture[self::CSV_REF_AVOIR]) {
            $this->facturesRedressees[$ligneFacture[self::CSV_REF_AVOIR]] = $facture->getId();
        }
    }

}
