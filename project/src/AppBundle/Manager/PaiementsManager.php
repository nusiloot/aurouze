<?php

namespace AppBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Paiements;

class PaiementsManager {

    protected $dm;
    protected $fm;

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
    const MOYEN_PAIEMENT_REGULARISATION_COMPTABLE = 'CB';

    public static $types_reglements_libelles = array(
        self::TYPE_REGLEMENT_FACTURE => "Règlement de facture",
        self::TYPE_REGLEMENT_ACCOMPTE_COMMANDE => "Acompte à la commande",
        self::TYPE_REGLEMENT_REGULARISATION => "Règlement de régularisation",
        self::TYPE_REGLEMENT_REGULARISATION_AVOIR => "Régularisation par avoir",
        self::TYPE_REGLEMENT_PERTE => "Perte",
        self::TYPE_REGLEMENT_GAIN => "Gain");
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

    function __construct(DocumentManager $dm, FactureManager $fm) {
        $this->dm = $dm;
        $this->fm = $fm;
    }

    public function getRepository() {

        return $this->dm->getRepository('AppBundle:Paiements');
    }

    
    public function createByDateCreation(\DateTime $dateCreation){
        $paiements = new Paiements();
        $paiements->setDateCreation($dateCreation);
        return $paiements;
    }
   
}
