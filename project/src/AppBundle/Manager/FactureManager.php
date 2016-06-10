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
        $facture->setDateEmission(new \DateTime());
        $facture->setDateFacturation($dateFacturation);

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

    public function getFacturesForCsv() {
        $date = new \DateTime();
        $facturesObjs = $this->getRepository()->findByDate($date);

        $facturesArray = array();
        $facturesArray[] = self::$export_paiement_libelle;

        foreach ($facturesObjs as $paiements) {
            foreach ($paiements->getPaiement() as $paiement) {
                $factureArr = array();
                $factureArr[self::EXPORT_DATE_PAIEMENT] = $paiement->getDatePaiement()->format('d/m/Y');
                $factureArr[self::EXPORT_CODE_COMPTABLE] = substr($paiement->getFacture()->getSociete()->getCodeComptable(),0,8);
                $factureArr[self::EXPORT_VR_PRIX] = $paiement->getMontant();
                $factureArr[self::EXPORT_FACTURE_NUM_RAISON_SOCIALE] = $paiement->getFacture()->getNumeroFacture()." ".$paiement->getFacture()->getSociete()->getRaisonSociale();
                $factureArr[self::EXPORT_PRIX] = $paiement->getMontant()." €";
                $factureArr[self::EXPORT_EMPTY] = "";
                $factureArr[self::EXPORT_CODE_COMPTABLE] = $paiement->getFacture()->getSociete()->getCodeComptable();
                $factureArr[self::EXPORT_FACTURE_NUM] = $paiement->getFacture()->getSociete()->getCodeComptable();

                $factureArr[self::EXPORT_CLIENT_RAISON_SOCIALE] = $paiement->getFacture()->getSociete()->getRaisonSociale();
                    $factureArr[self::EXPORT_TVA_7] = "- €";
                    $factureArr[self::EXPORT_TVA_196] = "- €";
                    if($paiement->getFacture()->getTva() == 0.1){
                      $factureArr[self::EXPORT_TVA_10] = $paiement->getFacture()->getMontantTaxe();
                    }else{
                      $factureArr[self::EXPORT_TVA_10] = "- €";
                    }
                    if($paiement->getFacture()->getTva() == 0.2){
                      $factureArr[self::EXPORT_TVA_20] = $paiement->getFacture()->getMontantTaxe();
                    }else{
                      $factureArr[self::EXPORT_TVA_20] = "- €";
                    }
                      $factureArr[self::EXPORT_MODE_REGLEMENT] = self::$moyens_paiement_libelles[$paiement->getMoyenPaiement()];
                      $factureArr[self::EXPORT_TYPE_REGLEMENT] = self::$types_reglements_libelles[$paiement->getTypeReglement()];
                      $factureArr[self::EXPORT_NUMERO_PIECE_BANQUE] = "";
                      $factureArr[self::EXPORT_LIBELLE_PIECE_BANQUE] = $paiement->getLibelle();
                      $factureArr[self::EXPORT_TYPE_PIECE_BANQUE] = self::$moyens_paiement_libelles[$paiement->getMoyenPaiement()];
                      $factureArr[self::EXPORT_MONTANT_PIECE_BANQUE] = $paiement->getMontant();
                      $factureArr[self::EXPORT_MONTANT_CHEQUE] = " ? pour ? ";


                $facturesArray[] = $factureArr;
            }
        }
        return $facturesArray;
    }



    }
