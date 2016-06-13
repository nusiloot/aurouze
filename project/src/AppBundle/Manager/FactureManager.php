<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Facture;
use AppBundle\Document\FactureLigne;
use AppBundle\Document\Societe;
use AppBundle\Document\Adresse;
use AppBundle\Manager\MouvementManager;

class FactureManager {

    protected $dm;
    protected $mm;
    protected $parameters;

    const DEFAUT_FREQUENCE_JOURS = 10;

    const EXPORT_DATE = 0 ;
    const EXPORT_JOURNAL= 1;
    const EXPORT_COMPTE= 2;
    const EXPORT_PIECE= 3;
    const EXPORT_LIBELLE= 4;
    const EXPORT_DEBIT= 5;
    const EXPORT_CREDIT= 6;
    const EXPORT_MONNAIE= 7;

    const EXPORT_LIGNE_GENERALE = 'generale';
    const EXPORT_LIGNE_TVA = 'tva';
    const EXPORT_LIGNE_HT = 'ht';

    const CODE_TVA_20 = "44571200";
    const CODE_TVA_10 = "44571010";

    const CODE_HT_20 = "70612000";
    const CODE_HT_10 = "70631000";


    const EXPORT_STATS_REPRESENTANT = 0 ;
    const EXPORT_STATS_RECONDUCTION_PREC = 1 ;
    const EXPORT_STATS_RECONDUCTION = 2 ;
    const EXPORT_STATS_PONCTUEL_PREC = 3 ;
    const EXPORT_STATS_PONCTUEL = 4 ;
    const EXPORT_STATS_RENOUVELABLE_PREC = 6 ;
    const EXPORT_STATS_RENOUVELABLE = 7;
    const EXPORT_STATS_NR_PREC = 8;
    const EXPORT_STATS_NR = 9;
    const EXPORT_STATS_PRODUIT_PREC = 10;
    const EXPORT_STATS_PRODUIT= 11;
    const EXPORT_STATS_PRODUIT_PRESTATION_PREC =12;
    const EXPORT_STATS_PRODUIT_PRESTATION = 13;
    const EXPORT_STATS_TOTAL_PREC = 14;
    const EXPORT_STATS_TOTAL = 15;

public static $export_factures_libelle = array(
  self::EXPORT_DATE => "Date",
   self::EXPORT_JOURNAL=> "Journal",
   self::EXPORT_COMPTE => "Compte",
   self::EXPORT_PIECE => "Pièce",
   self::EXPORT_LIBELLE => "Libellé",
   self::EXPORT_DEBIT => "Débit",
   self::EXPORT_CREDIT => "Crédit",
  self::EXPORT_MONNAIE => "Monnaie"
);

public static $export_stats_libelle = array(
  self::EXPORT_STATS_REPRESENTANT => "Représentant",
   self::EXPORT_STATS_RECONDUCTION_PREC => "Reconduction tacite (1)",
   self::EXPORT_STATS_RECONDUCTION => "Reconduction tacite (2)",
   self::EXPORT_STATS_PONCTUEL_PREC => "Ponctuel (1)",
   self::EXPORT_STATS_PONCTUEL => "Ponctuel (2)",
   self::EXPORT_STATS_RENOUVELABLE_PREC => "Renouvelable sur proposition (1)",
   self::EXPORT_STATS_RENOUVELABLE => "Renouvelable sur proposition (2)",
  self::EXPORT_STATS_NR_PREC => "NR (1)",
  self::EXPORT_STATS_NR => "NR (2)",
  self::EXPORT_STATS_PRODUIT_PREC => "Produits (1)",
  self::EXPORT_STATS_PRODUIT => "Produits (2)",
  self::EXPORT_STATS_PRODUIT_PRESTATION_PREC => "Produits prestations (1)",
  self::EXPORT_STATS_PRODUIT_PRESTATION => "Produits prestations (2)",
  self::EXPORT_STATS_TOTAL_PREC => "Total (1)",
  self::EXPORT_STATS_TOTAL => "Total (2)"
);

    function __construct(DocumentManager $dm, MouvementManager $mm, $parameters) {
        $this->dm = $dm;
        $this->mm = $mm;
        $this->parameters = $parameters;
    }

    public function getParameters() {

        return $this->parameters;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Facture');
    }

    public function findBySociete(Societe $societe) {

        return $this->getRepository()->findBy(array('societe' => $societe->getId()), array('dateEmission' => 'desc'));
    }

    public function createVierge(Societe $societe) {
        $facture = new Facture();
        $facture->setSociete($societe);
        $facture->setDateEmission(new \DateTime());
        $facture->getEmetteur()->setNom($this->parameters['emetteur']['nom']);
        $facture->getEmetteur()->setAdresse($this->parameters['emetteur']['adresse']);
        $facture->getEmetteur()->setCodePostal($this->parameters['emetteur']['code_postal']);
        $facture->getEmetteur()->setCommune($this->parameters['emetteur']['commune']);
        $facture->getEmetteur()->setTelephone($this->parameters['emetteur']['telephone']);
        $facture->getEmetteur()->setFax($this->parameters['emetteur']['fax']);
        $facture->getEmetteur()->setEmail($this->parameters['emetteur']['email']);

        return $facture;
    }

    public function create(Societe $societe, $mouvements, $dateFacturation) {
        $facture = new Facture();
        $facture->setSociete($societe);
        $facture->setDateFacturation($dateFacturation);
        $facture->setDateEmission(new \DateTime());

        $facture->getEmetteur()->setNom($this->parameters['emetteur']['nom']);
        $facture->getEmetteur()->setAdresse($this->parameters['emetteur']['adresse']);
        $facture->getEmetteur()->setCodePostal($this->parameters['emetteur']['code_postal']);
        $facture->getEmetteur()->setCommune($this->parameters['emetteur']['commune']);
        $facture->getEmetteur()->setTelephone($this->parameters['emetteur']['telephone']);
        $facture->getEmetteur()->setFax($this->parameters['emetteur']['fax']);
        $facture->getEmetteur()->setEmail($this->parameters['emetteur']['email']);

        foreach($mouvements as $mouvement) {
            if(!$mouvement->isFacturable() || $mouvement->isFacture()) {
                continue;
            }
            $ligne = new FactureLigne();
            $ligne->pullFromMouvement($mouvement);
            $facture->addLigne($ligne);
        }

        $facture->update();
        $facture->facturerMouvements();

        return $facture;
    }

    public function getMouvementsBySociete(Societe $societe) {

        return $this->mm->getMouvementsBySociete($societe, true, false);
    }

    public function getMouvements() {

        return $this->mm->getMouvements(true, false);
    }

    public function getStatsForCsv(){
      $date = new \DateTime();
      $facturesObjs = $this->getRepository()->exportOneMonthByDate($date);
      $ca_stats = array();
      $ca_stats['ENTETE'] = self::$export_stats_libelle;
      foreach ($facturesObjs as $facture) {
        if(!$facture->getContrat()){
            if(!array_key_exists('PAS DE CONTRAT',$ca_stats)){
            $ca_stats['PAS DE CONTRAT'] = array();
              foreach (array_keys(self::$export_stats_libelle) as $stats_index) {
                $ca_stats['PAS DE CONTRAT'][$stats_index] = 0.0;
              }
            }
            $ca_stats['PAS DE CONTRAT'][self::EXPORT_STATS_PRODUIT] += $facture->getMontantTTC();
            $ca_stats['PAS DE CONTRAT'][self::EXPORT_STATS_REPRESENTANT] = "TOTAL";
        }else{
          $commercial = ($facture->getContrat()->getCommercial())? $facture->getContrat()->getCommercial()->getId() : "VIDE";
          if(!array_key_exists($commercial,$ca_stats)){
            foreach (array_keys(self::$export_stats_libelle) as $stats_index) {
              $ca_stats[$commercial][$stats_index] = 0.0;
            }
          }
        if($facture->getContrat()->isTypeReconductionTacite()){
            $ca_stats[$commercial][self::EXPORT_STATS_RECONDUCTION] += $facture->getMontantTTC();
        }elseif($facture->getContrat()->isTypePonctuel()){
            $ca_stats[$commercial][self::EXPORT_STATS_PONCTUEL] += $facture->getMontantTTC();
        }elseif($facture->getContrat()->isTypeRenouvelableSurProposition()){
            $ca_stats[$commercial][self::EXPORT_STATS_RENOUVELABLE] += $facture->getMontantTTC();
        }

        foreach ($facture->getContrat()->getProduits() as $produit) {
          $ca_stats[$commercial][self::EXPORT_STATS_PRODUIT_PRESTATION] += $produit->getPrixPrestation();
        }
        $ca_stats[$commercial][self::EXPORT_STATS_REPRESENTANT] = (!$ca_stats[$commercial][self::EXPORT_STATS_REPRESENTANT])? 'VIDE' : $this->dm->getRepository('AppBundle:Compte')->findOneById($commercial)->getIdentite();
        $ca_stats[$commercial][self::EXPORT_STATS_TOTAL] += $facture->getMontantTTC();
      }
    }

    $facturesLastObjs = $this->getRepository()->exportOneMonthByDate($date->modify("-1 year"));
    foreach ($facturesLastObjs as $facture) {
      if(!$facture->getContrat()){
          if(!array_key_exists('PAS DE CONTRAT',$ca_stats)){
          $ca_stats['PAS DE CONTRAT'] = array();
            foreach (array_keys(self::$export_stats_libelle) as $stats_index) {
              $ca_stats['PAS DE CONTRAT'][$stats_index] = 0.0;
            }
          }
          $ca_stats['PAS DE CONTRAT'][self::EXPORT_STATS_PRODUIT_PREC] += $facture->getMontantTTC();
          $ca_stats['PAS DE CONTRAT'][self::EXPORT_STATS_REPRESENTANT] = "TOTAL";
      }else{
        $commercial = ($facture->getContrat()->getCommercial())? $facture->getContrat()->getCommercial()->getId() : "VIDE";
        if(!array_key_exists($commercial,$ca_stats)){
          foreach (array_keys(self::$export_stats_libelle) as $stats_index) {
            $ca_stats[$commercial][$stats_index] = 0.0;
          }
        }
      if($facture->getContrat()->isTypeReconductionTacite()){
          $ca_stats[$commercial][self::EXPORT_STATS_RECONDUCTION_PREC] += $facture->getMontantTTC();
      }elseif($facture->getContrat()->isTypePonctuel()){
          $ca_stats[$commercial][self::EXPORT_STATS_PONCTUEL_PREC] += $facture->getMontantTTC();
      }elseif($facture->getContrat()->isTypeRenouvelableSurProposition()){
          $ca_stats[$commercial][self::EXPORT_STATS_RENOUVELABLE_PREC] += $facture->getMontantTTC();
      }

      foreach ($facture->getContrat()->getProduits() as $produit) {
        $ca_stats[$commercial][self::EXPORT_STATS_PRODUIT_PRESTATION_PREC] += $produit->getPrixPrestation();
      }
      $ca_stats[$commercial][self::EXPORT_STATS_REPRESENTANT] = (!$ca_stats[$commercial][self::EXPORT_STATS_REPRESENTANT])? "VIDE" : $this->dm->getRepository('AppBundle:Compte')->findOneById($commercial)->getIdentite();
      $ca_stats[$commercial][self::EXPORT_STATS_TOTAL_PREC] += $facture->getMontantTTC();
    }
  }


    foreach ($ca_stats as $commercial => $stats) {
      ksort($stats);
      foreach ($stats as $key => $stat) {
        if(is_numeric($key)){
        $ca_stats['PAS DE CONTRAT'][$key] += $ca_stats[$commercial][$key];
      }
    }

    }
    return $ca_stats;
  }


    public function getFacturesForCsv() {
        $date = new \DateTime();
        $facturesObjs = $this->getRepository()->exportOneMonthByDate($date);

        $facturesArray = array();
        $facturesArray[] = self::$export_factures_libelle;

        foreach ($facturesObjs as $facture) {
              $facturesArray[] =  $this->buildFactureLigne($facture,self::EXPORT_LIGNE_GENERALE);
              $facturesArray[] =  $this->buildFactureLigne($facture,self::EXPORT_LIGNE_TVA);
              $facturesArray[] =  $this->buildFactureLigne($facture,self::EXPORT_LIGNE_HT);
        }
        return $facturesArray;
    }


    public function buildFactureLigne($facture,$typeLigne = self::EXPORT_LIGNE_GENERALE){
    $factureLigne = array();
    $factureLigne[self::EXPORT_DATE] = ($facture->getDateEmission())? $facture->getDateFacturation()->format('d/m/Y') : "????";
    $factureLigne[self::EXPORT_JOURNAL] =  "VENTES" ;
    if($typeLigne == self::EXPORT_LIGNE_GENERALE){
        $factureLigne[self::EXPORT_COMPTE] = $facture->getSociete()->getCodeComptable();
        $factureLigne[self::EXPORT_DEBIT] = ($facture->isAvoir())? "0" : $facture->getMontantTTC();
        $factureLigne[self::EXPORT_CREDIT] = ($facture->isAvoir())? $facture->getMontantTTC() : "0";
    }elseif($typeLigne == self::EXPORT_LIGNE_TVA){

      if($facture->getTva() == 0.2){
          $factureLigne[self::EXPORT_COMPTE] = self::CODE_TVA_20;
      }elseif($facture->getTva() == 0.1){
        $factureLigne[self::EXPORT_COMPTE] = self::CODE_TVA_10;
      }
      $factureLigne[self::EXPORT_DEBIT] = ($facture->isAvoir())? $facture->getMontantTaxe() : "0";
      $factureLigne[self::EXPORT_CREDIT] =  ($facture->isAvoir())? "0" : $facture->getMontantTaxe();
    }elseif($typeLigne == self::EXPORT_LIGNE_HT){
      if($facture->getTva() == 0.2){
          $factureLigne[self::EXPORT_COMPTE] = self::CODE_HT_20;
      }elseif($facture->getTva() == 0.1){
        $factureLigne[self::EXPORT_COMPTE] = self::CODE_HT_10;
      }
      $factureLigne[self::EXPORT_DEBIT] = ($facture->isAvoir())? $facture->getMontantHt() : "0";
      $factureLigne[self::EXPORT_CREDIT] =  ($facture->isAvoir())? "0" : $facture->getMontantHt();

    }
    $factureLigne[self::EXPORT_PIECE] =  $facture->getNumeroFacture();
    $factureLigne[self::EXPORT_LIBELLE] =  $facture->getSociete()->getRaisonSociale();
    $factureLigne[self::EXPORT_MONNAIE] =  "E" ;
    ksort($factureLigne);
    return $factureLigne;
  }
}
