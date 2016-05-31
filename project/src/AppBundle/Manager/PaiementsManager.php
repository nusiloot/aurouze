<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Paiements;

class PaiementsManager {

    protected $dm;
    protected $fm;
    protected $parameters;

    const TYPE_REGLEMENT_FACTURE = 'FACTURE';
    const TYPE_REGLEMENT_ACCOMPTE_COMMANDE = 'ACCOMPTE_COMMANDE';
    const TYPE_REGLEMENT_REGULARISATION = 'REGULARISATION';
    const TYPE_REGLEMENT_REGULARISATION_AVOIR = 'REGULARISATION_AVOIR';
    const TYPE_REGLEMENT_PERTE = 'PERTE';
    const TYPE_REGLEMENT_GAIN = 'GAIN';
    const MOYEN_PAIEMENT_CHEQUE = 'CHEQUE';
    const MOYEN_PAIEMENT_VIREMENT = 'VIREMENT';
    const MOYEN_PAIEMENT_ESPECE = 'ESPECE';
    const MOYEN_PAIEMENT_TRAITE = 'TRAITE';
    const MOYEN_PAIEMENT_CB = 'CB';
    const MOYEN_PAIEMENT_REGULARISATION_COMPTABLE = 'REGUL_COMPTA';

    const EXPORT_DATE_PAIEMENT = 0;
    const EXPORT_CODE_COMPTABLE_TRONQ = 1;
    const EXPORT_VR_PRIX = 2;
    const EXPORT_FACTURE_NUM_RAISON_SOCIALE = 3;
    const EXPORT_PRIX = 4;
    const EXPORT_EMPTY = 5;    
    const EXPORT_CODE_COMPTABLE = 6;
    const EXPORT_FACTURE_NUM = 7;
    
    
    public static $types_reglements_libelles = array(
        self::TYPE_REGLEMENT_FACTURE => "Règlement de facture",
        self::TYPE_REGLEMENT_ACCOMPTE_COMMANDE => "Acompte à la commande",
        self::TYPE_REGLEMENT_REGULARISATION => "Règlement de régularisation",
        self::TYPE_REGLEMENT_REGULARISATION_AVOIR => "Régularisation par avoir",
        self::TYPE_REGLEMENT_PERTE => "Perte",
        self::TYPE_REGLEMENT_GAIN => "Gain");
    public static $nouveau_types_reglements_libelles = array(
        self::TYPE_REGLEMENT_FACTURE => "Règlement de facture",
        self::TYPE_REGLEMENT_ACCOMPTE_COMMANDE => "Acompte à la commande",
        self::TYPE_REGLEMENT_REGULARISATION => "Règlement de régularisation");
    public static $types_reglements_index = array(
        "1" => self::TYPE_REGLEMENT_FACTURE,
        "2" => self::TYPE_REGLEMENT_ACCOMPTE_COMMANDE,
        "3" => self::TYPE_REGLEMENT_REGULARISATION,
        "4" => self::TYPE_REGLEMENT_REGULARISATION_AVOIR,
        "5" => self::TYPE_REGLEMENT_PERTE,
        "6" => self::TYPE_REGLEMENT_GAIN);
    public static $moyens_paiement_libelles = array(
        self::MOYEN_PAIEMENT_CHEQUE => "Chèque",
        self::MOYEN_PAIEMENT_VIREMENT => "Virement",
        self::MOYEN_PAIEMENT_ESPECE => "Espèces",
        self::MOYEN_PAIEMENT_TRAITE => "Traite",
        self::MOYEN_PAIEMENT_CB => "Carte Bleue",
        self::MOYEN_PAIEMENT_REGULARISATION_COMPTABLE => "Régularisation comptable");
    public static $moyens_paiement_index = array(
        "1" => self::MOYEN_PAIEMENT_CHEQUE,
        "2" => self::MOYEN_PAIEMENT_VIREMENT,
        "3" => self::MOYEN_PAIEMENT_ESPECE,
        "4" => self::MOYEN_PAIEMENT_TRAITE,
        "5" => self::MOYEN_PAIEMENT_CB,
        "6" => self::MOYEN_PAIEMENT_REGULARISATION_COMPTABLE);



    
    function __construct(DocumentManager $dm, FactureManager $fm, $parameters) {
        $this->dm = $dm;
        $this->fm = $fm;
        $this->parameters = $parameters;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Paiements');
    }

    public function getParameters() {

        return $this->parameters;
    }

    public function createByDateCreation(\DateTime $dateCreation) {
        $paiements = new Paiements();
        $paiements->setDateCreation($dateCreation);
        return $paiements;
    }

    public function getPaiementsForCsv() {
        $date = new \DateTime();
        $paiementsObjs = $this->getRepository()->findByDate($date);
        
        $paiementsArray = array();
        foreach ($paiementsObjs as $paiements) {
            foreach ($paiements->getPaiement() as $paiement) {
                $paiementArr = array();
                $paiementArr[self::EXPORT_DATE_PAIEMENT] = $paiement->getDatePaiement()->format('d/m/Y');
                $paiementArr[self::EXPORT_CODE_COMPTABLE] = substr($paiement->getFacture()->getSociete()->getCodeComptable(),0,8);
                $paiementArr[self::EXPORT_VR_PRIX] = $paiement->getMontant();
                $paiementArr[self::EXPORT_FACTURE_NUM_RAISON_SOCIALE] = $paiement->getFacture()->getNumeroFacture()." ".$paiement->getFacture()->getSociete()->getRaisonSociale();
                $paiementArr[self::EXPORT_PRIX] = $paiement->getMontant()." €";
                $paiementArr[self::EXPORT_EMPTY] = "";
                $paiementArr[self::EXPORT_CODE_COMPTABLE] = $paiement->getFacture()->getSociete()->getCodeComptable();
                $paiementArr[self::EXPORT_FACTURE_NUM] = $paiement->getFacture()->getSociete()->getCodeComptable();
                $paiementArr[] = $paiement->getMontant();
                
                $paiementsArray[] = $paiementArr;
            }
        }
        return $paiementsArray;
    }

}
